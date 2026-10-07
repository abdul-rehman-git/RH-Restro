<?php

declare(strict_types=1);

namespace App\Http\Requests\PublicContent;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateAboutPageRequest extends FormRequest
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
            'story_content' => ['required', 'string'],
            'artist_name' => ['required', 'string', 'max:255'],
            'artist_quote' => ['nullable', 'string'],
            'stats_text' => ['nullable', 'string'],
            'process_steps_text' => ['nullable', 'string'],
            'values_text' => ['nullable', 'string'],
            'artist_image' => ['nullable', ImageUpload::validationRule()],
            'remove_artist_image' => ['nullable', 'boolean'],
        ];
    }

    public function artistImageFile(): ?UploadedFile
    {
        return $this->file('artist_image');
    }
}
