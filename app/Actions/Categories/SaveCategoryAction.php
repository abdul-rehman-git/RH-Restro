<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ImageUploadService;

class SaveCategoryAction
{
    public function __construct(
        protected ImageUploadService $uploads,
    ) {}

    public function handle(StoreCategoryRequest|UpdateCategoryRequest $request, ?Category $category = null): Category
    {
        $data = $request->payload();
        $queued = [];

        if ($category === null) {
            if ($request->imageFile() !== null) {
                $image = $this->uploads->upload($request->imageFile(), 'image', 'category-image');
                $data = [...$data, ...$this->imagePayload($image, 'image')];
                $queued[] = $image;
            }

            if ($request->bannerImageFile() !== null) {
                $banner = $this->uploads->upload($request->bannerImageFile(), 'banner_image', 'category-banner');
                $data = [...$data, ...$this->imagePayload($banner, 'banner_image')];
                $queued[] = $banner;
            }

            $category = Category::query()->create($data);
            $this->uploads->queueCloudinaryUploads($queued);

            return $category;
        }

        if ($request->shouldRemoveImage()) {
            $this->uploads->delete($this->deletePayload($category, 'image'));
            $data = [...$data, ...$this->clearedImagePayload('image')];
        }

        if ($request->shouldRemoveBannerImage()) {
            $this->uploads->delete($this->deletePayload($category, 'banner_image'));
            $data = [...$data, ...$this->clearedImagePayload('banner_image')];
        }

        if ($request->imageFile() !== null) {
            $this->uploads->delete($this->deletePayload($category, 'image'));
            $image = $this->uploads->upload($request->imageFile(), 'image', 'category-image');
            $data = [...$data, ...$this->imagePayload($image, 'image')];
            $queued[] = $image;
        }

        if ($request->bannerImageFile() !== null) {
            $this->uploads->delete($this->deletePayload($category, 'banner_image'));
            $banner = $this->uploads->upload($request->bannerImageFile(), 'banner_image', 'category-banner');
            $data = [...$data, ...$this->imagePayload($banner, 'banner_image')];
            $queued[] = $banner;
        }

        $category->update($data);
        $this->uploads->queueCloudinaryUploads($queued);

        return $category;
    }

    /**
     * @param  array{url: string, cloudinary_public_id: string|null, is_uploaded_to_cloudinary: bool, uploaded_image_id: int}  $upload
     * @return array<string, mixed>
     */
    private function imagePayload(array $upload, string $field): array
    {
        return [
            $field => $upload['url'],
            "{$field}_cloudinary_public_id" => $upload['cloudinary_public_id'] ?? null,
            "{$field}_is_uploaded_to_cloudinary" => (bool) ($upload['is_uploaded_to_cloudinary'] ?? false),
            "{$field}_uploaded_image_id" => $upload['uploaded_image_id'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clearedImagePayload(string $field): array
    {
        return [
            $field => null,
            "{$field}_cloudinary_public_id" => null,
            "{$field}_is_uploaded_to_cloudinary" => false,
            "{$field}_uploaded_image_id" => null,
        ];
    }

    /**
     * @return array{cloudinary_public_id: string|null, uploaded_image_id: int|null}
     */
    private function deletePayload(Category $category, string $field): array
    {
        return [
            'cloudinary_public_id' => $category->{"{$field}_cloudinary_public_id"},
            'uploaded_image_id' => $category->{"{$field}_uploaded_image_id"},
        ];
    }
}
