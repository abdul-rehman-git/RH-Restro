<?php

declare(strict_types=1);

namespace App\Http\Requests\Reviews;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreReviewRequest extends FormRequest
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
            'product_id' => ['nullable', 'exists:products,id'],
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string'],
            'existing_images' => ['nullable', 'array', 'max:5'],
            'existing_images.*' => ['string', 'max:2048'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [ImageUpload::validationRule(ImageUpload::reviewMaxKilobytes())],
            'reviewed_at' => ['nullable', 'date'],
            'helpful_count' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['required', 'boolean'],
            'is_approved' => ['required', 'boolean'],
            'is_verified_purchase' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('product_id') === '') {
            $this->merge(['product_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->safe()->except(['images', 'existing_images']);
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
}
