<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContactInquiryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_inquiries_index_uses_the_expected_prop_contract(): void
    {
        $user = User::factory()->create();

        ContactInquiry::query()->create([
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'subject' => 'Custom frame quote',
            'message' => 'Please share options.',
            'status' => 'in_progress',
            'source_page' => 'contact',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contact-inquiries');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ContactInquiries/Index')
                ->has('contactInquiries.data', 1)
                ->where('contactInquiries.total', 1)
                ->where('contactInquiries.data.0.status', 'in_progress')
                ->where('contactInquiries.data.0.status_label', 'In Progress'));
    }

    public function test_contact_inquiry_show_uses_the_expected_prop_contract(): void
    {
        $user = User::factory()->create();

        $inquiry = ContactInquiry::query()->create([
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'phone' => '+92-300-1111111',
            'subject' => 'Wall art sizing',
            'message' => 'Need sizing guidance.',
            'status' => 'replied',
            'source_page' => 'product',
            'admin_notes' => 'Followed up on WhatsApp.',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/contact-inquiries/{$inquiry->id}");

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ContactInquiries/Show')
                ->where('contactInquiry.name', 'Sara')
                ->where('contactInquiry.status', 'replied')
                ->has('statusOptions', 4));
    }
}
