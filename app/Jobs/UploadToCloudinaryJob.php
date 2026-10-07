<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UploadedImage;
use App\Services\CloudinaryStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadToCloudinaryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private int $uploadedImageId) {}

    public function handle(CloudinaryStorageService $cloudinary): void
    {
        $image = UploadedImage::query()->find($this->uploadedImageId);

        if (! $image) {
            Log::warning('UploadedImage not found for Cloudinary job', [
                'uploaded_image_id' => $this->uploadedImageId,
            ]);

            return;
        }

        if ($image->is_uploaded_to_cloudinary) {
            return;
        }

        if (! $cloudinary->isConfigured()) {
            Log::warning('Cloudinary not configured; skipping async upload', [
                'uploaded_image_id' => $image->id,
            ]);

            return;
        }

        $disk = $image->disk ?: 'public';

        if (! Storage::disk($disk)->exists($image->path)) {
            Log::error('Local image missing for Cloudinary upload', [
                'uploaded_image_id' => $image->id,
                'disk' => $disk,
                'path' => $image->path,
            ]);

            return;
        }

        $absolutePath = Storage::disk($disk)->path($image->path);
        $fileName = $image->original_name ?: basename($image->path);
        $publicId = pathinfo($fileName, PATHINFO_FILENAME);

        try {
            $result = $cloudinary->uploadPublicFile($absolutePath, $fileName, $publicId);

            $image->update([
                'cloudinary_public_id' => $result['id'],
                'is_uploaded_to_cloudinary' => true,
                'url' => $result['url'],
            ]);

            $this->syncOwners($image);

            Log::info('UploadedImage synced to Cloudinary', [
                'uploaded_image_id' => $image->id,
                'cloudinary_public_id' => $result['id'],
                'url' => $result['url'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('UploadToCloudinaryJob failed', [
                'uploaded_image_id' => $image->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function syncOwners(UploadedImage $image): void
    {
        Category::query()
            ->where('image_uploaded_image_id', $image->id)
            ->update([
                'image' => $image->url,
                'image_cloudinary_public_id' => $image->cloudinary_public_id,
                'image_is_uploaded_to_cloudinary' => true,
            ]);

        Category::query()
            ->where('banner_image_uploaded_image_id', $image->id)
            ->update([
                'banner_image' => $image->url,
                'banner_image_cloudinary_public_id' => $image->cloudinary_public_id,
                'banner_image_is_uploaded_to_cloudinary' => true,
            ]);

        Product::query()
            ->where('image_uploaded_image_id', $image->id)
            ->update([
                'image' => $image->url,
                'image_cloudinary_public_id' => $image->cloudinary_public_id,
                'image_is_uploaded_to_cloudinary' => true,
            ]);

        Product::query()
            ->whereNotNull('gallery_image_files')
            ->orderBy('id')
            ->each(function (Product $product) use ($image): void {
                $files = $product->gallery_image_files ?? [];
                $changed = false;

                foreach ($files as $index => $file) {
                    if ((int) ($file['uploaded_image_id'] ?? 0) !== $image->id) {
                        continue;
                    }

                    $files[$index]['url'] = $image->url;
                    $files[$index]['file_id'] = $image->cloudinary_public_id;
                    $files[$index]['cloudinary_public_id'] = $image->cloudinary_public_id;
                    $files[$index]['is_uploaded_to_cloudinary'] = true;
                    $changed = true;
                }

                if (! $changed) {
                    return;
                }

                $product->update([
                    'gallery_image_files' => $files,
                    'gallery_images' => array_values(array_filter(array_column($files, 'url'))),
                    'image' => $files[0]['url'] ?? $product->image,
                    'image_cloudinary_public_id' => $files[0]['cloudinary_public_id'] ?? $product->image_cloudinary_public_id,
                    'image_is_uploaded_to_cloudinary' => (bool) ($files[0]['is_uploaded_to_cloudinary'] ?? false),
                    'image_uploaded_image_id' => $files[0]['uploaded_image_id'] ?? $product->image_uploaded_image_id,
                ]);
            });

        Customer::query()
            ->where('profile_photo_uploaded_image_id', $image->id)
            ->update([
                'profile_photo_url' => $image->url,
                'profile_photo_cloudinary_public_id' => $image->cloudinary_public_id,
                'profile_photo_is_uploaded_to_cloudinary' => true,
            ]);

        $this->syncSettingsImage($image);
    }

    private function syncSettingsImage(UploadedImage $image): void
    {
        $setting = Setting::query()->first();

        if (! $setting) {
            return;
        }

        $data = $setting->data ?? [];
        $changed = false;

        $paths = [
            'business_logo',
            'public_site.logo',
            'public_site.footer_logo',
            'public_pages.home.hero_image',
            'public_pages.about.artist_image',
            'seo.default_og_image',
        ];

        foreach ($paths as $path) {
            $current = data_get($data, $path);

            if (! is_array($current) || (int) ($current['uploaded_image_id'] ?? 0) !== $image->id) {
                continue;
            }

            data_set($data, $path, [
                ...$current,
                'url' => $image->url,
                'file_id' => $image->cloudinary_public_id,
                'cloudinary_public_id' => $image->cloudinary_public_id,
                'is_uploaded_to_cloudinary' => true,
            ]);
            $changed = true;
        }

        if ($changed) {
            $setting->data = $data;
            $setting->save();
        }
    }
}
