<?php

declare(strict_types=1);

namespace App\Http\Requests\PublicContent;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateHomePageRequest extends FormRequest
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
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string'],
            'stats_text' => ['nullable', 'string'],
            'hero_image' => ['nullable', ImageUpload::validationRule()],
            'remove_hero_image' => ['nullable', 'boolean'],
        ];
    }

    public function heroImageFile(): ?UploadedFile
    {
        return $this->file('hero_image');
    }
}
