<?php

declare(strict_types=1);

namespace App\Http\Requests\ContactInquiries;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactInquiryRequest extends FormRequest
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
            'status' => ['required', 'in:new,in_progress,replied,closed'],
            'admin_notes' => ['nullable', 'string'],
            'responded_at' => ['nullable', 'date'],
        ];
    }
}
