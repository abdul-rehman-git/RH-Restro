<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0A0A0A">

        @php
            $pageSeo = $page['props']['seo'] ?? null;
            $siteProps = $page['props']['site'] ?? null;
            $siteName = $siteProps['name'] ?? $page['props']['businessSettings']['name'] ?? config('app.name', 'RH Restro');
            $pageTitle = $pageSeo['title'] ?? "{$siteName} — Fine Dining & Gourmet Restaurant";
            $pageDescription = $pageSeo['description'] ?? ($seoDefaults['default_description'] ?? 'Experience exquisite fine dining, artisan pizzas, gourmet burgers, sizzling steaks, and warm hospitality at RH Restro.');
            $pageKeywords = $pageSeo['keywords'] ?? 'RH Restro, fine dining, restaurant, artisan pizzas, gourmet burgers, sizzling steaks, mocktails, halal food, table reservations, catering';
            $pageRobots = $pageSeo['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
            $pageCanonical = $pageSeo['canonical'] ?? url()->current();
            $pageImage = $pageSeo['og']['image'] ?? ($seoDefaults['default_og_image'] ?? url('/rh-icon-512.png'));
            $pageOgType = $pageSeo['og']['type'] ?? 'website';
        @endphp

        <title inertia>{{ $pageTitle }}</title>

        <!-- Primary Meta Tags -->
        <meta name="description" content="{{ $pageDescription }}" head-key="description" inertia />
        <meta name="keywords" content="{{ $pageKeywords }}" head-key="keywords" inertia />
        <meta name="robots" content="{{ $pageRobots }}" head-key="robots" inertia />
        <link rel="canonical" href="{{ $pageCanonical }}" head-key="canonical" inertia />

        <!-- Open Graph / Facebook / WhatsApp / Social Cards -->
        <meta property="og:site_name" content="{{ $siteName }}" head-key="og:site_name" inertia />
        <meta property="og:type" content="{{ $pageOgType }}" head-key="og:type" inertia />
        <meta property="og:url" content="{{ $pageCanonical }}" head-key="og:url" inertia />
        <meta property="og:title" content="{{ $pageSeo['og']['title'] ?? $pageTitle }}" head-key="og:title" inertia />
        <meta property="og:description" content="{{ $pageSeo['og']['description'] ?? $pageDescription }}" head-key="og:description" inertia />
        <meta property="og:image" content="{{ $pageImage }}" head-key="og:image" inertia />
        <meta property="og:image:alt" content="{{ $pageSeo['og']['title'] ?? $pageTitle }}" head-key="og:image:alt" inertia />
        <meta property="og:locale" content="en_US" head-key="og:locale" inertia />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" head-key="twitter:card" inertia />
        <meta name="twitter:title" content="{{ $pageSeo['twitter']['title'] ?? $pageTitle }}" head-key="twitter:title" inertia />
        <meta name="twitter:description" content="{{ $pageSeo['twitter']['description'] ?? $pageDescription }}" head-key="twitter:description" inertia />
        <meta name="twitter:image" content="{{ $pageImage }}" head-key="twitter:image" inertia />

        <!-- Google Site Verification -->
        @if (!empty($seoDefaults['google_site_verification']))
            <meta name="google-site-verification" content="{{ $seoDefaults['google_site_verification'] }}" />
        @endif

        <!-- Preconnect & DNS-Prefetch for Speed & Core Web Vitals -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="https://images.unsplash.com">
        <link rel="preconnect" href="https://res.cloudinary.com" crossorigin>
        <link rel="dns-prefetch" href="https://res.cloudinary.com">

        <!-- Favicon and App Icons -->
        <link rel="icon" type="image/svg+xml" href="/rh-icon.svg?v=rh4" />
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=rh4" />
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=rh4" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=rh4" />
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=rh4" sizes="180x180" />
        <link rel="icon" type="image/png" sizes="192x192" href="/rh-icon-192.png?v=rh4" />
        <link rel="icon" type="image/png" sizes="512x512" href="/rh-icon-512.png?v=rh4" />

        <!-- Sitemap and PWA Manifest Discovery -->
        <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}" />
        <link rel="manifest" href="/manifest.json" />

        <!-- Typography -->
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:500,600,700,800,900|figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Structured Data (JSON-LD) for Search Engine Crawlers -->
        @if (!empty($pageSeo['jsonLd']))
            @foreach ($pageSeo['jsonLd'] as $schema)
                <script type="application/ld+json" data-rh-seo="server">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
            @endforeach
        @endif

        <!-- Google Analytics -->
        @if (!empty($seoDefaults['google_analytics_id']))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoDefaults['google_analytics_id'] }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $seoDefaults['google_analytics_id'] }}');
            </script>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
