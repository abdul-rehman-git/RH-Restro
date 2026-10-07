<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name'),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug'),
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

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $slug = trim((string) $this->input('slug', ''));

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($slug !== '' ? $slug : $name),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->safe()
            ->except(['image', 'banner_image', 'remove_image', 'remove_banner_image']);

        $payload['slug'] = Str::slug((string) (($payload['slug'] ?? null) ?: $payload['name']));
        $payload['sort_order'] = $payload['sort_order'] ?? 0;

        return $payload;
    }

    public function imageFile(): ?UploadedFile
    {
        return $this->file('image');
    }

    public function bannerImageFile(): ?UploadedFile
    {
        return $this->file('banner_image');
    }

    public function shouldRemoveImage(): bool
    {
        return $this->boolean('remove_image');
    }

    public function shouldRemoveBannerImage(): bool
    {
        return $this->boolean('remove_banner_image');
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
            'slug.unique' => 'A category with this name already exists.',
        ];
    }
}
