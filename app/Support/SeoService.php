<?php

declare(strict_types=1);

namespace App\Support;

class SeoService
{
    public static function tags(string $page, array $data = [], ?string $canonical = null): array
    {
        $siteName = $data['site_name'] ?? config('app.name', 'RH Restro');
        $defaultDescription = $data['default_description'] ?? 'Discover exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.';
        $defaultImage = $data['default_og_image'] ?? null;
        $appUrl = $data['url'] ?? config('app.url');
        $url = $canonical ?: url()->current();

        $title = match ($page) {
            'home' => $siteName,
            'shop' => "Our Menu — {$siteName}",
            'product' => ($data['product']['title'] ?? 'Dish') . " — {$siteName}",
            'about' => "Our Story — {$siteName}",
            'contact' => "Contact Us — {$siteName}",
            'cart' => "Your Order — {$siteName}",
            'checkout' => "Checkout — {$siteName}",
            'custom-order' => "Table Reservations & Catering — {$siteName}",
            'reviews' => !empty($data['product']['title'])
                ? "{$data['product']['title']} Reviews — {$siteName}"
                : "Guest Reviews — {$siteName}",
            'order-tracking' => "Order Tracking — {$siteName}",
            'sign-in' => "Sign In — {$siteName}",
            default => "{$page} — {$siteName}",
        };

        $description = $data['description'] ?? $defaultDescription;

        $ogType = match ($page) {
            'product' => 'product',
            'about' => 'article',
            'contact' => 'website',
            default => 'website',
        };

        $robots = match ($page) {
            'cart', 'checkout', 'sign-in', 'order-tracking', 'my-account' => 'noindex, nofollow',
            default => 'index, follow',
        };

        $image = $data['image'] ?? $defaultImage;

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'robots' => $robots,
            'og' => [
                'title' => $title,
                'description' => $description,
                'image' => $image,
                'url' => $url,
                'type' => $ogType,
                'site_name' => $siteName,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $image,
            ],
        ];
    }

    public static function organizationJsonLd(array $site): array
    {
        $logo = $site['logo_url'] ?? null;
        $socialLinks = $site['social_links'] ?? [];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site['name'] ?? config('app.name', 'RH Commerce'),
            'url' => config('app.url'),
            'description' => $site['tagline'] ?? '',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $site['phone'] ?? '',
                'email' => $site['email'] ?? '',
                'contactType' => 'customer service',
            ],
        ];

        if ($logo) {
            $jsonLd['logo'] = $logo;
            $jsonLd['image'] = $logo;
        }

        $socialUrls = array_values(array_filter([
            $socialLinks['facebook'] ?? null,
            $socialLinks['instagram'] ?? null,
            $socialLinks['twitter'] ?? null,
        ]));

        if ($socialUrls) {
            $jsonLd['sameAs'] = $socialUrls;
        }

        if ($site['address'] ?? null) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address'],
            ];
        }

        return $jsonLd;
    }

    public static function websiteJsonLd(array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site['name'] ?? config('app.name', 'RH Commerce'),
            'url' => config('app.url'),
            'description' => $site['tagline'] ?? '',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => config('app.url') . '/shop?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function localBusinessJsonLd(array $site): array
    {
        $businessHours = $site['business_hours'] ?? [];

        $hours = [];
        foreach ($businessHours as $entry) {
            $days = explode(' - ', $entry['day'] ?? '');
            $hoursSpecification = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $days,
                'opens' => explode(' - ', $entry['hours'] ?? '')[0] ?? '09:00',
                'closes' => explode(' - ', $entry['hours'] ?? '')[1] ?? '18:00',
            ];
            if (count($days) === 1) {
                $hoursSpecification['dayOfWeek'] = $days[0];
            }
            $hours[] = $hoursSpecification;
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $site['name'] ?? config('app.name', 'RH Commerce'),
            'url' => config('app.url'),
            'telephone' => $site['phone'] ?? '',
            'email' => $site['email'] ?? '',
            'image' => $site['logo_url'] ?? null,
            'priceRange' => '$$',
        ];

        if ($site['address'] ?? null) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address'],
            ];
        }

        if ($hours) {
            $jsonLd['openingHoursSpecification'] = $hours;
        }

        return $jsonLd;
    }

    public static function breadcrumbJsonLd(array $crumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($crumbs as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $crumb['label'],
                'item' => $crumb['url'],
            ];
            $position++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public static function productJsonLd(array $product): array
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['title'] ?? '',
            'description' => $product['description'] ?? $product['short_description'] ?? '',
            'sku' => $product['slug'] ?? '',
        ];

        if ($product['images'][0] ?? null) {
            $jsonLd['image'] = $product['images'];
        }

        $jsonLd['offers'] = [
            '@type' => 'Offer',
            'url' => config('app.url') . '/product/' . ($product['slug'] ?? ''),
            'priceCurrency' => 'USD',
            'price' => $product['price'] ?? 0,
            'availability' => ($product['in_stock'] ?? false)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
        ];

        if ($product['rating'] ?? 0 > 0) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product['rating'],
                'reviewCount' => $product['reviews_count'] ?? 0,
                'bestRating' => 5,
            ];
        }

        return $jsonLd;
    }

    public static function articleJsonLd(array $about): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $about['hero_title'] ?? 'About Us',
            'description' => $about['hero_subtitle'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $about['artist_name'] ?? 'RH Team',
            ],
            'image' => $about['artist_image_url'] ?? null,
            'articleBody' => $about['story_content'] ?? '',
        ];
    }

    public static function faqJsonLd(array $steps): array
    {
        $mainEntity = [];

        foreach ($steps as $step) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $step['title'] ?? '',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $step['description'] ?? '',
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}
