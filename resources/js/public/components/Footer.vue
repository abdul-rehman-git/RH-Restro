<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const site = computed(() => usePage().props.site);

const props = defineProps({
    quickLinks: {
        type: Array,
        default: () => [
            { label: 'Menu', href: '/shop' },
            { label: 'Track Order', href: '/order-tracking' },
            { label: 'Table & Catering', href: '/custom-order' },
            { label: 'Our Story', href: '/about' },
            { label: 'Guest Reviews', href: '/reviews' },
            { label: 'Contact', href: '/contact' },
        ],
    },
    customerServiceLinks: {
        type: Array,
        default: () => [
            { label: 'Delivery Policy', href: '/shipping-policy' },
            { label: 'Order & Dining FAQ', href: '/faq' },
            { label: 'Guest Reviews', href: '/reviews' },
            { label: 'Privacy Policy', href: '/privacy-policy' },
            { label: 'Terms of Service', href: '/terms' },
            { label: 'Sitemap', href: '/sitemap' },
        ],
    },
    bottomLinks: {
        type: Array,
        default: () => [
            { label: 'Privacy Policy', href: '/privacy-policy' },
            { label: 'Terms of Service', href: '/terms' },
            { label: 'FAQ', href: '/faq' },
            { label: 'Sitemap', href: '/sitemap' },
            { label: 'XML Sitemap', href: '/sitemap.xml' },
        ],
    },
});

const socialItems = computed(() => {
    const links = site.value?.social_links || {};
    return [
        { key: 'facebook', href: links.facebook, icon: 'facebook' },
        { key: 'instagram', href: links.instagram, icon: 'instagram' },
        { key: 'twitter', href: links.twitter, icon: 'twitter' },
    ].filter((item) => item.href);
});

const socialIcons = {
    facebook:
        'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
    instagram:
        'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.949.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
    twitter:
        'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
};

const currentYear = new Date().getFullYear();

const brandParts = computed(() => {
    const raw = (site.value?.name || 'RH Restro').trim();
    const parts = raw.split(/\s+/);
    if (parts.length > 1) {
        return {
            prefix: parts[0],
            suffix: parts.slice(1).join(' ')
        };
    }
    return {
        prefix: 'RH',
        suffix: raw.replace(/^RH/i, '').trim() || 'Restro'
    };
});
</script>

<template>
    <footer class="bg-card border-t border-border mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-6 sm:gap-8">
                <!-- Brand Info (Spans 2 cols on mobile, 1 col on desktop) -->
                <div class="col-span-2 lg:col-span-1 pr-2">
                    <div class="flex items-center space-x-2.5 mb-2.5 sm:mb-4">
                        <img :src="site?.footer_logo_url || site?.logo_url || '/rh-restro-logo.png?v=20261007_v1'" :alt="site?.name || 'RH Restro'"
                            class="h-8 sm:h-10 w-auto max-w-[110px] object-contain shrink-0 drop-shadow-sm" />
                        <span class="text-base sm:text-xl font-extrabold font-heading">
                            <span class="text-foreground dark:text-white font-extrabold">{{ brandParts.prefix }}</span>
                            <span class="ml-1.5 bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 bg-clip-text text-transparent font-extrabold">{{ brandParts.suffix }}</span>
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-muted-foreground mb-3 sm:mb-4 line-clamp-2 sm:line-clamp-none">
                        {{ site?.footer_text }}
                    </p>

                    <div v-if="socialItems.length" class="flex space-x-2.5">
                        <a v-for="item in socialItems" :key="item.key" :href="item.href" target="_blank"
                            rel="noreferrer"
                            class="public-interactive flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-full bg-muted transition-luxury group hover:bg-amber-500/20">
                            <span class="text-muted-foreground group-hover:text-amber-500">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path :d="socialIcons[item.icon]" />
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Quick Links (Col 1 on mobile grid) -->
                <div class="col-span-1">
                    <h3 class="text-xs sm:text-base font-bold uppercase tracking-wider text-foreground mb-2 sm:mb-4">
                        Quick Links
                    </h3>
                    <ul class="space-y-1.5 sm:space-y-2">
                        <li v-for="link in props.quickLinks" :key="link.href">
                            <Link :href="link.href"
                                class="text-xs sm:text-sm text-muted-foreground hover:text-amber-500 transition-colors">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Customer Service (Col 2 on mobile grid) -->
                <div class="col-span-1">
                    <h3 class="text-xs sm:text-base font-bold uppercase tracking-wider text-foreground mb-2 sm:mb-4">
                        Customer Service
                    </h3>
                    <ul class="space-y-1.5 sm:space-y-2">
                        <li v-for="link in props.customerServiceLinks" :key="link.href">
                            <a :href="link.href"
                                class="text-xs sm:text-sm text-muted-foreground hover:text-amber-500 transition-colors">
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Us (Spans 2 cols on mobile, 1 col on desktop) -->
                <div class="col-span-2 lg:col-span-1 pt-2 lg:pt-0 border-t border-border/40 lg:border-t-0">
                    <h3 class="text-xs sm:text-base font-bold uppercase tracking-wider text-foreground mb-2 sm:mb-4">
                        Contact Us
                    </h3>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 sm:gap-3">
                        <li v-if="site?.address" class="flex items-start space-x-2.5">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-xs sm:text-sm text-muted-foreground">{{ site?.address }}</span>
                        </li>
                        <li v-if="site?.phone" class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <a :href="`tel:${site?.phone}`"
                                class="text-xs sm:text-sm text-muted-foreground hover:text-amber-500 transition-colors">{{
                                site?.phone
                                }}</a>
                        </li>
                        <li v-if="site?.email" class="flex items-center space-x-2.5">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a :href="`mailto:${site?.email}`"
                                class="text-xs sm:text-sm text-muted-foreground hover:text-amber-500 transition-colors truncate">{{
                                site?.email
                                }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="mt-6 sm:mt-12 pt-4 sm:pt-8 border-t border-border">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 text-center sm:text-left">
                    <p class="text-xs text-muted-foreground">
                        &copy; {{ currentYear }} {{ site?.copyright_text }}
                    </p>
                    <div v-if="props.bottomLinks.length" class="flex items-center space-x-4 sm:space-x-6">
                        <a v-for="link in props.bottomLinks" :key="link.href" :href="link.href"
                            class="text-xs text-muted-foreground hover:text-amber-500 transition-colors">
                            {{ link.label }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
