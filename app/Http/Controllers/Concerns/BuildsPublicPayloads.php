<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use App\Models\Review;
use App\Support\ImageUrl;
use App\Support\SeoService;
use App\Support\SettingStore;

trait BuildsPublicPayloads
{
    protected function sitePayload(): array
    {
        $site = SettingStore::publicSite();

        return [
            'name' => $site['name'],
            'tagline' => $site['tagline'],
            'logo_url' => $this->assetUrl($site['logo']['url'] ?? $site['logo_path'] ?? null) ?: '/rh-restro-logo.png?v=20261007_v1',
            'footer_logo_url' => $this->assetUrl($site['footer_logo']['url'] ?? $site['footer_logo_path'] ?? null) ?: '/rh-restro-logo.png?v=20261007_v1',
            'email' => $site['email'],
            'phone' => $site['phone'],
            'whatsapp' => $site['whatsapp'],
            'whatsapp_url' => $site['whatsapp']
                ? 'https://wa.me/'.preg_replace('/\D+/', '', $site['whatsapp'])
                : null,
            'address' => $site['address'],
            'map_embed_url' => $site['map_embed_url'],
            'footer_text' => $site['footer_text'],
            'copyright_text' => $site['copyright_text'],
            'social_links' => $site['social_links'] ?? [],
            'business_hours' => $site['business_hours'] ?? [],
        ];
    }

    protected function homeContentPayload(array $home): array
    {
        $heroImage = $home['hero_image']['url'] ?? $home['hero_image_path'] ?? (is_string($home['hero_image'] ?? null) ? $home['hero_image'] : null);
        $heroBgImage = $home['hero_bg_image']['url'] ?? $home['hero_bg_image_path'] ?? (is_string($home['hero_bg_image'] ?? null) ? $home['hero_bg_image'] : null);

        return [
            'hero_badge' => $home['hero_badge'] ?? 'Authentic Flavors & Gourmet Dining',
            'hero_title' => $home['hero_title'] ?? '',
            'hero_subtitle' => $home['hero_subtitle'] ?? '',
            'stats' => $home['stats'] ?? [],
            'hero_image_url' => $this->assetUrl($heroImage),
            'hero_bg_image_url' => $this->assetUrl($heroBgImage),
            'primary_cta_label' => $home['primary_cta_label'] ?? 'Explore Menu',
            'primary_cta_link' => $home['primary_cta_link'] ?? '/shop',
            'secondary_cta_label' => $home['secondary_cta_label'] ?? 'Reserve Table',
            'secondary_cta_link' => $home['secondary_cta_link'] ?? '/custom-order',
            'categories_title' => $home['categories_title'] ?? 'Explore Our Menu',
            'categories_subtitle' => $home['categories_subtitle'] ?? 'Discover mouth-watering dishes crafted with love, passion, and the finest fresh ingredients.',
            'products_title' => $home['products_title'] ?? "Chef's Special Dishes",
            'products_subtitle' => $home['products_subtitle'] ?? 'Our most celebrated culinary creations, prepared hot and fresh for every order.',
            'testimonials_title' => $home['testimonials_title'] ?? 'What Diners Say',
            'testimonials_subtitle' => $home['testimonials_subtitle'] ?? 'Unfiltered experiences and heartfelt reviews from our valued food lovers.',
            'cta_title' => $home['cta_title'] ?? 'Planning a Party or Private Catering?',
            'cta_subtitle' => $home['cta_subtitle'] ?? 'Let us cater your special celebrations, corporate lunches, or private dinners with customized chef menus and premium hospitality.',
            'cta_primary_label' => $home['cta_primary_label'] ?? 'Book Catering & Events',
            'cta_primary_link' => $home['cta_primary_link'] ?? '/custom-order',
            'cta_secondary_label' => $home['cta_secondary_label'] ?? 'Contact Us',
            'cta_secondary_link' => $home['cta_secondary_link'] ?? '/contact',
        ];
    }

    protected function aboutContentPayload(array $about): array
    {
        return [
            'hero_title' => $about['hero_title'] ?? '',
            'hero_subtitle' => $about['hero_subtitle'] ?? '',
            'story_content' => $about['story_content'] ?? '',
            'artist_name' => $about['artist_name'] ?? '',
            'artist_quote' => $about['artist_quote'] ?? '',
            'stats' => $about['stats'] ?? [],
            'process_steps' => $about['process_steps'] ?? [],
            'values' => $about['values'] ?? [],
            'artist_image_url' => $this->assetUrl($about['artist_image']['url'] ?? $about['artist_image_path'] ?? null),
        ];
    }

    protected function productCardPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'price' => (float) $product->price,
            'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
            'image' => $product->imageUrl(),
            'category' => $product->category?->name,
            'category_slug' => $product->category?->slug,
            'short_description' => $product->short_description,
            'badge_label' => $product->badge_label,
            'rating' => round((float) ($product->approved_reviews_avg_rating ?? 0), 1),
            'reviews_count' => (int) ($product->approved_reviews_count ?? 0),
            'in_stock' => (int) $product->stock_quantity > 0,
            'stock_quantity' => (int) $product->stock_quantity,
        ];
    }

    protected function reviewImageUrls(Review $review): array
    {
        return array_map(fn (string $path): ?string => $this->assetUrl($path), $review->imagePaths());
    }

    protected function assetUrl(?string $path): ?string
    {
        return ImageUrl::resolve($path);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function seoPayload(string $page, array $extra = []): array
    {
        $site = $this->sitePayload();
        $seoSettings = SettingStore::seoSettings();

        $data = [
            'site_name' => $site['name'],
            'url' => config('app.url'),
            'default_description' => $seoSettings['default_description'] ?? '',
            'default_og_image' => $this->assetUrl($seoSettings['default_og_image']['url'] ?? $seoSettings['default_og_image_path'] ?? null),
            ...$extra,
        ];

        return SeoService::tags($page, $data);
    }
}
