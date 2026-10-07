<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ShoppingCart, Menu, X, Sun, Moon, Monitor, User, ArrowRight, CheckCircle2 } from "lucide-vue-next";
import { inject } from "vue";
import CustomerAuthModal from "@/Components/CustomerAuthModal.vue";
import Footer from "@/public/components/Footer.vue";
import { useCustomer } from "@/composables/useCustomer";
import { Toaster } from "vue-sonner";
import ChatWidget from "@/Components/ChatWidget.vue";

const site = computed(() => usePage().props.site);
const theme = inject("theme");
const setTheme = inject("setTheme");

const { customer, cartCount, openAuthModal, logout, fetchWishlist, fetchCart, lastAddedCartItem, showFloatingCart, closeFloatingCart } = useCustomer();

onMounted(() => {
    fetchWishlist();
    fetchCart();
});

const mobileMenuOpen = ref(false);
const themeMenuOpen = ref(false);
const themeMenuRef = ref(null);


const navLinks = [
    { path: "/", label: "Home" },
    { path: "/shop", label: "Menu" },
    { path: "/custom-order", label: "Table & Catering" },
    { path: "/order-tracking", label: "Track Order" },
    { path: "/about", label: "Our Story" },
    { path: "/contact", label: "Contact" },
];

const currentPath = computed(() => usePage().url.split("?")[0]);

const isActive = (path) => {
    if (path === "/") {
        return currentPath.value === "/";
    }

    return (
        currentPath.value === path || currentPath.value.startsWith(`${path}/`)
    );
};

const handleClickOutside = (event) => {
    if (themeMenuRef.value && !themeMenuRef.value.contains(event.target)) {
        themeMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
});

watch(currentPath, () => {
    mobileMenuOpen.value = false;
    themeMenuOpen.value = false;
});

const customerInitials = computed(() =>
    (customer.value?.name || "CU")
        .split(" ")
        .map((part) => part[0])
        .join("")
        .slice(0, 2)
        .toUpperCase(),
);

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
    <div class="flex flex-col min-h-screen">
        <nav class="sticky top-0 z-50 border-b border-border bg-card/95 shadow-sm backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex min-h-14 items-center justify-between gap-3 py-1.5 sm:h-20 sm:min-h-0 sm:py-0">
                    <Link href="/" class="flex min-w-0 flex-1 items-center space-x-2.5 sm:space-x-3 group">
                        <img :src="site?.logo_url || '/rh-restro-logo.png?v=20261007_v1'" :alt="site?.name || 'RH Restro'"
                            class="h-9 sm:h-11 w-auto max-w-[120px] shrink-0 object-contain transition-luxury group-hover:scale-105 drop-shadow-sm" />
                        <div class="min-w-0">
                            <span
                                class="block truncate text-base sm:text-xl font-extrabold tracking-tight font-heading group-hover:opacity-90 transition-opacity">
                                <span class="text-foreground dark:text-white font-extrabold">{{ brandParts.prefix }}</span>
                                <span class="ml-1.5 bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 bg-clip-text text-transparent font-extrabold drop-shadow-xs">{{ brandParts.suffix }}</span>
                            </span>
                            <p class="hidden truncate text-[11px] leading-tight font-medium tracking-normal text-muted-foreground/80 dark:text-slate-400 sm:block sm:text-xs">
                                {{ site?.tagline || 'Fine Dining & Gourmet Flavors' }}
                            </p>
                        </div>
                    </Link>

                    <div class="hidden md:flex items-center space-x-1">
                        <Link v-for="link in navLinks" :key="link.path" :href="link.path"
                            class="public-interactive rounded-full px-4 py-2 text-sm transition-all" :class="isActive(link.path)
                                    ? 'text-[#F59E0B] bg-[#F59E0B]/10 font-bold'
                                    : 'text-foreground hover:text-[#F59E0B] hover:bg-muted font-medium'
                                ">
                            {{ link.label }}
                        </Link>
                    </div>

                    <div class="flex shrink-0 items-center space-x-1.5 sm:space-x-3">
                        <Link href="/custom-order" class="hidden xl:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-[#F59E0B]/40 bg-[#F59E0B]/10 text-xs font-bold text-[#F59E0B] hover:bg-[#F59E0B] hover:text-white transition-all">
                            <span>Book Table</span>
                        </Link>

                        <div class="relative" ref="themeMenuRef">
                            <button @click="themeMenuOpen = !themeMenuOpen"
                                class="public-interactive rounded-full p-2 text-foreground transition-colors hover:bg-muted"
                                aria-label="Toggle theme">
                                <Sun v-if="theme === 'light'" class="w-5 h-5" />
                                <Moon v-else-if="theme === 'dark'" class="w-5 h-5" />
                                <Monitor v-else class="w-5 h-5" />
                            </button>

                            <Transition enter-active-class="custom-fade-in-down motion-dropdown-enter"
                                leave-active-class="custom-fade-out-up motion-dropdown-leave">
                                <div v-if="themeMenuOpen"
                                    class="absolute right-0 top-full z-[70] mt-2 w-48 overflow-hidden rounded-2xl border border-border bg-card p-2 shadow-luxury sm:bg-card/95 sm:backdrop-blur-md">
                                    <button v-for="t in ['light', 'dark', 'system']" :key="t" @click="
                                        setTheme(t);
                                    themeMenuOpen = false;
                                    "
                                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium transition-colors"
                                        :class="theme === t
                                                ? 'bg-[#F59E0B] text-black font-bold'
                                                : 'text-foreground hover:bg-muted'
                                            ">
                                        <Sun v-if="t === 'light'" class="h-4 w-4 shrink-0" />
                                        <Moon v-else-if="t === 'dark'" class="h-4 w-4 shrink-0" />
                                        <Monitor v-else class="h-4 w-4 shrink-0" />
                                        <span class="capitalize whitespace-nowrap">{{ t }}</span>
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <Link href="/cart"
                            class="public-interactive relative rounded-full p-2 text-foreground transition-colors hover:bg-muted"
                            aria-label="Shopping cart">
                            <ShoppingCart class="w-5 h-5" />
                            <span
                                class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-[#F59E0B] to-[#EA580C] text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">
                                {{ cartCount }}
                            </span>
                        </Link>

                        <Link v-if="customer" href="/my-account"
                            class="public-interactive inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full border border-[#F59E0B]/30 bg-[#F59E0B]/12 text-[#F59E0B] transition-colors hover:border-[#F59E0B]/60"
                            aria-label="My Account">
                            <img v-if="customer.profile_photo_url" :src="customer.profile_photo_url"
                                :alt="customer.name || 'My Account'" class="h-full w-full object-cover" />
                            <span v-else class="text-sm font-bold tracking-wide">
                                {{ customerInitials }}
                            </span>
                        </Link>
                        <button v-else @click="openAuthModal"
                            class="public-interactive hidden rounded-full bg-gradient-to-r from-[#F59E0B] to-[#EA580C] px-5 py-2 text-sm font-bold text-white shadow-sm transition-all hover:shadow-md hover:from-[#EA580C] hover:to-[#D97706] sm:block">
                            Sign In
                        </button>

                        <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="public-interactive rounded-lg p-2 text-foreground transition-colors hover:bg-muted md:hidden"
                            aria-label="Toggle menu">
                            <X v-if="mobileMenuOpen" class="w-6 h-6" />
                            <Menu v-else class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>

            <Transition enter-active-class="custom-fade-in-down motion-dropdown-enter"
                leave-active-class="custom-fade-out-up motion-dropdown-leave">
                <div v-if="mobileMenuOpen" class="border-t border-border bg-card/98 backdrop-blur-md md:hidden">
                    <div class="space-y-2 px-4 py-4">
                        <Link v-for="link in navLinks" :key="link.path" :href="link.path"
                            @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm transition-colors" :class="isActive(link.path)
                                    ? 'text-[#F59E0B] bg-[#F59E0B]/10 font-bold'
                                    : 'text-foreground hover:bg-muted font-medium'
                                ">
                            {{ link.label }}
                        </Link>
                        <Link href="/custom-order" @click="mobileMenuOpen = false"
                            class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#EA580C] px-4 py-3 font-bold text-white shadow-md">
                            <span>Book a Table / Catering</span>
                        </Link>
                        <Link v-if="customer" href="/my-account" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-xl bg-[#F59E0B]/10 px-4 py-3 font-semibold text-[#F59E0B]">
                            <User class="h-4 w-4 shrink-0" />
                            My Account
                        </Link>
                        <button v-if="customer" @click="logout(); mobileMenuOpen = false;"
                            class="block w-full text-left rounded-xl px-4 py-3 font-medium text-red-500 hover:bg-red-500/10">
                            Sign Out ({{ customer.name.split(' ')[0] }})
                        </button>
                        <button v-else @click="openAuthModal(); mobileMenuOpen = false;"
                            class="block w-full rounded-xl border border-[#F59E0B] px-4 py-3 text-center font-bold text-[#F59E0B] hover:bg-[#F59E0B]/10">
                            Sign In / Register
                        </button>
                    </div>
                </div>
            </Transition>
        </nav>

        <main class="flex-1 overflow-x-hidden">
            <Transition mode="out-in" enter-active-class="custom-fade-in-up motion-page-enter"
                leave-active-class="custom-fade-out motion-page-leave">
                <div :key="currentPath" class="min-h-full">
                    <slot />
                </div>
            </Transition>
        </main>

        <Footer />

        <!-- Floating Add-to-Cart Checkout Action Bar -->
        <Transition
            enter-active-class="transition duration-500 ease-out transform"
            enter-from-class="translate-y-12 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-300 ease-in transform"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="translate-y-12 opacity-0 scale-95"
        >
            <div
                v-if="showFloatingCart && lastAddedCartItem"
                class="fixed bottom-5 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md z-50 rounded-2xl border border-amber-500/40 bg-slate-950/95 text-white p-3.5 shadow-2xl backdrop-blur-md flex items-center justify-between gap-3"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative shrink-0 w-12 h-12 rounded-xl overflow-hidden border border-amber-500/40 shadow-sm">
                        <img :src="lastAddedCartItem.image" :alt="lastAddedCartItem.title" class="w-full h-full object-cover" />
                        <span class="absolute top-0 right-0 bg-amber-500 text-slate-950 w-4 h-4 rounded-bl-md flex items-center justify-center text-[10px] font-extrabold">
                            ✓
                        </span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1 text-[11px] font-extrabold text-amber-500 uppercase tracking-wider">
                            <CheckCircle2 class="w-3.5 h-3.5 text-amber-500" /> Added to Cart!
                        </div>
                        <div class="text-xs font-bold text-white truncate max-w-[150px] sm:max-w-[190px]">
                            {{ lastAddedCartItem.title }}
                        </div>
                        <div class="text-[11px] text-slate-300 truncate mt-0.5">
                            <span v-if="lastAddedCartItem.variant_name" class="text-amber-500 font-semibold mr-1">
                                {{ lastAddedCartItem.variant_name }} •
                            </span>
                            <span>{{ formatPrice(lastAddedCartItem.price) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <Link
                        href="/cart"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-md transition-all active:scale-95 whitespace-nowrap"
                    >
                        Checkout <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                    <button
                        @click="closeFloatingCart"
                        class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
                        aria-label="Dismiss notification"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </Transition>

        <a v-if="site?.whatsapp_url" :href="site.whatsapp_url" target="_blank" rel="noopener noreferrer"
            aria-label="Chat on WhatsApp"
            class="whatsapp-fab fixed right-5 bottom-24 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366] hover:bg-[#20bd5a] text-white shadow-xl transition-all duration-300 hover:scale-110 active:scale-95 md:right-6 md:bottom-24">
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6" aria-hidden="true">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z" />
            </svg>
        </a>

        <CustomerAuthModal />
        <Toaster position="bottom-right" richColors />
        <ChatWidget />
    </div>
</template>
