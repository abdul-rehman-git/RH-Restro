<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Support\ImageUpload;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', ImageUpload::validationRule()],
            'banner_image' => ['nullable', ImageUpload::validationRule()],
            'remove_image' => ['nullable', 'boolean'],
            'remove_banner_image' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
        ];
    }
}
