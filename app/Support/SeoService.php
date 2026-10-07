<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

class SeoService
{
    /**
     * Build comprehensive SEO tags payload for a page.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function tags(string $page, array $data = [], ?string $canonical = null): array
    {
        $siteName = $data['site_name'] ?? config('app.name', 'RH Restro');
        $defaultDescription = $data['default_description'] ?? 'Experience exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.';
        $defaultImage = $data['default_og_image'] ?? null;
        $url = $canonical ?: url()->current();

        $title = $data['title'] ?? match ($page) {
            'home' => "{$siteName} — Fine Dining & Gourmet Restaurant",
            'shop' => "Our Menu — Handcrafted Dishes & Specials | {$siteName}",
            'product' => ($data['product']['title'] ?? 'Dish') . " — {$siteName}",
            'about' => "Our Story & Culinary Heritage — {$siteName}",
            'contact' => "Contact & Table Reservations — {$siteName}",
            'cart' => "Your Order Bag — {$siteName}",
            'checkout' => "Checkout & Order Confirmation — {$siteName}",
            'custom-order' => "Table Reservations & Private Catering — {$siteName}",
            'reviews' => !empty($data['product']['title'])
                ? "{$data['product']['title']} Reviews — {$siteName}"
                : "Guest Reviews & Dining Feedback — {$siteName}",
            'order-tracking' => "Track Your Order Status — {$siteName}",
            'sign-in' => "Customer Sign In — {$siteName}",
            'faq' => "Frequently Asked Questions — {$siteName}",
            'terms' => "Terms of Service & Dining Policies — {$siteName}",
            'privacy-policy' => "Privacy Policy — {$siteName}",
            'shipping-policy' => "Delivery & Takeaway Policy — {$siteName}",
            'returns' => "Refund & Cancellation Policy — {$siteName}",
            'sitemap' => "Website Directory & Sitemap — {$siteName}",
            default => "{$page} — {$siteName}",
        };

        $description = $data['description'] ?? match ($page) {
            'home' => $defaultDescription,
            'shop' => "Explore our handcrafted gourmet menu at {$siteName}. Featuring artisan wood-fired pizzas, slow-grilled prime steaks, smash burgers, and fresh mocktails.",
            'product' => !empty($data['product']['short_description'])
                ? (string) $data['product']['short_description']
                : (!empty($data['product']['description'])
                    ? Str::limit(strip_tags((string) $data['product']['description']), 160)
                    : "Savor our chef-crafted {$data['product']['title']} at {$siteName}. Freshly made with authentic farm-fresh ingredients and culinary passion."),
            'about' => "Discover the culinary journey, artisan kitchen standards, and master chefs behind {$siteName}. Dedicated to extraordinary taste and heartfelt hospitality.",
            'contact' => "Get in touch with {$siteName} for table bookings, event catering, menu questions, or general inquiries. Call, email, or visit us today.",
            'custom-order' => "Reserve your table or plan custom event catering with {$siteName}. Personalized menus, private dining rooms, and premium hospitality for any event.",
            'reviews' => "Read authentic dining reviews and guest ratings for {$siteName}. See what food lovers say about our signature recipes, service, and ambiance.",
            'faq' => "Find answers to frequently asked questions about online food ordering, hot delivery times, table reservations, catering packages, and food hygiene at {$siteName}.",
            'terms' => "Review the terms and conditions for ordering, table reservations, payments, and dining at {$siteName}.",
            'privacy-policy' => "Learn how {$siteName} protects your personal information, contact details, payment transactions, and account privacy.",
            'shipping-policy' => "Learn about our hot food delivery areas, packaging integrity, estimated arrival times, and takeaway options at {$siteName}.",
            'returns' => "Understand our customer satisfaction guarantee, cancellation windows, and refund procedures for dine-in and online food orders at {$siteName}.",
            'sitemap' => "Explore the full directory of gourmet menus, dishes, table reservations, and customer support pages for {$siteName}.",
            default => $defaultDescription,
        };

        $ogType = match ($page) {
            'product' => 'restaurant.menu_item',
            'about' => 'article',
            'contact' => 'restaurant.restaurant',
            default => 'website',
        };

        $robots = match ($page) {
            'cart', 'checkout', 'sign-in', 'order-tracking', 'my-account' => 'noindex, nofollow',
            default => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        };

        $image = $data['image'] ?? $defaultImage;
        if ($image && !Str::startsWith($image, ['http://', 'https://'])) {
            $image = url(ltrim($image, '/'));
        }
        if (!$image) {
            $image = url('/rh-icon-512.png');
        }

        $keywords = $data['keywords'] ?? 'RH Restro, fine dining, restaurant, artisan pizzas, gourmet burgers, sizzling steaks, pasta, mocktails, halal food, table reservations, catering, online food ordering';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $url,
            'robots' => $robots,
            'og' => [
                'title' => $title,
                'description' => $description,
                'image' => $image,
                'url' => $url,
                'type' => $ogType,
                'site_name' => $siteName,
                'locale' => 'en_US',
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $image,
            ],
            'jsonLd' => $data['jsonLd'] ?? [],
        ];
    }

    /**
     * Organization structured data (Schema.org).
     *
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function organizationJsonLd(array $site): array
    {
        $siteName = $site['name'] ?? config('app.name', 'RH Restro');
        $logo = self::ensureAbsoluteUrl($site['logo_url'] ?? '/rh-logo.png');
        $socialLinks = $site['social_links'] ?? [];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => config('app.url') . '/#organization',
            'name' => $siteName,
            'url' => config('app.url'),
            'description' => $site['tagline'] ?? 'Fine Dining & Gourmet Flavors',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $site['phone'] ?? '+1 (555) 234-8900',
                'email' => $site['email'] ?? 'reservations@rhrestro.com',
                'contactType' => 'customer service',
                'areaServed' => 'US',
                'availableLanguage' => ['English'],
            ],
        ];

        if ($logo) {
            $jsonLd['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
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

        if (!empty($site['address'])) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => str_replace("\n", ", ", $site['address']),
                'addressLocality' => 'New York',
                'addressRegion' => 'NY',
                'postalCode' => '10001',
                'addressCountry' => 'US',
            ];
        }

        return $jsonLd;
    }

    /**
     * WebSite structured data (Schema.org).
     *
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function websiteJsonLd(array $site): array
    {
        $siteName = $site['name'] ?? config('app.name', 'RH Restro');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => config('app.url') . '/#website',
            'name' => $siteName,
            'url' => config('app.url'),
            'description' => $site['tagline'] ?? 'Fine Dining & Gourmet Flavors',
            'inLanguage' => 'en-US',
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

    /**
     * Restaurant / LocalBusiness structured data (Schema.org).
     *
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function localBusinessJsonLd(array $site): array
    {
        $siteName = $site['name'] ?? config('app.name', 'RH Restro');
        $businessHours = $site['business_hours'] ?? [];
        $logo = self::ensureAbsoluteUrl($site['logo_url'] ?? '/rh-logo.png');

        $hours = [];
        $dayMap = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday',
        ];

        foreach ($businessHours as $entry) {
            $daysString = $entry['day'] ?? '';
            $dayParts = explode(' - ', $daysString);
            $opens = '11:00';
            $closes = '23:30';

            if (!empty($entry['hours'])) {
                $timeParts = explode(' - ', $entry['hours']);
                if (isset($timeParts[0])) {
                    $parsed = strtotime($timeParts[0]);
                    if ($parsed !== false) $opens = date('H:i', $parsed);
                }
                if (isset($timeParts[1])) {
                    $parsed = strtotime($timeParts[1]);
                    if ($parsed !== false) $closes = date('H:i', $parsed);
                }
            }

            $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            if (count($dayParts) === 2) {
                $start = trim($dayParts[0]);
                $end = trim($dayParts[1]);
                $startIdx = array_search($start, $allDays, true);
                $endIdx = array_search($end, $allDays, true);
                if ($startIdx !== false && $endIdx !== false && $startIdx <= $endIdx) {
                    $dayList = array_slice($allDays, $startIdx, $endIdx - $startIdx + 1);
                } else {
                    $dayList = array_values(array_filter([$start, $end]));
                }
            } elseif (!empty($dayParts[0])) {
                $dayList = [trim($dayParts[0])];
            } else {
                $dayList = $allDays;
            }

            $hours[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => count($dayList) === 1 ? $dayList[0] : $dayList,
                'opens' => $opens,
                'closes' => $closes,
            ];
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            '@id' => config('app.url') . '/#restaurant',
            'name' => $siteName,
            'url' => config('app.url'),
            'telephone' => $site['phone'] ?? '+1 (555) 234-8900',
            'email' => $site['email'] ?? 'reservations@rhrestro.com',
            'priceRange' => '$$',
            'servesCuisine' => [
                'American',
                'Italian',
                'Steakhouse',
                'Gourmet Burgers',
                'Artisan Pizza',
                'Fine Dining',
            ],
            'hasMenu' => config('app.url') . '/shop',
            'acceptsReservations' => 'True',
        ];

        if ($logo) {
            $jsonLd['image'] = $logo;
        }

        if (!empty($site['address'])) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => str_replace("\n", ", ", $site['address']),
                'addressLocality' => 'New York',
                'addressRegion' => 'NY',
                'postalCode' => '10001',
                'addressCountry' => 'US',
            ];
        }

        if ($hours) {
            $jsonLd['openingHoursSpecification'] = $hours;
        }

        $socialLinks = $site['social_links'] ?? [];
        $socialUrls = array_values(array_filter([
            $socialLinks['facebook'] ?? null,
            $socialLinks['instagram'] ?? null,
            $socialLinks['twitter'] ?? null,
        ]));
        if ($socialUrls) {
            $jsonLd['sameAs'] = $socialUrls;
        }

        return $jsonLd;
    }

    /**
     * BreadcrumbList structured data (Schema.org).
     *
     * @param  array<int, array{label: string, url: string}>  $crumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(array $crumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($crumbs as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $crumb['label'],
                'item' => self::ensureAbsoluteUrl($crumb['url']),
            ];
            $position++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Product / MenuItem structured data (Schema.org).
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public static function productJsonLd(array $product, ?string $siteName = null): array
    {
        $siteName = $siteName ?? config('app.name', 'RH Restro');
        $description = !empty($product['short_description'])
            ? (string) $product['short_description']
            : (!empty($product['description'])
                ? Str::limit(strip_tags((string) $product['description']), 300)
                : (string) ($product['title'] ?? ''));

        $images = [];
        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $img) {
                if ($img) {
                    $images[] = self::ensureAbsoluteUrl($img);
                }
            }
        }
        if (empty($images) && !empty($product['image'])) {
            $images[] = self::ensureAbsoluteUrl($product['image']);
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => ['Product', 'MenuItem'],
            'name' => $product['title'] ?? '',
            'description' => $description,
            'sku' => (string) ($product['sku'] ?? $product['slug'] ?? ''),
            'brand' => [
                '@type' => 'Brand',
                'name' => $siteName,
            ],
        ];

        if (!empty($product['category'])) {
            $jsonLd['category'] = $product['category'];
        }

        if ($images) {
            $jsonLd['image'] = $images;
        }

        $price = (float) ($product['price'] ?? 0);
        $jsonLd['offers'] = [
            '@type' => 'Offer',
            'url' => config('app.url') . '/product/' . ($product['slug'] ?? ''),
            'priceCurrency' => 'USD',
            'price' => number_format($price, 2, '.', ''),
            'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => ($product['in_stock'] ?? false)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Restaurant',
                'name' => $siteName,
            ],
        ];

        $rating = (float) ($product['rating'] ?? 0);
        $reviewsCount = (int) ($product['reviews_count'] ?? 0);
        if ($rating > 0 && $reviewsCount > 0) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($rating, 1, '.', ''),
                'reviewCount' => $reviewsCount,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        return $jsonLd;
    }

    /**
     * ItemList structured data (Schema.org) for Menu listing.
     *
     * @param  array<int, array<string, mixed>>  $itemsList
     * @return array<string, mixed>
     */
    public static function itemListJsonLd(array $itemsList, string $name, string $url): array
    {
        $elements = [];
        $position = 1;

        foreach ($itemsList as $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'url' => config('app.url') . '/product/' . ($item['slug'] ?? ''),
                'name' => $item['title'] ?? '',
            ];
            $position++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'url' => self::ensureAbsoluteUrl($url),
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }

    /**
     * AboutPage / Article structured data (Schema.org).
     *
     * @param  array<string, mixed>  $about
     * @return array<string, mixed>
     */
    public static function articleJsonLd(array $about): array
    {
        $image = self::ensureAbsoluteUrl($about['artist_image_url'] ?? null);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'headline' => $about['hero_title'] ?? 'Our Culinary Story & Heritage',
            'description' => $about['hero_subtitle'] ?? '',
            'url' => config('app.url') . '/about',
            'mainEntity' => [
                '@type' => 'Restaurant',
                'name' => config('app.name', 'RH Restro'),
                'description' => $about['story_content'] ?? '',
            ],
        ];

        if ($image) {
            $jsonLd['image'] = $image;
        }

        return $jsonLd;
    }

    /**
     * FAQPage structured data (Schema.org).
     *
     * @param  array<int, array<string, mixed>>  $faqs
     * @return array<string, mixed>
     */
    public static function faqJsonLd(array $faqs): array
    {
        $mainEntity = [];

        foreach ($faqs as $faq) {
            $q = $faq['question'] ?? $faq['q'] ?? $faq['title'] ?? '';
            $a = $faq['answer'] ?? $faq['a'] ?? $faq['description'] ?? '';

            if ($q && $a) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name' => trim(strip_tags((string) $q)),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => trim(strip_tags((string) $a)),
                    ],
                ];
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }

    /**
     * Ensure a URL is fully qualified with scheme and domain.
     */
    public static function ensureAbsoluteUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $trimmed = trim($url);
        if (Str::startsWith($trimmed, ['http://', 'https://'])) {
            return $trimmed;
        }

        return url(ltrim($trimmed, '/'));
    }
}
