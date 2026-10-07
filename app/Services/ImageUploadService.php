<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\UploadToCloudinaryJob;
use App\Models\UploadedImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploadService
{
    public function __construct(
        protected ImageOptimizer $optimizer,
        protected CloudinaryStorageService $cloudinary,
    ) {}

    /**
     * Store locally and queue Cloudinary upload so the request is not blocked.
     *
     * @return array{
     *     url: string,
     *     cloudinary_public_id: string|null,
     *     is_uploaded_to_cloudinary: bool,
     *     uploaded_image_id: int,
     *     local_path: string
     * }
     */
    public function upload(UploadedFile $file, string $field = 'image', ?string $prefix = null): array
    {
        $optimized = $this->optimizer->optimize($file, $field);
        $uploadName = $this->fileName($file, $optimized->extension, $prefix);
        $relativePath = 'uploads/'.now()->format('Y/m').'/'.$uploadName;

        try {
            $stored = Storage::disk('public')->put($relativePath, file_get_contents($optimized->path) ?: '');

            if (! $stored) {
                throw ValidationException::withMessages([
                    $field => 'Unable to store the uploaded image locally.',
                ]);
            }

            $localUrl = Storage::disk('public')->url($relativePath);

            $record = UploadedImage::query()->create([
                'disk' => 'public',
                'path' => $relativePath,
                'url' => $localUrl,
                'original_name' => $uploadName,
                'mime_type' => $this->mimeType($optimized->extension),
                'size' => Storage::disk('public')->size($relativePath) ?: null,
                'cloudinary_public_id' => null,
                'is_uploaded_to_cloudinary' => false,
            ]);

            Log::info('Image stored locally; waiting for owner save before Cloudinary queue', [
                'field' => $field,
                'uploaded_image_id' => $record->id,
                'local_url' => $localUrl,
            ]);

            return [
                'url' => $localUrl,
                'cloudinary_public_id' => null,
                'is_uploaded_to_cloudinary' => false,
                'uploaded_image_id' => $record->id,
                'local_path' => $relativePath,
            ];
        } finally {
            $optimized->cleanup();
        }
    }

    public function queueCloudinaryUpload(int $uploadedImageId): void
    {
        UploadToCloudinaryJob::dispatch($uploadedImageId);

        Log::info('Cloudinary upload queued', [
            'uploaded_image_id' => $uploadedImageId,
        ]);
    }

    /**
     * @param  array<int, array{uploaded_image_id?: int|null}>  $uploads
     */
    public function queueCloudinaryUploads(array $uploads): void
    {
        foreach ($uploads as $upload) {
            $id = $upload['uploaded_image_id'] ?? null;

            if (is_int($id) && $id > 0) {
                $this->queueCloudinaryUpload($id);
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array{
     *     url: string,
     *     cloudinary_public_id: string|null,
     *     is_uploaded_to_cloudinary: bool,
     *     uploaded_image_id: int,
     *     local_path: string
     * }>
     */
    public function uploadMany(array $files, string $field = 'images', ?string $prefix = null): array
    {
        $uploaded = [];

        try {
            foreach ($files as $index => $file) {
                $uploaded[] = $this->upload($file, $field, $prefix ? "{$prefix}-".($index + 1) : null);
            }
        } catch (\Throwable $exception) {
            $this->deleteMany($uploaded);

            throw $exception;
        }

        return $uploaded;
    }

    /**
     * @param  array{
     *     file_id?: string|null,
     *     cloudinary_public_id?: string|null,
     *     uploaded_image_id?: int|null,
     *     local_path?: string|null,
     *     is_uploaded_to_cloudinary?: bool|null
     * }|null  $file
     */
    public function delete(?array $file): void
    {
        if ($file === null) {
            return;
        }

        $uploadedImageId = $file['uploaded_image_id'] ?? null;

        if (is_int($uploadedImageId) && $uploadedImageId > 0) {
            $record = UploadedImage::query()->find($uploadedImageId);

            if ($record) {
                $this->deleteUploadedImage($record);

                return;
            }
        }

        $fileId = trim((string) ($file['cloudinary_public_id'] ?? $file['file_id'] ?? ''));

        if ($fileId !== '') {
            $this->cloudinary->deleteFile($fileId);
        }

        $localPath = trim((string) ($file['local_path'] ?? ''));

        if ($localPath !== '') {
            Storage::disk('public')->delete($localPath);
        }
    }

    /**
     * @param  array<int, array{file_id?: string|null, cloudinary_public_id?: string|null, uploaded_image_id?: int|null}>  $files
     */
    public function deleteMany(array $files): void
    {
        foreach ($files as $file) {
            $this->delete($file);
        }
    }

    private function deleteUploadedImage(UploadedImage $record): void
    {
        if ($record->is_uploaded_to_cloudinary && $record->cloudinary_public_id) {
            $this->cloudinary->deleteFile($record->cloudinary_public_id);
        }

        Storage::disk($record->disk ?: 'public')->delete($record->path);
        $record->delete();
    }

    private function fileName(UploadedFile $file, string $extension, ?string $prefix = null): string
    {
        $base = $prefix
            ?: pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);

        $base = Str::slug($base !== '' ? $base : 'image');

        return sprintf('%s-%s.%s', $base, Str::lower(Str::random(12)), $extension);
    }

    private function mimeType(string $extension): string
    {
        return match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
