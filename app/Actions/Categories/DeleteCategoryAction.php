<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use App\Services\ImageUploadService;

class DeleteCategoryAction
{
    public function __construct(
        protected ImageUploadService $uploads,
    ) {}

    public function handle(Category $category): string
    {
        if ($category->products()->exists()) {
            return 'Category cannot be deleted while products are assigned to it.';
        }

        $this->uploads->deleteMany([
            [
                'cloudinary_public_id' => $category->image_cloudinary_public_id,
                'uploaded_image_id' => $category->image_uploaded_image_id,
            ],
            [
                'cloudinary_public_id' => $category->banner_image_cloudinary_public_id,
                'uploaded_image_id' => $category->banner_image_uploaded_image_id,
            ],
        ]);
        $category->delete();

        return 'Category deleted successfully.';
    }
}
