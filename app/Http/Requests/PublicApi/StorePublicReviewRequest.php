<?php

declare(strict_types=1);

namespace App\Http\Requests\PublicApi;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StorePublicReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:20'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => [ImageUpload::validationRule(ImageUpload::reviewMaxKilobytes())],
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function imageFiles(): array
    {
        return array_values(array_filter($this->file('images', [])));
    }
}
