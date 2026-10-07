<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Support\ImageUpload;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'materials' => ['nullable', 'string', 'max:255'],
            'feature_points_text' => ['nullable', 'string'],
            'care_instructions' => ['nullable', 'string'],
            'shipping_note' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => [ImageUpload::validationRule()],
            'existing_images' => ['nullable', 'array', 'max:8'],
            'existing_images.*' => ['string', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'gte:price'],
            'badge_label' => ['nullable', 'string', 'max:80'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.image' => ['nullable', 'string', 'max:2048'],
            'variants.*.image_file' => ['nullable', 'file', ImageUpload::validationRule()],
        ];
    }
}
