<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactInquiries\UpdateContactInquiryRequest;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactInquiryController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('ContactInquiries/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'contactInquiries' => ContactInquiry::query()
                ->search($request->string('search')->toString())
                ->status($request->string('status')->toString())
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (ContactInquiry $inquiry): array => [
                    'id' => $inquiry->id,
                    'name' => $inquiry->name,
                    'email' => $inquiry->email,
                    'subject' => $inquiry->subject,
                    'status' => $inquiry->status,
                    'status_label' => collect(ContactInquiry::STATUS_OPTIONS)
                        ->firstWhere('value', $inquiry->status)['label'] ?? $inquiry->status,
                    'source_page' => $inquiry->source_page,
                    'created_at' => $inquiry->created_at?->format('M d, Y'),
                ]),
        ]);
    }

    public function show(ContactInquiry $contactInquiry): Response
    {
        return Inertia::render('ContactInquiries/Show', [
            'contactInquiry' => [
                'id' => $contactInquiry->id,
                'name' => $contactInquiry->name,
                'email' => $contactInquiry->email,
                'phone' => $contactInquiry->phone,
                'subject' => $contactInquiry->subject,
                'message' => $contactInquiry->message,
                'source_page' => $contactInquiry->source_page,
                'status' => $contactInquiry->status,
                'admin_notes' => $contactInquiry->admin_notes,
                'responded_at' => $contactInquiry->responded_at?->toDateTimeString(),
                'created_at' => $contactInquiry->created_at?->toDayDateTimeString(),
            ],
            'statusOptions' => ContactInquiry::STATUS_OPTIONS,
        ]);
    }

    public function update(UpdateContactInquiryRequest $request, ContactInquiry $contactInquiry): RedirectResponse
    {
        $contactInquiry->update($request->validated());

        return redirect()
            ->route('contact-inquiries.show', $contactInquiry)
            ->with('status', 'Inquiry updated successfully.');
    }
}
