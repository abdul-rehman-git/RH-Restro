<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    Map,
    UtensilsCrossed,
    BookOpen,
    HelpCircle,
    ShieldCheck,
    FileCode,
    ExternalLink,
    ChevronRight,
    Sparkles,
    CalendarCheck,
    PhoneCall,
    Award
} from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();
const site = computed(() => page.props.site);
const categories = computed(() => page.props.categories || []);
const featuredDishes = computed(() => page.props.featuredDishes || []);
const xmlSitemapUrl = computed(() => page.props.xmlSitemapUrl || '/sitemap.xml');

const generalPages = [
    { label: 'Home Page', href: '/', desc: 'Welcome page, chef highlights, signature dishes, and dining experience' },
    { label: 'Explore Full Menu', href: '/shop', desc: 'Browse our complete culinary selection and filter by category or price' },
    { label: 'Table Reservations & Catering', href: '/custom-order', desc: 'Book private dining, party reservations, and custom event catering' },
    { label: 'Track Food Order', href: '/order-tracking', desc: 'Live status tracking for online food orders and hot kitchen prep' },
    { label: 'Guest Reviews & Ratings', href: '/reviews', desc: 'Verified dining reviews and ratings from our valued food lovers' },
    { label: 'Our Story & Culinary Heritage', href: '/about', desc: 'Learn about our passion, master chefs, and farm-fresh ingredients' },
    { label: 'Contact Us & Host Desk', href: '/contact', desc: 'Phone numbers, WhatsApp support, email, address, and interactive map' },
];

const policyPages = [
    { label: 'Dining & Ordering FAQ', href: '/faq', desc: 'Answers about delivery times, dietary options, reservations, and payments' },
    { label: 'Terms of Service', href: '/terms', desc: 'Dining policies, ordering guidelines, and website conditions' },
    { label: 'Privacy Policy', href: '/privacy-policy', desc: 'How we respect, secure, and handle your personal data' },
    { label: 'Delivery & Takeaway Policy', href: '/shipping-policy', desc: 'Hot food packaging, delivery radii, and estimated timeframes' },
    { label: 'Refund & Cancellation Policy', href: '/returns', desc: 'Satisfaction guarantee, cancellation rules, and refund resolution' },
];

const technicalFeeds = [
    { label: 'XML Sitemap (Search Engines)', href: '/sitemap.xml', desc: 'Standard sitemaps.org XML feed with Google Image tags', isExternal: true },
    { label: 'Robots.txt Directives', href: '/robots.txt', desc: 'Crawl instructions and allowed search bot index paths', isExternal: true },
    { label: 'Web App Manifest (PWA)', href: '/manifest.json', desc: 'Progressive Web App installation and icon manifest', isExternal: true },
];
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background">
            <section class="py-8 sm:py-16 bg-background">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader
                        tag="h1"
                        title="Website Directory & Sitemap"
                        subtitle="A complete overview of all pages, categories, and culinary offerings across our restaurant website."
                        centered
                    />

                    <!-- XML Sitemap Callout Card -->
                    <div class="mt-8 p-4 sm:p-6 rounded-2xl border border-amber-500/30 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-500 shrink-0">
                                <FileCode class="w-6 h-6" />
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-foreground">Machine-Readable XML Sitemap</h2>
                                <p class="text-xs sm:text-sm text-muted-foreground">
                                    For Google Search Console, Bing Webmaster, and automated crawler indexing.
                                </p>
                            </div>
                        </div>
                        <a
                            :href="xmlSitemapUrl"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs sm:text-sm transition-colors shadow-sm"
                        >
                            <span>Open sitemap.xml</span>
                            <ExternalLink class="w-4 h-4" />
                        </a>
                    </div>

                    <!-- Sitemap Sections Grid -->
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Primary Dining & Experience Pages -->
                        <div class="rounded-2xl border border-border bg-card p-6 flex flex-col">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10 text-amber-500">
                                    <BookOpen class="w-5 h-5" />
                                </div>
                                <h2 class="text-lg font-bold text-foreground font-heading">Primary Restaurant Pages</h2>
                            </div>
                            <ul class="space-y-3 divide-y divide-border/50">
                                <li v-for="item in generalPages" :key="item.href" class="pt-3 first:pt-0">
                                    <Link :href="item.href" class="group block">
                                        <div class="flex items-center justify-between text-sm sm:text-base font-medium text-foreground group-hover:text-amber-500 transition-colors">
                                            <span>{{ item.label }}</span>
                                            <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-amber-500 transition-transform group-hover:translate-x-0.5" />
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ item.desc }}</p>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Menu Categories -->
                        <div class="rounded-2xl border border-border bg-card p-6 flex flex-col">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10 text-amber-500">
                                    <UtensilsCrossed class="w-5 h-5" />
                                </div>
                                <div class="flex-1 flex items-center justify-between">
                                    <h2 class="text-lg font-bold text-foreground font-heading">Menu Categories</h2>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-500 font-medium">
                                        {{ categories.length }} Categories
                                    </span>
                                </div>
                            </div>

                            <div v-if="categories.length > 0" class="space-y-3 divide-y divide-border/50">
                                <div v-for="cat in categories" :key="cat.slug" class="pt-3 first:pt-0">
                                    <Link :href="cat.url || `/shop?category=${cat.slug}`" class="group block">
                                        <div class="flex items-center justify-between text-sm sm:text-base font-medium text-foreground group-hover:text-amber-500 transition-colors">
                                            <span>{{ cat.name }}</span>
                                            <div class="flex items-center gap-2">
                                                <span v-if="cat.products_count" class="text-xs text-muted-foreground">
                                                    {{ cat.products_count }} dishes
                                                </span>
                                                <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-amber-500 transition-transform group-hover:translate-x-0.5" />
                                            </div>
                                        </div>
                                        <p v-if="cat.description" class="text-xs text-muted-foreground mt-0.5 line-clamp-1">
                                            {{ cat.description }}
                                        </p>
                                    </Link>
                                </div>
                            </div>
                            <div v-else class="text-xs text-muted-foreground py-4">
                                Browse our full culinary menu at <Link href="/shop" class="text-amber-500 underline">Our Menu</Link>.
                            </div>
                        </div>

                        <!-- Featured & Signature Dishes -->
                        <div v-if="featuredDishes.length > 0" class="rounded-2xl border border-border bg-card p-6 flex flex-col md:col-span-2">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10 text-amber-500">
                                    <Sparkles class="w-5 h-5" />
                                </div>
                                <div class="flex-1 flex items-center justify-between">
                                    <h2 class="text-lg font-bold text-foreground font-heading">Signature Chef Specialties</h2>
                                    <Link href="/shop" class="text-xs text-amber-500 hover:underline flex items-center gap-1">
                                        <span>View all in menu</span>
                                        <ChevronRight class="w-3.5 h-3.5" />
                                    </Link>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <Link
                                    v-for="dish in featuredDishes"
                                    :key="dish.slug"
                                    :href="dish.url || `/product/${dish.slug}`"
                                    class="p-3 rounded-xl border border-border/60 hover:border-amber-500/50 bg-background/50 hover:bg-muted/40 transition-all group flex items-center justify-between"
                                >
                                    <div class="min-w-0 pr-2">
                                        <span class="block text-sm font-medium text-foreground group-hover:text-amber-500 transition-colors truncate">
                                            {{ dish.title }}
                                        </span>
                                        <span v-if="dish.category" class="block text-xs text-muted-foreground">
                                            {{ dish.category }}
                                        </span>
                                    </div>
                                    <span v-if="dish.price" class="text-xs font-bold text-amber-500 whitespace-nowrap">
                                        ${{ Number(dish.price).toFixed(2) }}
                                    </span>
                                </Link>
                            </div>
                        </div>

                        <!-- Policies, Terms & Customer Care -->
                        <div class="rounded-2xl border border-border bg-card p-6 flex flex-col">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10 text-amber-500">
                                    <ShieldCheck class="w-5 h-5" />
                                </div>
                                <h2 class="text-lg font-bold text-foreground font-heading">Customer Care & Policies</h2>
                            </div>
                            <ul class="space-y-3 divide-y divide-border/50">
                                <li v-for="item in policyPages" :key="item.href" class="pt-3 first:pt-0">
                                    <Link :href="item.href" class="group block">
                                        <div class="flex items-center justify-between text-sm sm:text-base font-medium text-foreground group-hover:text-amber-500 transition-colors">
                                            <span>{{ item.label }}</span>
                                            <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-amber-500 transition-transform group-hover:translate-x-0.5" />
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ item.desc }}</p>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Technical Feeds & Search Engine Resources -->
                        <div class="rounded-2xl border border-border bg-card p-6 flex flex-col">
                            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-border">
                                <div class="p-2 rounded-lg bg-primary/10 text-amber-500">
                                    <FileCode class="w-5 h-5" />
                                </div>
                                <h2 class="text-lg font-bold text-foreground font-heading">Feeds & Machine Indexing</h2>
                            </div>
                            <ul class="space-y-3 divide-y divide-border/50">
                                <li v-for="item in technicalFeeds" :key="item.href" class="pt-3 first:pt-0">
                                    <a :href="item.href" target="_blank" rel="noopener" class="group block">
                                        <div class="flex items-center justify-between text-sm sm:text-base font-medium text-foreground group-hover:text-amber-500 transition-colors">
                                            <span>{{ item.label }}</span>
                                            <ExternalLink class="w-4 h-4 text-muted-foreground group-hover:text-amber-500 transition-colors" />
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ item.desc }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>

