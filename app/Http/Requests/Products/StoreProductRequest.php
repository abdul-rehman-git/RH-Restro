<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug'),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku'),
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

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->safe()
            ->except(['images', 'existing_images', 'feature_points_text', 'variants']);

        $payload['slug'] = Str::slug((string) (($payload['slug'] ?? null) ?: $payload['title']));
        $payload['stock_quantity'] = $payload['stock_quantity'] ?? 0;
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['feature_points'] = $this->featurePoints();

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function variantsData(): array
    {
        return array_values(array_filter(
            $this->input('variants', []),
            fn (mixed $v): bool => is_array($v) && ! empty($v['name'])
        ));
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function imageFiles(): array
    {
        return array_values(array_filter($this->file('images', [])));
    }

    /**
     * @return array<int, string>
     */
    public function existingImages(): array
    {
        return collect($this->validated('existing_images', []))
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function featurePoints(): array
    {
        return Collection::make(preg_split('/\r\n|\r|\n/', (string) $this->validated('feature_points_text', '')))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }
}
