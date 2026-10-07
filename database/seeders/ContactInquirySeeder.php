<?php

namespace Database\Seeders;

use App\Models\ContactInquiry;
use Illuminate\Database\Seeder;

class ContactInquirySeeder extends Seeder
{
    /**
     * Seed the application's contact inquiries.
     */
    public function run(): void
    {
        $inquiries = [
            [
                'name' => 'Marcus Vance',
                'email' => 'marcus.vance@techcorp.io',
                'phone' => '+1 (555) 342-9981',
                'subject' => 'Corporate Annual Dinner Catering for 60 Guests',
                'message' => 'We are organizing our annual executive dinner next month and would like to reserve private hall seating along with a 4-course gourmet dinner (steaks, artisanal pastas, and desserts). Please share package pricing and availability.',
                'source_page' => 'custom-order',
                'status' => 'new',
                'admin_notes' => 'Corporate dinner inquiry for 60 guests. Custom banquet menu and pricing prepared.',
                'responded_at' => null,
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'elena.rostova@designstudio.com',
                'phone' => '+1 (555) 782-1204',
                'subject' => 'VIP Table Reservation for Birthday Celebration',
                'message' => 'Looking to book a prime candlelight table for 8 persons this coming Saturday at 8:00 PM. Could we also request a custom molten lava birthday dessert platter with message scripting?',
                'source_page' => 'contact',
                'status' => 'in_progress',
                'admin_notes' => 'Table booked for Saturday. Kitchen notified for custom birthday dessert scripting.',
                'responded_at' => null,
            ],
            [
                'name' => 'Daniel Kim',
                'email' => 'daniel.kim@venturehaus.co',
                'phone' => '+1 (555) 912-3847',
                'subject' => 'Live BBQ Grilling Catering for Family Gathering',
                'message' => 'Do you provide on-site live BBQ chef catering for private garden parties? We have around 35 family members and love your sizzling lamb chops and kebabs.',
                'source_page' => 'contact',
                'status' => 'replied',
                'admin_notes' => 'Shared outdoor live BBQ grilling packages and setup details with client.',
                'responded_at' => now()->subDays(2),
            ],
        ];

        foreach ($inquiries as $inquiry) {
            ContactInquiry::updateOrCreate(
                [
                    'email' => $inquiry['email'],
                    'subject' => $inquiry['subject'],
                ],
                $inquiry,
            );
        }
    }
}
