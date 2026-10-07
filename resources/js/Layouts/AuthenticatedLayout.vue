<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Globe2,
    LayoutDashboard,
    MessageSquareQuote,
    Package,
    Settings2,
    ShoppingBag,
    Star,
    Tags,
    Users,
    Wallet,
} from 'lucide-vue-next';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AppToaster from '@/Components/AppToaster.vue';
import Dropdown from '@/Components/Dropdown.vue';
import ChatWidget from '@/Components/ChatWidget.vue';
import { inject } from 'vue';

defineProps({
    header: { type: String, default: 'Dashboard' },
});

const user = computed(() => usePage().props.auth.user);
const businessSettings = computed(() => usePage().props.businessSettings);
const theme = inject('theme');
const setTheme = inject('setTheme');

const sidebarOpen = ref(false);

const navigation = [
    { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard'), icon: LayoutDashboard },
    { label: 'Categories', href: route('categories.index'), active: route().current('categories.*'), icon: Tags },
    { label: 'Products', href: route('products.index'), active: route().current('products.*'), icon: Package },
    { label: 'Public Content', href: route('public-content.edit'), active: route().current('public-content.*'), icon: Globe2 },
    { label: 'Customers', href: route('customers.index'), active: route().current('customers.*'), icon: Users },
    { label: 'Orders', href: route('orders.index'), active: route().current('orders.*'), icon: ShoppingBag },
    { label: 'Payments', href: route('payments.index'), active: route().current('payments.*'), icon: Wallet },
    { label: 'Reviews', href: route('admin-reviews.index'), active: route().current('admin-reviews.*'), icon: Star },
    { label: 'Inquiries', href: route('contact-inquiries.index'), active: route().current('contact-inquiries.*'), icon: MessageSquareQuote },
    { label: 'Settings', href: route('settings.edit'), active: route().current('settings.*'), icon: Settings2 },
];
</script>

<template>
    <Head>
        <meta name="robots" content="noindex, nofollow" />
    </Head>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-950">
        <AppToaster />

        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-slate-950/30 dark:bg-slate-950/60 lg:hidden"
            @click="sidebarOpen = false" />

        <div class="flex min-h-screen">
            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white transition-transform dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                <div class="border-b border-slate-200 px-6 py-6 dark:border-slate-800">
                    <Link href="/" class="flex items-center gap-3">
                        <template v-if="businessSettings?.logoUrl">
                            <img :src="businessSettings.logoUrl" :alt="businessSettings.name"
                                class="h-11 w-11 rounded-xl object-cover ring-1 ring-black/5" />
                        </template>
                        <div v-else
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                            <ApplicationLogo class="h-6 w-6 fill-current" />
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                Admin
                            </p>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ businessSettings?.name }}
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="px-4 py-6">
                    <nav class="space-y-1.5">
                        <Link v-for="item in navigation" :key="item.label" :href="item.href"
                            class="relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all"
                            :class="item.active
                                ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white dark:ring-1 dark:ring-slate-700/80 shadow-xs font-semibold'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-slate-100'">
                            <component :is="item.icon" class="h-5 w-5 transition-colors" :class="item.active ? 'text-amber-500' : ''" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="sidebarOpen = true"
                                class="rounded-xl border border-slate-200 p-2 text-slate-600 dark:border-slate-700 dark:text-slate-300 lg:hidden">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                                    Admin Panel
                                </p>
                                <template v-if="$slots.header">
                                    <slot name="header" />
                                </template>
                                <p v-else class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                    {{ header }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Dropdown>
                                <template #trigger>
                                    <button type="button"
                                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:text-slate-100">
                                        <svg v-if="theme === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <svg v-else-if="theme === 'dark'" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="hidden capitalize sm:inline">{{ theme }}</span>
                                    </button>
                                </template>

                                <template #content>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white py-2 dark:border-slate-700 dark:bg-slate-900">
                                        <button v-for="t in ['light', 'dark', 'system']" :key="t" type="button"
                                            class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm transition"
                                            :class="theme === t
                                                ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                                            @click="setTheme(t)">
                                            <svg v-if="t === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <svg v-else-if="t === 'dark'" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                            </svg>
                                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="capitalize">{{ t }}</span>
                                        </button>
                                    </div>
                                </template>
                            </Dropdown>

                            <Dropdown>
                                <template #trigger>
                                    <button type="button"
                                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left dark:border-slate-700 dark:bg-slate-950">
                                        <template v-if="businessSettings?.logoUrl">
                                            <img :src="businessSettings.logoUrl" :alt="businessSettings.name"
                                                class="h-10 w-10 rounded-full object-cover ring-1 ring-black/5" />
                                        </template>
                                        <div v-else
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
                                            {{user?.name?.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase()
                                            }}
                                        </div>
                                        <div class="hidden sm:block">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                {{ user?.name }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ user?.email }}
                                            </p>
                                        </div>
                                    </button>
                                </template>

                                <template #content>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white py-2 dark:border-slate-700 dark:bg-slate-900">
                                        <Link href="/profile"
                                            class="block w-full px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                                            Profile
                                        </Link>
                                        <Link href="/settings"
                                            class="block w-full px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                                            Settings
                                        </Link>
                                        <Link href="/logout" method="post" as="button"
                                            class="block w-full px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                                            Log Out
                                        </Link>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>
        <ChatWidget />
    </div>
</template>
