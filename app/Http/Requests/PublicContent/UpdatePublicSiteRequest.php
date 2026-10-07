<?php

declare(strict_types=1);

namespace App\Http\Requests\PublicContent;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdatePublicSiteRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'map_embed_url' => ['nullable', 'url', 'max:1000'],
            'footer_text' => ['nullable', 'string'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'business_hours_text' => ['nullable', 'string'],
            'logo' => ['nullable', ImageUpload::validationRule()],
            'footer_logo' => ['nullable', ImageUpload::validationRule()],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_footer_logo' => ['nullable', 'boolean'],
        ];
    }

    public function logoFile(): ?UploadedFile
    {
        return $this->file('logo');
    }

    public function footerLogoFile(): ?UploadedFile
    {
        return $this->file('footer_logo');
    }
}
