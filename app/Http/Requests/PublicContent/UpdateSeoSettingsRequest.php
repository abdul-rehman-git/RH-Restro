<?php

declare(strict_types=1);

namespace App\Http\Requests\PublicContent;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateSeoSettingsRequest extends FormRequest
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
            'default_description' => ['nullable', 'string', 'max:500'],
            'default_og_image' => ['nullable', ImageUpload::validationRule()],
            'remove_default_og_image' => ['nullable', 'boolean'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function defaultOgImageFile(): ?UploadedFile
    {
        return $this->file('default_og_image');
    }
}
