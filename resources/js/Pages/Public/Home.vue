<script setup>
import { computed, inject, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    ArrowRight,
    Award,
    Utensils,
    Clock,
    ShieldCheck,
    Sparkles,
    Flame,
    ShoppingBag,
    Calendar,
    PhoneCall,
    Star,
    ChefHat,
    CheckCircle2
} from "lucide-vue-next";
import SeoHead from "@/Components/SeoHead.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import ProductCard from "@/public/components/ProductCard.vue";
import SectionHeader from "@/public/components/SectionHeader.vue";
import { formatPrice } from "@/public/utils/formatPrice";

const page = usePage();
const pageData = computed(() => page.props.home || {});
const actualTheme = inject("actualTheme", ref("light"));
const isDark = computed(() => {
    const val = typeof actualTheme === "object" && actualTheme !== null && "value" in actualTheme
        ? actualTheme.value
        : actualTheme;
    return val === "dark";
});

const heroBgImage = computed(() => {
    if (isDark.value) {
        return pageData.value.hero_bg_image_url || "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920&q=85";
    }
    return pageData.value.hero_bg_image_light_url || "https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1920&q=85";
});

const categories = computed(() => pageData.value.categories || []);
const products = computed(() => pageData.value.products || []);
const testimonials = computed(() => pageData.value.testimonials || []);
const stats = computed(() => pageData.value.stats || []);

const heroFeaturedDish = computed(() => {
    return products.value.find(p => p.slug === 'double-truffle-smashed-burger') || products.value[0] || null;
});

const activeCategorySlug = ref('all');

const filteredDishes = computed(() => {
    if (activeCategorySlug.value === 'all') {
        return products.value;
    }
    return products.value.filter(p => {
        if (p.category_slug === activeCategorySlug.value) return true;
        if (p.category && p.category.toLowerCase().includes(activeCategorySlug.value.replace(/-/g, ' '))) return true;
        return false;
    });
});

const heroTitleParts = computed(() => {
    const title = (pageData.value.hero_title || "Savor Exceptional Taste & Culinary Craft").trim();
    const words = title.split(/\s+/).filter(Boolean);

    if (words.length <= 1) {
        return {
            lead: title,
            accent: "",
        };
    }

    return {
        lead: words.slice(0, -1).join(" "),
        accent: words.at(-1) || "",
    };
});

const valueProps = [
    {
        title: "Farm-Fresh Sourcing",
        description: "100% organic local produce, prime certified meats, and pure hand-ground spices in every dish.",
        icon: Utensils,
    },
    {
        title: "Master Culinary Chefs",
        description: "Artisan recipes crafted with 15+ years of kitchen mastery and genuine passion for gastronomy.",
        icon: ChefHat,
    },
    {
        title: "Hot Express Delivery",
        description: "Delivered piping hot to your table or doorstep in thermal-sealed heat-locking packaging.",
        icon: Clock,
    },
    {
        title: "100% Halal & Hygienic",
        description: "Prepared in certified pristine kitchens following the strictest international food safety standards.",
        icon: ShieldCheck,
    },
];

const promoBanners = [
    {
        badge: "WEEKEND SPECIAL",
        title: "20% OFF BURGERS",
        subtitle: "Double Angus smashed patties with melted vintage cheddar & black truffle aioli.",
        image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80",
        link: "/shop?category=gourmet-burgers",
        cta: "Order Burger",
        tag: "Best Seller",
    },
    {
        badge: "CHEF'S SIGNATURE",
        title: "PRIME ANGUS CUTS",
        subtitle: "28-day dry-aged charbroiled ribeye served with garlic herb butter & truffle potato mash.",
        image: "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80",
        link: "/shop?category=signature-mains",
        cta: "Explore Steaks",
        tag: "Premium Cut",
    },
    {
        badge: "FREE EXPRESS DELIVERY",
        title: "WOOD-FIRED PIZZAS",
        subtitle: "48-hour fermented sourdough baked at 900°F with sweet San Marzano tomatoes & fresh basil.",
        image: "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80",
        link: "/shop?category=pizzas-pastas",
        cta: "Order Pizza",
        tag: "Stone Baked",
    },
];

const galleryImages = [
    {
        title: "Prime Angus Ribeye",
        category: "Steakhouse",
        image: "https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80",
    },
    {
        title: "Double Truffle Smashed Burger",
        category: "Gourmet Burgers",
        image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80",
    },
    {
        title: "Neapolitan Margherita",
        category: "Wood-Fired Pizza",
        image: "https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?w=800&q=80",
    },
    {
        title: "Sizzling BBQ Platter",
        category: "Flame Grills",
        image: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80",
    },
    {
        title: "Belgian Molten Lava Cake",
        category: "Desserts",
        image: "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80",
    },
    {
        title: "Fresh Mint Margarita",
        category: "Mocktails",
        image: "https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&q=80",
    },
];
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen overflow-x-hidden">
            <!-- 1. MODERN BURGOS RESTAURANT HERO SECTION -->
            <section
                :class="[
                    'relative min-h-[90vh] lg:min-h-screen flex items-center py-16 sm:py-24 lg:py-32 overflow-hidden transition-colors duration-500',
                    isDark
                        ? 'bg-[#0B0F19] text-white'
                        : 'bg-gradient-to-b from-[#FFFDF9] via-[#FAF6F0] to-[#F5EFE6] text-slate-900'
                ]"
            >
                <!-- Full-bleed background ambiance image with warm culinary vignette -->
                <div class="absolute inset-0 z-0">
                    <img
                        :key="isDark ? 'dark-hero' : 'light-hero'"
                        :src="heroBgImage"
                        alt="RH Restro Dining Ambiance"
                        :class="[
                            'w-full h-full object-cover object-center scale-105 transition-all duration-700',
                            isDark
                                ? 'opacity-40 mix-blend-luminosity'
                                : 'opacity-30 mix-blend-multiply filter contrast-105'
                        ]"
                    />
                    <!-- Multi-layered dark slate gradient overlays for high-contrast typography -->
                    <template v-if="isDark">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0B0F19] via-[#0B0F19]/90 to-[#0B0F19]/60" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-[#0B0F19]/80" />
                        <!-- Ambient warm fire glow -->
                        <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#EA580C]/20 rounded-full blur-3xl pointer-events-none" />
                        <div class="absolute top-1/3 right-0 w-[500px] h-[500px] bg-[#F59E0B]/15 rounded-full blur-3xl pointer-events-none" />
                    </template>
                    <template v-else>
                        <!-- Light mode subtle warm amber/cream overlays for bright daylight ambiance -->
                        <div class="absolute inset-0 bg-gradient-to-r from-[#FFFDF9]/95 via-[#FFFDF9]/85 to-[#FFFDF9]/40" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#FFFDF9] via-transparent to-[#FFFDF9]/70" />
                        <!-- Ambient sunlit golden glow -->
                        <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#F59E0B]/15 rounded-full blur-3xl pointer-events-none" />
                        <div class="absolute top-1/3 right-0 w-[500px] h-[500px] bg-[#EA580C]/10 rounded-full blur-3xl pointer-events-none" />
                    </template>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                    <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-10">
                        <!-- LEFT COLUMN: Punchy Burgos Headline & CTAs -->
                        <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="lg:col-span-7 space-y-6 sm:space-y-8">
                            <!-- Sizzling Top Pill Badge -->
                            <div
                                :class="[
                                    'inline-flex items-center gap-2.5 rounded-full border px-4 py-2 text-xs sm:text-sm font-bold tracking-wide backdrop-blur-md shadow-lg transition-colors',
                                    isDark
                                        ? 'border-[#F59E0B]/40 bg-[#F59E0B]/15 text-[#FDE68A]'
                                        : 'border-amber-500/30 bg-amber-500/10 text-amber-900 shadow-sm'
                                ]"
                            >
                                <span class="flex h-2.5 w-2.5 rounded-full bg-[#EA580C] animate-pulse" />
                                <Flame class="h-4 w-4 text-[#F59E0B]" />
                                <span>{{ pageData.hero_badge || 'HOT & FRESH GOURMET DINING' }}</span>
                            </div>

                            <!-- Bold Main Headline -->
                            <h1
                                :class="[
                                    'font-display text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] drop-shadow-sm transition-colors',
                                    isDark ? 'text-white' : 'text-slate-900'
                                ]"
                            >
                                {{ heroTitleParts.lead }}
                                <span v-if="heroTitleParts.accent" class="block text-amber-gradient mt-2 pb-1">
                                    {{ heroTitleParts.accent }}
                                </span>
                            </h1>

                            <!-- Subtitle Description -->
                            <p
                                :class="[
                                    'max-w-xl text-base sm:text-lg lg:text-xl leading-relaxed font-normal transition-colors',
                                    isDark ? 'text-slate-300' : 'text-slate-700'
                                ]"
                            >
                                {{ pageData.hero_subtitle || 'Indulge in an unforgettable dining experience with chef-crafted artisan recipes, 28-day aged prime cuts, and wood-fired delicacies made fresh to order.' }}
                            </p>

                            <!-- Modern Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                                <Link :href="pageData.primary_cta_link || '/shop'" class="btn-premium group text-base font-bold">
                                    <ShoppingBag class="h-5 w-5" />
                                    <span>{{ pageData.primary_cta_label || 'Order Online' }}</span>
                                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </Link>

                                <Link
                                    :href="pageData.secondary_cta_link || '/custom-order'"
                                    :class="[
                                        'btn-glass group text-base font-semibold backdrop-blur-md transition-colors',
                                        isDark
                                            ? 'border-white/20 bg-white/10 hover:bg-white/20 text-white'
                                            : 'border-slate-300 bg-white/80 hover:bg-white text-slate-800 shadow-sm'
                                    ]"
                                >
                                    <Calendar class="h-5 w-5 text-[#F59E0B]" />
                                    <span>{{ pageData.secondary_cta_label || 'Book a Table' }}</span>
                                </Link>
                            </div>

                            <!-- Live Perks / Trust Strip directly under Hero CTAs -->
                            <div
                                :class="[
                                    'pt-4 border-t grid grid-cols-3 gap-3 sm:gap-6 text-xs sm:text-sm transition-colors',
                                    isDark ? 'border-white/15 text-slate-300' : 'border-slate-300/70 text-slate-600'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-[#F59E0B]/20 flex items-center justify-center text-[#F59E0B] flex-shrink-0">
                                        ⚡
                                    </div>
                                    <span :class="isDark ? 'font-semibold text-white' : 'font-semibold text-slate-900'">30-Min Fast Delivery</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-[#F59E0B]/20 flex items-center justify-center text-[#F59E0B] flex-shrink-0">
                                        🥩
                                    </div>
                                    <span :class="isDark ? 'font-semibold text-white' : 'font-semibold text-slate-900'">100% Halal Angus</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-[#F59E0B]/20 flex items-center justify-center text-[#F59E0B] flex-shrink-0">
                                        ⭐
                                    </div>
                                    <span :class="isDark ? 'font-semibold text-white' : 'font-semibold text-slate-900'">4.9 Star Rating</span>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Burgos-Style Floating Dish Showcase -->
                        <div v-reveal="{ preset: 'zoom', duration: 760, delay: 120 }" class="lg:col-span-5 relative flex justify-center">
                            <div class="relative w-full max-w-sm sm:max-w-md">
                                <!-- Golden Aura Glow -->
                                <div class="absolute -inset-6 rounded-full bg-gradient-to-tr from-[#EA580C]/40 via-[#F59E0B]/25 to-transparent blur-3xl -z-10" />

                                <!-- Floating Dish Plate Card -->
                                <div
                                    :class="[
                                        'relative rounded-3xl border p-4 sm:p-5 shadow-2xl backdrop-blur-xl group transition-all duration-300',
                                        isDark
                                            ? 'border-[#F59E0B]/30 bg-[#131B2E]/90'
                                            : 'border-amber-500/25 bg-white/95 shadow-amber-950/10'
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'relative aspect-[4/3] rounded-2xl overflow-hidden border',
                                            isDark ? 'bg-slate-900 border-white/10' : 'bg-slate-100 border-slate-200'
                                        ]"
                                    >
                                        <img
                                            :src="heroFeaturedDish?.image || pageData.hero_image_url || 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80'"
                                            :alt="heroFeaturedDish?.title || 'Chef Signature Dish'"
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent" />

                                        <!-- Live badge on image -->
                                        <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full border border-[#F59E0B]/40 bg-black/80 px-3 py-1 text-xs font-bold text-[#FDE68A] backdrop-blur-md">
                                            <Sparkles class="h-3.5 w-3.5 text-[#F59E0B]" />
                                            Chef's Daily Special
                                        </div>

                                        <!-- Dish Details at bottom of photo -->
                                        <div class="absolute bottom-3 left-3 right-3 text-white">
                                            <h3 class="font-display text-lg sm:text-xl font-extrabold text-white">
                                                {{ heroFeaturedDish?.title || 'Double Truffle Smashed Burger' }}
                                            </h3>
                                            <p class="text-xs text-slate-300 line-clamp-1">
                                                {{ heroFeaturedDish?.short_description || 'Two crispy Angus patties, vintage cheddar & black truffle aioli' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Floating Price Sticker -->
                                    <div
                                        :class="[
                                            'absolute -top-4 -right-4 flex h-20 w-20 flex-col items-center justify-center rounded-full bg-gradient-to-br from-[#F59E0B] to-[#EA580C] text-white shadow-xl ring-4 transition-all',
                                            isDark ? 'ring-[#0B0F19]' : 'ring-white'
                                        ]"
                                    >
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-100">Only</span>
                                        <span class="text-xs sm:text-sm font-black tracking-tight leading-tight px-1 text-center">
                                            {{ heroFeaturedDish ? formatPrice(heroFeaturedDish.price) : 'Rs. 1,250' }}
                                        </span>
                                    </div>

                                    <!-- Bottom Action Strip -->
                                    <div class="mt-4 flex items-center justify-between pt-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                                            <span :class="['text-xs font-semibold', isDark ? 'text-slate-300' : 'text-slate-700']">Freshly Made To Order</span>
                                        </div>
                                        <Link :href="heroFeaturedDish ? `/product/${heroFeaturedDish.slug}` : '/shop'" class="group/btn inline-flex items-center gap-1.5 text-xs font-bold text-[#F59E0B] hover:text-[#EA580C] transition-colors">
                                            <span>Order Now</span>
                                            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover/btn:translate-x-1" />
                                        </Link>
                                    </div>
                                </div>

                                <!-- Floating Mini Review Card -->
                                <div
                                    :class="[
                                        'hidden sm:flex absolute -bottom-6 -left-6 items-center gap-3 rounded-2xl border p-3.5 shadow-2xl backdrop-blur-md transition-all',
                                        isDark
                                            ? 'border-white/15 bg-black/85 text-white'
                                            : 'border-amber-200/80 bg-white/95 text-slate-900 shadow-xl'
                                    ]"
                                >
                                    <img
                                        src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&q=80"
                                        alt="Diner Avatar"
                                        class="h-10 w-10 rounded-full object-cover border border-[#F59E0B]"
                                    />
                                    <div>
                                        <div class="flex items-center space-x-0.5">
                                            <Star v-for="i in 5" :key="i" class="h-3 w-3 fill-[#F59E0B] text-[#F59E0B]" />
                                        </div>
                                        <p :class="['text-xs font-semibold', isDark ? 'text-white' : 'text-slate-900']">"Best smash burger in town!"</p>
                                        <span :class="['text-[10px]', isDark ? 'text-slate-400' : 'text-slate-500']">Sophia M. — Verified Diner</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. BURGOS 3 PROMOTIONAL OFFER BANNERS -->
            <section class="py-12 sm:py-16 bg-background">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            v-for="banner in promoBanners"
                            :key="banner.title"
                            class="group relative overflow-hidden rounded-3xl border border-border/80 bg-card p-6 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#F59E0B]/50 flex flex-col justify-between min-h-[220px]"
                        >
                            <!-- Background Image with Dark Gradient -->
                            <div class="absolute inset-0 z-0">
                                <img
                                    :src="banner.image"
                                    :alt="banner.title"
                                    class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-108"
                                />
                                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/75 to-black/40" />
                            </div>

                            <!-- Content -->
                            <div class="relative z-10 space-y-2">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-gradient-to-r from-[#F59E0B] to-[#EA580C] text-white shadow-sm">
                                    {{ banner.badge }}
                                </span>
                                <h3 class="font-display text-2xl font-black text-white tracking-tight">
                                    {{ banner.title }}
                                </h3>
                                <p class="text-xs text-slate-200 line-clamp-2 max-w-[240px]">
                                    {{ banner.subtitle }}
                                </p>
                            </div>

                            <div class="relative z-10 pt-4">
                                <Link
                                    :href="banner.link"
                                    class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold text-black transition-colors hover:bg-[#F59E0B] hover:text-white shadow-md"
                                >
                                    <span>{{ banner.cta }}</span>
                                    <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. INTERACTIVE CATEGORY TABS & MENU SHOWCASE (BURGOS STYLE) -->
            <section :class="`py-12 sm:py-20 ${isDark ? 'bg-[#0B0F19]' : 'bg-slate-50'}`">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#F59E0B]/15 text-[#F59E0B] border border-[#F59E0B]/30 mb-3">
                            <Utensils class="h-3.5 w-3.5" />
                            Delicious Selections
                        </span>
                        <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-foreground tracking-tight mb-4">
                            Explore Our Gourmet Menu
                        </h2>
                        <p class="text-muted-foreground text-sm sm:text-base">
                            Handcrafted with farm-fresh organic produce, prime cuts, and authentic secret recipes.
                        </p>
                    </div>

                    <!-- Category Filter Tabs (Scrollable on mobile) -->
                    <div class="relative mb-8 sm:mb-10">
                        <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:justify-center sm:flex-wrap">
                            <button
                                @click="activeCategorySlug = 'all'"
                                class="shrink-0 whitespace-nowrap rounded-full px-5 py-2.5 text-xs sm:text-sm font-bold transition-all"
                                :class="activeCategorySlug === 'all'
                                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#EA580C] text-white shadow-md scale-105'
                                    : 'border border-border bg-card text-foreground hover:border-[#F59E0B]/50 hover:bg-muted'"
                            >
                                All Menu Dishes
                            </button>

                            <button
                                v-for="category in categories"
                                :key="category.slug"
                                @click="activeCategorySlug = category.slug"
                                class="shrink-0 whitespace-nowrap rounded-full px-4 sm:px-5 py-2.5 text-xs sm:text-sm font-bold transition-all"
                                :class="activeCategorySlug === category.slug
                                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#EA580C] text-white shadow-md scale-105'
                                    : 'border border-border bg-card text-foreground hover:border-[#F59E0B]/50 hover:bg-muted'"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Filtered Dishes Grid -->
                    <div v-if="filteredDishes.length" v-stagger="{ preset: 'fadeUp', stagger: 85, duration: 620 }" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                        <ProductCard
                            v-for="dish in filteredDishes"
                            :key="dish.slug"
                            :id="dish.id"
                            :slug="dish.slug"
                            :title="dish.title"
                            :price="dish.price"
                            :compare-price="dish.compare_price"
                            :image="dish.image"
                            :category="dish.category"
                            :rating="dish.rating"
                            :reviews-count="dish.reviews_count"
                            :in-stock="dish.in_stock"
                            :stock-quantity="dish.stock_quantity"
                        />
                    </div>

                    <div v-else class="rounded-2xl border border-border bg-card p-12 text-center text-muted-foreground shadow-sm">
                        No dishes found in this category. Explore our full catalog!
                    </div>

                    <!-- Bottom View All Button -->
                    <div class="mt-10 sm:mt-12 text-center">
                        <Link href="/shop" class="btn-premium group inline-flex items-center gap-2">
                            <span>View Full Restaurant Menu</span>
                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>
            </section>

            <!-- 4. WHY DINE WITH US / RESTAURANT VALUE PROPS -->
            <section class="py-12 sm:py-20 bg-background border-y border-border/60">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                        <div
                            v-for="item in valueProps"
                            :key="item.title"
                            class="rounded-3xl border border-border/80 bg-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-[#F59E0B]/50 hover:shadow-md text-center"
                        >
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#F59E0B]/20 to-[#EA580C]/10 text-[#F59E0B] shadow-inner">
                                <component :is="item.icon" class="h-8 w-8" />
                            </div>
                            <h3 class="font-display mb-2 text-lg font-bold text-foreground">
                                {{ item.title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. CHEF STORY & HERITAGE (FROM KITCHEN TO TABLE) -->
            <section :class="`py-12 sm:py-20 ${isDark ? 'bg-[#0B0F19]' : 'bg-slate-50'}`">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                        <!-- Left: Chef in Action Photo with Warm Badge -->
                        <div v-reveal="{ preset: 'zoom', duration: 720 }" class="relative">
                            <div class="relative overflow-hidden rounded-3xl border border-border shadow-2xl">
                                <img
                                    src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=1000&q=80"
                                    alt="Executive Chef Crafting Gourmet Dishes"
                                    class="h-[420px] sm:h-[480px] w-full object-cover transition-transform duration-700 hover:scale-105"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
                                <div class="absolute bottom-6 left-6 right-6 text-white">
                                    <p class="font-display text-xl font-bold">Chef Marco & The Culinary Crew</p>
                                    <p class="text-xs text-slate-300">Dedicated to crafting authentic recipes every single day</p>
                                </div>
                            </div>

                            <!-- Floating Experience Pill -->
                            <div
                                :class="[
                                    'absolute -top-4 -left-4 sm:top-6 sm:-left-6 rounded-2xl border p-4 shadow-xl backdrop-blur-md transition-all',
                                    isDark ? 'border-white/20 bg-black/90 text-white' : 'border-amber-200/80 bg-white/95 text-slate-900 shadow-xl'
                                ]"
                            >
                                <span class="text-2xl font-black text-[#F59E0B]">15+</span>
                                <p :class="['text-xs font-semibold', isDark ? 'text-slate-300' : 'text-slate-600']">Years of Culinary<br />Mastery</p>
                            </div>
                        </div>

                        <!-- Right: Story & Mission -->
                        <div v-stagger="{ preset: 'fadeUp', stagger: 110, duration: 680 }" class="space-y-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#F59E0B]/15 text-[#F59E0B] border border-[#F59E0B]/30">
                                <ChefHat class="h-3.5 w-3.5" />
                                Our Culinary Story
                            </span>

                            <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-foreground tracking-tight leading-tight">
                                Where Pure Passion Meets Artisan Gastronomy
                            </h2>

                            <p class="text-sm sm:text-base leading-relaxed text-muted-foreground">
                                Founded with a commitment to culinary excellence, RH Restro combines time-honored cooking methods with modern flavors. From our 48-hour fermented Neapolitan dough to our 28-day dry-aged Angus ribeye steaks, every dish is an homage to real taste.
                            </p>

                            <div class="grid grid-cols-2 gap-4 pt-2">
                                <div class="rounded-2xl border border-border bg-card p-4">
                                    <CheckCircle2 class="h-5 w-5 text-[#F59E0B] mb-2" />
                                    <p class="font-bold text-foreground text-sm">Wood-Fired Stone Oven</p>
                                    <p class="text-xs text-muted-foreground">900°F blistering heat locks in moisture and smoky aroma.</p>
                                </div>
                                <div class="rounded-2xl border border-border bg-card p-4">
                                    <CheckCircle2 class="h-5 w-5 text-[#F59E0B] mb-2" />
                                    <p class="font-bold text-foreground text-sm">Cast-Iron Smashed</p>
                                    <p class="text-xs text-muted-foreground">Crisp caramelized edges with juicy tender beef centers.</p>
                                </div>
                            </div>

                            <div class="pt-2 flex items-center gap-4">
                                <Link href="/about" class="btn-premium">
                                    <span>Read Our Full Story</span>
                                    <ArrowRight class="h-4 w-4" />
                                </Link>
                                <Link href="/custom-order" class="text-sm font-bold text-foreground hover:text-[#F59E0B] transition-colors">
                                    Reserve Table &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. GUEST REVIEWS / DINER TESTIMONIALS -->
            <section class="py-12 sm:py-20 bg-background">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#F59E0B]/15 text-[#F59E0B] border border-[#F59E0B]/30 mb-3">
                            <Star class="h-3.5 w-3.5" />
                            Guest Love
                        </span>
                        <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-foreground tracking-tight mb-4">
                            What Diners Say
                        </h2>
                        <p class="text-muted-foreground text-sm sm:text-base">
                            Unfiltered experiences and heartfelt reviews from food lovers who dined with us.
                        </p>
                    </div>

                    <div v-if="testimonials.length" v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="grid gap-6 md:grid-cols-3">
                        <div
                            v-for="testimonial in testimonials"
                            :key="testimonial.id"
                            class="rounded-3xl border border-border/80 bg-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-[#F59E0B]/40 hover:shadow-xl flex flex-col justify-between"
                        >
                            <div>
                                <div class="mb-4 flex items-center space-x-1">
                                    <Star v-for="i in (testimonial.rating || 5)" :key="i" class="h-4 w-4 fill-[#F59E0B] text-[#F59E0B]" />
                                </div>
                                <p class="text-sm italic text-muted-foreground leading-relaxed mb-6">
                                    "{{ testimonial.content }}"
                                </p>
                            </div>

                            <div class="flex items-center justify-between border-t border-border/60 pt-4">
                                <span class="font-display text-sm font-bold text-foreground">
                                    {{ testimonial.name }}
                                </span>
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    Verified Diner
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. CULINARY GALLERY STRIP (INSTAGRAM STYLE) -->
            <section class="py-10 bg-background border-t border-border/60">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-xl sm:text-2xl font-bold text-foreground">
                            Culinary Highlights
                        </h3>
                        <p class="text-xs sm:text-sm text-muted-foreground">Follow our daily gourmet creations @RHRestro</p>
                    </div>
                    <Link href="/shop" class="text-xs sm:text-sm font-bold text-[#F59E0B] hover:text-[#EA580C] transition-colors">
                        View All Menu &rarr;
                    </Link>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                        <div
                            v-for="item in galleryImages"
                            :key="item.title"
                            class="group relative aspect-square overflow-hidden rounded-2xl bg-muted"
                        >
                            <img
                                :src="item.image"
                                :alt="item.title"
                                class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end text-white">
                                <p class="text-xs font-bold line-clamp-1">{{ item.title }}</p>
                                <span class="text-[10px] text-[#FDE68A]">{{ item.category }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 8. VIP TABLE RESERVATIONS & EVENT CATERING CTA BANNER -->
            <section class="py-12 sm:py-20 bg-background">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div
                        v-reveal="{ preset: 'zoom', duration: 720 }"
                        :class="[
                            'relative overflow-hidden rounded-3xl border p-8 sm:p-14 text-center shadow-2xl transition-all duration-300',
                            isDark
                                ? 'border-[#F59E0B]/30 bg-gradient-to-br from-[#131B2E] via-[#0B0F19] to-black text-white'
                                : 'border-amber-500/30 bg-gradient-to-br from-amber-50 via-orange-50/50 to-amber-100/70 text-slate-900'
                        ]"
                    >
                        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-[#EA580C]/20 blur-3xl pointer-events-none" />
                        <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#F59E0B]/20 blur-3xl pointer-events-none" />

                        <span
                            :class="[
                                'relative z-10 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border transition-colors',
                                isDark
                                    ? 'bg-[#F59E0B]/20 text-[#FDE68A] border-[#F59E0B]/40'
                                    : 'bg-amber-500/15 text-amber-900 border-amber-500/30'
                            ]"
                        >
                            <Sparkles class="h-3.5 w-3.5 text-[#F59E0B]" />
                            Bookings & Private Dining
                        </span>

                        <h2
                            :class="[
                                'relative z-10 font-display text-3xl sm:text-5xl font-black tracking-tight mb-4 transition-colors',
                                isDark ? 'text-white' : 'text-slate-900'
                            ]"
                        >
                            Planning a Birthday Party, Corporate Lunch, or Private Dinner?
                        </h2>

                        <p
                            :class="[
                                'relative z-10 mx-auto max-w-2xl text-sm sm:text-lg mb-8 leading-relaxed transition-colors',
                                isDark ? 'text-slate-300' : 'text-slate-700'
                            ]"
                        >
                            Let RH Restro create an unforgettable culinary evening for your guests with custom multi-course chef menus, prime candlelight table setups, and dedicated hospitality.
                        </p>

                        <div class="relative z-10 flex flex-col sm:flex-row justify-center gap-4">
                            <Link
                                href="/custom-order"
                                class="btn-premium group inline-flex items-center justify-center gap-2"
                            >
                                <Calendar class="h-5 w-5" />
                                <span>Reserve Table / Book Event</span>
                                <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </Link>

                            <Link
                                href="/contact"
                                :class="[
                                    'btn-glass group inline-flex items-center justify-center gap-2 transition-colors',
                                    isDark
                                        ? 'text-white border-white/20 bg-white/10 hover:bg-white/20'
                                        : 'text-slate-900 border-slate-300 bg-white/80 hover:bg-white shadow-sm'
                                ]"
                            >
                                <PhoneCall class="h-5 w-5 text-[#F59E0B]" />
                                <span>Contact Dining Desk</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
