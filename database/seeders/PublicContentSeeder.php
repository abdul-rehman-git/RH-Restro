<?php

namespace Database\Seeders;

use App\Support\SettingStore;
use Illuminate\Database\Seeder;

class PublicContentSeeder extends Seeder
{
    /**
     * Seed the application's public content settings.
     */
    public function run(): void
    {
        SettingStore::put('public_site', [
            'name' => 'RH Restro',
            'tagline' => 'Fine Dining & Gourmet Flavors',
            'logo' => null,
            'footer_logo' => null,
            'email' => 'reservations@rhrestro.com',
            'phone' => '+1 (555) 234-8900',
            'whatsapp' => '+15552348900',
            'address' => "Food Street & Gourmet Avenue\nLuxury Dining Quarter\nNew York, NY 10001",
            'map_embed_url' => null,
            'footer_text' => 'Experience the finest culinary art, authentic wood-fired pizzas, sizzling steaks, artisan burgers, and handcrafted mocktails made with farm-fresh ingredients.',
            'copyright_text' => 'RH Restro. All rights reserved.',
            'social_links' => [
                'facebook' => 'https://facebook.com/rhrestro',
                'instagram' => 'https://instagram.com/rhrestro',
                'twitter' => 'https://twitter.com/rhrestro',
            ],
            'business_hours' => [
                ['day' => 'Monday - Thursday', 'hours' => '12:00 PM - 11:30 PM'],
                ['day' => 'Friday - Sunday', 'hours' => '12:00 PM - 01:00 AM'],
            ],
        ]);

        SettingStore::put('public_pages.home', [
            'hero_badge' => 'Authentic Flavors & Gourmet Dining',
            'hero_title' => 'Savor Exceptional Taste & Culinary Craft',
            'hero_subtitle' => 'Indulge in an unforgettable dining experience with chef-crafted artisan recipes, premium farm-fresh ingredients, and sizzling specialties made to perfection.',
            'hero_bg_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920&q=80',
            'hero_image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=1000&q=85',
            'primary_cta_label' => 'Explore Menu',
            'primary_cta_link' => '/shop',
            'secondary_cta_label' => 'Reserve Table',
            'secondary_cta_link' => '/custom-order',
            'stats' => [
                ['label' => 'Signature Dishes', 'value' => '80+'],
                ['label' => 'Happy Diners', 'value' => '35k+'],
                ['label' => 'Fresh & Halal', 'value' => '100%'],
            ],
            'categories_title' => 'Explore Our Menu',
            'categories_subtitle' => 'Discover mouth-watering dishes crafted with love, passion, and the finest fresh ingredients.',
            'products_title' => "Chef's Special Dishes",
            'products_subtitle' => 'Our most celebrated culinary creations, prepared hot and fresh for every order.',
            'testimonials_title' => 'What Diners Say',
            'testimonials_subtitle' => 'Unfiltered experiences and heartfelt reviews from our valued food lovers.',
            'cta_title' => 'Planning a Party or Private Catering?',
            'cta_subtitle' => 'Let us cater your special celebrations, corporate lunches, or private dinners with customized chef menus and premium hospitality.',
            'cta_primary_label' => 'Book Catering & Events',
            'cta_primary_link' => '/custom-order',
            'cta_secondary_label' => 'Contact Us',
            'cta_secondary_link' => '/contact',
        ]);

        SettingStore::put('public_pages.about', [
            'hero_badge' => 'Our Culinary Story',
            'hero_title' => 'Passion for Flavor & Heartfelt Hospitality',
            'hero_subtitle' => 'At RH Restro, every recipe is a celebration of authentic taste, master craftsmanship, and the pure joy of sharing great food.',
            'story_title' => 'Our Heritage & Kitchen Philosophy',
            'story_content' => "RH Restro was born out of a genuine passion for honest, flavorful food. We believe that dining should be an experience that awakens the senses and brings people closer together.\n\nFrom hand-stretched Neapolitan pizzas and slow-grilled prime steaks to juicy artisan smash burgers and signature beverages, each item is cooked with utmost care and fresh ingredients.\n\nWhether you are joining us for a cozy family dinner or ordering sizzling hot delivery to your doorstep, we promise uncompromising quality in every bite.",
            'artist_name' => 'Executive Chef & Culinary Team',
            'artist_quote' => 'Great food is crafted with patience, passion, and the finest ingredients.',
            'artist_image' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=1000&q=80',
            'stats' => [
                ['label' => 'Signature Dishes', 'value' => '80+'],
                ['label' => 'Happy Foodies', 'value' => '35,000+'],
                ['label' => 'Expert Chefs', 'value' => '12+'],
                ['label' => 'Guest Rating', 'value' => '4.9/5'],
            ],
            'process_steps' => [
                ['title' => 'Farm-Fresh Sourcing', 'description' => 'We select only organic vegetables, prime halal meats, and imported authentic cheeses.'],
                ['title' => 'Artisanal Preparation', 'description' => 'Every marinade, dough, and sauce is crafted in-house from scratch by our master chefs.'],
                ['title' => 'Hot & Prompt Service', 'description' => 'Served fresh to your table or dispatched in insulated heat-sealed packaging.'],
            ],
            'values' => [
                ['title' => 'Taste Excellence', 'description' => 'Every dish is crafted to deliver rich, authentic flavors with premium ingredients.'],
                ['title' => 'Uncompromising Hygiene', 'description' => 'Our kitchens operate under strict food safety and sanitized cleanliness protocols.'],
                ['title' => 'Warm Hospitality', 'description' => 'We welcome every guest like family with attentive and gracious dining service.'],
                ['title' => 'Fresh Every Day', 'description' => 'Never frozen, never pre-packed — all ingredients are sourced fresh daily.'],
            ],
        ]);

        SettingStore::put('public_pages.contact', [
            'title' => 'Get in Touch',
            'subtitle' => "Have a question about an order, product details, or custom inquiries? We're here to help.",
            'form_title' => 'Send us a Message',
            'whatsapp_title' => 'Chat on WhatsApp',
            'whatsapp_text' => 'For quick questions and direct customer support',
            'whatsapp_button_label' => 'Start Chat',
        ]);
    }
}
