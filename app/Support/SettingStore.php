<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SettingStore
{
    public static function all(): array
    {
        return self::model()->data ?? [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(self::all(), $key, $default);
    }

    public static function put(string $key, mixed $value): array
    {
        $settings = self::model();
        $data = $settings->data ?? [];

        // Normalize WhatsApp number if being set
        if ($key === 'public_site.whatsapp' && is_string($value)) {
            $value = self::normalizeWhatsAppNumber($value);
        }

        data_set($data, $key, $value);

        $settings->data = $data;
        $settings->save();

        return $data;
    }

    public static function merge(string $key, array $values): array
    {
        $current = self::get($key, []);
        $merged = array_replace_recursive(
            is_array($current) ? $current : [],
            $values,
        );

        self::put($key, $merged);

        return $merged;
    }

    public static function publicSite(): array
    {
        return array_replace_recursive(self::publicSiteDefaults(), self::get('public_site', []));
    }

    public static function homePage(): array
    {
        return array_replace_recursive(self::homePageDefaults(), self::get('public_pages.home', []));
    }

    public static function aboutPage(): array
    {
        return array_replace_recursive(self::aboutPageDefaults(), self::get('public_pages.about', []));
    }

    public static function publicSiteDefaults(): array
    {
        return [
            'name' => 'RH Restro',
            'tagline' => 'Fine Dining & Gourmet Flavors',
            'logo' => '/rh-logo.svg',
            'footer_logo' => '/rh-logo.svg',
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
        ];
    }

    public static function homePageDefaults(): array
    {
        return [
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
        ];
    }

    public static function seoSettings(): array
    {
        return array_replace_recursive(self::seoSettingsDefaults(), self::get('seo', []));
    }

    public static function seoSettingsDefaults(): array
    {
        return [
            'default_description' => 'Experience exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.',
            'default_og_image' => '/rh-icon-512.png',
            'google_analytics_id' => '',
            'google_site_verification' => '',
        ];
    }

    /**
     * @return array{cloud_name: string, api_key: string, api_secret: string, folder: string}
     */
    public static function cloudinary(): array
    {
        $stored = self::get('cloudinary', []);
        $stored = is_array($stored) ? $stored : [];

        $defaults = self::cloudinaryDefaults();

        return [
            'cloud_name' => trim((string) ($stored['cloud_name'] ?? $defaults['cloud_name'])),
            'api_key' => trim((string) ($stored['api_key'] ?? $defaults['api_key'])),
            'api_secret' => trim((string) ($stored['api_secret'] ?? $defaults['api_secret'])),
            'folder' => trim((string) ($stored['folder'] ?? $defaults['folder'])) ?: 'rh-commerce',
        ];
    }

    /**
     * @return array{cloud_name: string, api_key: string, api_secret: string, folder: string}
     */
    public static function cloudinaryDefaults(): array
    {
        return [
            'cloud_name' => (string) config('services.cloudinary.cloud_name', ''),
            'api_key' => (string) config('services.cloudinary.api_key', ''),
            'api_secret' => (string) config('services.cloudinary.api_secret', ''),
            'folder' => (string) config('services.cloudinary.folder', 'rh-commerce'),
        ];
    }

    /**
     * Safe payload for the admin settings UI (never exposes the raw secret).
     *
     * @return array{cloud_name: string, api_key: string, folder: string, api_secret_set: bool, is_configured: bool}
     */
    public static function cloudinaryForAdmin(): array
    {
        $settings = self::cloudinary();

        return [
            'cloud_name' => $settings['cloud_name'],
            'api_key' => $settings['api_key'],
            'folder' => $settings['folder'],
            'api_secret_set' => $settings['api_secret'] !== '',
            'is_configured' => $settings['cloud_name'] !== ''
                && $settings['api_key'] !== ''
                && $settings['api_secret'] !== '',
        ];
    }

    public static function aboutPageDefaults(): array
    {
        return [
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
        ];
    }

    protected static function model(): Setting
    {
        if (! Schema::hasTable('setting')) {
            return new Setting([
                'data' => [],
            ]);
        }

        return Setting::query()->firstOrCreate([], [
            'data' => [],
        ]);
    }

    /**
     * Normalize WhatsApp number to standard format (country code + number, no symbols)
     * Example: +92 343 662 5008 or 03436625008 -> 923436625008
     */
    protected static function normalizeWhatsAppNumber(string $number): string
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/\D+/', '', $number);

        // If starts with 0 (local format), replace with country code 92
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '92' . substr($cleaned, 1);
        }

        // If doesn't start with country code, assume 92
        if (!str_starts_with($cleaned, '92') && !str_starts_with($cleaned, '1')) {
            $cleaned = '92' . $cleaned;
        }

        return $cleaned;
    }
}
