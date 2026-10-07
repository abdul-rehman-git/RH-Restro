<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0A0A0A">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Primary meta tags -->
        <meta name="description" content="{{ $seoDefaults['default_description'] ?? 'Discover curated smart gadgets, lifestyle accessories, home essentials, and modern goods with seamless shopping and reliable delivery at RH.' }}" inertia />
        <meta name="robots" content="index, follow" inertia />

        <!-- Open Graph default tags (overridden by @inertiaHead on each page) -->
        <meta property="og:site_name" content="{{ config('app.name', 'RH Commerce') }}" inertia />
        <meta property="og:type" content="website" inertia />
        <meta property="og:url" content="{{ url()->current() }}" inertia />
        <meta name="twitter:card" content="summary_large_image" inertia />

        <!-- Google Site Verification -->
        @if (!empty($seoDefaults['google_site_verification']))
            <meta name="google-site-verification" content="{{ $seoDefaults['google_site_verification'] }}" />
        @endif

        <!-- Favicon and Icons -->
        <link rel="icon" type="image/svg+xml" href="/rh-icon.svg?v=rh4" />
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=rh4" />
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=rh4" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?v=rh4" />
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=rh4" sizes="180x180" />
        <link rel="icon" type="image/png" sizes="192x192" href="/rh-icon-192.png?v=rh4" />
        <link rel="icon" type="image/png" sizes="512x512" href="/rh-icon-512.png?v=rh4" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:500,600,700,800,900|figtree:400,500,600&display=swap" rel="stylesheet" />

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
