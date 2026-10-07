<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;

class SaveProductAction
{
    public function __construct(
        protected ImageUploadService $uploads,
    ) {}

    public function handle(StoreProductRequest|UpdateProductRequest $request, ?Product $product = null): Product
    {
        $data = $request->payload();

        if ($product === null) {
            $images = $this->uploads->uploadMany($request->imageFiles(), 'images', 'product-image');

            $product = Product::query()->create([
                ...$data,
                ...$this->primaryImagePayload($images[0] ?? null),
                'gallery_images' => $images !== [] ? array_column($images, 'url') : null,
                'gallery_image_files' => $images !== [] ? $images : null,
            ]);

            $this->uploads->queueCloudinaryUploads($images);
            $this->saveVariants($product, $request);

            return $product;
        }

        $currentImages = $product->imagePaths();
        $currentFiles = $product->galleryImageFiles();
        $keptImages = collect($request->existingImages())
            ->filter(fn (string $path): bool => in_array($path, $currentImages, true))
            ->values()
            ->all();

        $removedFiles = array_values(array_filter(
            $currentFiles,
            fn (array $file): bool => ! in_array($file['url'], $keptImages, true),
        ));

        $this->uploads->deleteMany($removedFiles);

        $newImages = $this->uploads->uploadMany($request->imageFiles(), 'images', 'product-image');
        $images = [
            ...array_values(array_filter(
                $currentFiles,
                fn (array $file): bool => in_array($file['url'], $keptImages, true),
            )),
            ...$newImages,
        ];

        $product->update([
            ...$data,
            ...$this->primaryImagePayload($images[0] ?? null),
            'gallery_images' => $images !== [] ? array_column($images, 'url') : null,
            'gallery_image_files' => $images !== [] ? $images : null,
        ]);

        $this->uploads->queueCloudinaryUploads($newImages);
        $this->saveVariants($product, $request);

        return $product;
    }

    private function saveVariants(Product $product, StoreProductRequest|UpdateProductRequest $request): void
    {
        $rawVariants = $request->input('variants', []);
        if (! is_array($rawVariants)) {
            return;
        }

        $keptVariantIds = [];

        foreach ($rawVariants as $index => $varData) {
            if (! is_array($varData) || blank($varData['name'] ?? null)) {
                continue;
            }

            $variantId = isset($varData['id']) ? (int) $varData['id'] : null;
            $imageFile = $request->file("variants.{$index}.image_file");
            
            $imagePayload = [];
            if ($imageFile instanceof UploadedFile) {
                $uploaded = $this->uploads->uploadMany([$imageFile], 'images', 'product-variant');
                if (isset($uploaded[0])) {
                    $this->uploads->queueCloudinaryUploads($uploaded);
                    $imagePayload = [
                        'image' => $uploaded[0]['url'] ?? null,
                        'image_cloudinary_public_id' => $uploaded[0]['cloudinary_public_id'] ?? null,
                        'image_is_uploaded_to_cloudinary' => (bool) ($uploaded[0]['is_uploaded_to_cloudinary'] ?? false),
                        'image_uploaded_image_id' => $uploaded[0]['uploaded_image_id'] ?? null,
                    ];
                }
            } elseif (! empty($varData['image']) && is_string($varData['image'])) {
                $imagePayload = [
                    'image' => $varData['image'],
                ];
            }

            $variantAttributes = [
                'name' => (string) $varData['name'],
                'sku' => ! empty($varData['sku']) ? (string) $varData['sku'] : null,
                'price' => (float) ($varData['price'] ?? 0),
                'compare_price' => ! empty($varData['compare_price']) ? (float) $varData['compare_price'] : null,
                'stock_quantity' => isset($varData['stock_quantity']) ? (int) $varData['stock_quantity'] : 0,
                'sort_order' => isset($varData['sort_order']) ? (int) $varData['sort_order'] : $index,
                'is_active' => isset($varData['is_active']) ? (bool) $varData['is_active'] : true,
                ...$imagePayload,
            ];

            if ($variantId > 0 && $product->variants()->where('id', $variantId)->exists()) {
                $variant = $product->variants()->find($variantId);
                $variant->update($variantAttributes);
                $keptVariantIds[] = $variant->id;
            } else {
                $variant = $product->variants()->create($variantAttributes);
                $keptVariantIds[] = $variant->id;
            }
        }

        // Delete any variants that were removed
        if ($keptVariantIds !== []) {
            $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
        } else {
            $product->variants()->delete();
        }
    }

    /**
     * @param  array{url?: string, cloudinary_public_id?: string|null, is_uploaded_to_cloudinary?: bool, uploaded_image_id?: int}|null  $image
     * @return array<string, mixed>
     */
    private function primaryImagePayload(?array $image): array
    {
        if ($image === null) {
            return [
                'image' => null,
                'image_cloudinary_public_id' => null,
                'image_is_uploaded_to_cloudinary' => false,
                'image_uploaded_image_id' => null,
            ];
        }

        return [
            'image' => $image['url'] ?? null,
            'image_cloudinary_public_id' => $image['cloudinary_public_id'] ?? null,
            'image_is_uploaded_to_cloudinary' => (bool) ($image['is_uploaded_to_cloudinary'] ?? false),
            'image_uploaded_image_id' => $image['uploaded_image_id'] ?? null,
        ];
    }
}
