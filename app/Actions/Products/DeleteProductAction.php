<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;
use App\Services\ImageUploadService;

class DeleteProductAction
{
    public function __construct(
        protected ImageUploadService $uploads,
    ) {}

    public function handle(Product $product): void
    {
        $this->uploads->deleteMany($product->galleryImageFiles());
        $product->delete();
    }
}
