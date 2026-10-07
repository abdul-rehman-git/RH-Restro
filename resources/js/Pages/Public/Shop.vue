<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { ArrowDownWideNarrow, Check, ChevronDown, SlidersHorizontal, X } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PaginationNav from '@/Components/PaginationNav.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ProductCard from '@/public/components/ProductCard.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';
import { formatPrice } from '@/public/utils/formatPrice';

const page = usePage();
const products = computed(() => page.props.products || { data: [], total: 0, last_page: 1, current_page: 1 });
const categories = computed(() => page.props.categories || []);
const filters = computed(() => page.props.filters || {});
const sidebarOpen = ref(false);
const minSelectablePrice = 100;
const maxSelectablePrice = 5000;

const selectedCategory = computed(() => filters.value.category || 'all');
const maxPrice = computed(() => Number(filters.value.max_price || 1000));
const sortBy = computed(() => filters.value.sort || 'featured');
const currentPage = computed(() => products.value.current_page || 1);
const localMaxPrice = ref(maxPrice.value);

watch(maxPrice, (value) => {
    localMaxPrice.value = value;
}, { immediate: true });

const categoryOptions = computed(() => [
    { value: 'all', label: 'All Categories' },
    ...categories.value.map((category) => ({
        value: category.slug,
        label: category.label || category.name,
    })),
]);

const categoryChips = computed(() => [
    { value: 'all', label: 'All' },
    ...categories.value.map((category) => ({
        value: category.slug,
        label: category.label || category.name,
    })),
]);

const sortOptions = [
    { value: 'featured', label: 'Featured' },
    { value: 'price-low', label: 'Price: Low to High' },
    { value: 'price-high', label: 'Price: High to Low' },
    { value: 'newest', label: 'Newest' },
];

const activeSheet = ref(null);

const openSheet = (sheet) => {
    activeSheet.value = sheet;
};

const closeSheet = () => {
    activeSheet.value = null;
};

watch(activeSheet, (value) => {
    document.body.style.overflow = value ? 'hidden' : '';
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

const activeFilterCount = computed(() => {
    let count = 0;
    if (selectedCategory.value !== 'all') count += 1;
    if (filters.value.max_price) count += 1;
    return count;
});

const currentSortLabel = computed(
    () => sortOptions.find((option) => option.value === sortBy.value)?.label || 'Featured',
);

const updateFilter = (key, value) => {
    const params = new URLSearchParams(window.location.search);

    if (!value || value === 'all') {
        params.delete(key);
    } else {
        params.set(key, value);
    }

    params.delete('page');
    router.get(`/shop?${params.toString()}`, {}, { preserveScroll: true });
};

const selectSort = (value) => {
    closeSheet();
    updateFilter('sort', value);
};

const handlePageChange = (nextPage) => {
    if (nextPage < 1 || nextPage > (products.value.last_page || 1)) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    params.set('page', String(nextPage));
    router.get(`/shop?${params.toString()}`);
};

const clearFilters = () => {
    closeSheet();
    router.get('/shop', {}, { preserveScroll: true });
};

const rangeProgress = computed(() => {
    const boundedValue = Math.min(Math.max(localMaxPrice.value, minSelectablePrice), maxSelectablePrice);
    return ((boundedValue - minSelectablePrice) / (maxSelectablePrice - minSelectablePrice)) * 100;
});

const updateMaxPrice = (value) => {
    localMaxPrice.value = Number(value);
};

const commitMaxPrice = () => {
    updateFilter('max_price', String(localMaxPrice.value));
};

const applyMobilePrice = () => {
    closeSheet();
    updateFilter('max_price', String(localMaxPrice.value));
};
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background py-6 pb-24 sm:py-12 md:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6 sm:mb-8">
                    <SectionHeader tag="h1" title="Our Gourmet Menu"
                        subtitle="Explore our handcrafted culinary delights — filter by category, price, and chef specialties." />
                </div>

                <!-- Mobile: horizontal scrollable category chips with edge fades -->
                <div class="relative mb-5 md:hidden">
                    <div class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-r from-background to-transparent" />
                    <div class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-l from-background to-transparent" />
                    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-6 pb-1">
                        <button v-for="chip in categoryChips" :key="chip.value"
                            @click="updateFilter('category', chip.value)"
                            class="shrink-0 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-[13px] font-medium transition-colors"
                            :class="selectedCategory === chip.value
                                ? 'border-amber-500 bg-amber-500 text-slate-950 font-bold shadow-sm'
                                : 'shop-chip border-border'">
                            {{ chip.label }}
                        </button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <aside v-reveal="{ preset: 'fadeRight', duration: 680 }" class="hidden md:block lg:w-64 flex-shrink-0">
                        <div class="sticky top-24">
                            <button @click="sidebarOpen = !sidebarOpen"
                                class="public-interactive mb-4 flex w-full items-center justify-between rounded-lg border border-border bg-card px-4 py-3 lg:hidden">
                                <span class="font-medium text-foreground">Filters</span>
                                <SlidersHorizontal class="w-5 h-5 text-muted-foreground" />
                            </button>

                            <div
                                :class="`${sidebarOpen ? 'block' : 'hidden'} lg:block bg-card rounded-xl p-6 border border-border shadow-luxury space-y-6`">
                                <div>
                                    <h3 class="font-semibold text-foreground mb-4">Category</h3>
                                    <div class="space-y-2">
                                        <label v-for="category in categoryOptions" :key="category.value"
                                            class="flex items-center space-x-3 cursor-pointer group">
                                            <input type="radio" name="category" :value="category.value"
                                                :checked="selectedCategory === category.value"
                                                @change="updateFilter('category', category.value)"
                                                class="h-4 w-4 border-border text-amber-500 focus:ring-amber-500" />
                                            <span
                                                class="text-sm text-muted-foreground group-hover:text-amber-500 transition-colors">
                                                {{ category.label }}
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-semibold text-foreground mb-4">Max Price</h3>
                                    <div class="space-y-4">
                                        <input
                                            type="range"
                                            :min="minSelectablePrice"
                                            :max="maxSelectablePrice"
                                            step="50"
                                            :value="localMaxPrice"
                                            @input="updateMaxPrice($event.target.value)"
                                            @change="commitMaxPrice"
                                            :style="{ '--range-progress': `${rangeProgress}%` }"
                                            class="public-range-slider h-2 w-full cursor-pointer appearance-none"
                                        />
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-muted-foreground">{{ formatPrice(minSelectablePrice) }}</span>
                                            <span class="font-medium text-amber-500">{{ formatPrice(localMaxPrice) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-border">
                                    <button @click="clearFilters"
                                        class="public-interactive w-full px-4 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground">
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div class="flex-1">
                        <div v-reveal="{ preset: 'fadeUp', duration: 620 }"
                            class="mb-4 flex items-center justify-between gap-4 md:mb-6 md:flex-row md:items-center">
                            <p class="text-sm text-muted-foreground md:text-base">
                                Showing <span class="font-medium text-foreground">{{ products.total || 0 }}</span>
                                results
                            </p>

                            <div class="relative hidden md:block">
                                <select :value="sortBy" @change="updateFilter('sort', $event.target.value)"
                                    class="public-field cursor-pointer appearance-none rounded-lg border border-border bg-white px-4 py-2 pr-10 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:[color-scheme:dark]">
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <ChevronDown
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                            </div>
                        </div>

                        <div v-stagger="{ preset: 'fadeUp', stagger: 85, duration: 620 }"
                            class="grid grid-cols-2 gap-3 md:gap-6 lg:grid-cols-3">
                            <ProductCard v-for="product in products.data || []" :key="product.slug" :id="product.id"
                                :slug="product.slug" :title="product.title" :price="product.price"
                                :compare-price="product.compare_price" :image="product.image"
                                :category="product.category" :rating="product.rating"
                                :reviews-count="product.reviews_count"
                                :in-stock="product.in_stock" :stock-quantity="product.stock_quantity" />
                        </div>

                        <div v-if="(products.last_page || 1) > 1" class="mt-10 md:mt-12">
                            <PaginationNav :current-page="currentPage" :last-page="products.last_page || 1"
                                :on-page-change="handlePageChange" variant="public" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile: sticky bottom Filter / Sort toolbar -->
        <div class="mobile-shop-toolbar fixed inset-x-0 bottom-0 z-40 border-t border-border md:hidden">
            <div class="grid grid-cols-2 divide-x divide-border">
                <button @click="openSheet('filter')"
                    class="flex min-h-[52px] items-center justify-center gap-2 text-sm font-semibold text-foreground active:bg-muted">
                    <SlidersHorizontal class="h-4 w-4" />
                    Filter
                    <span v-if="activeFilterCount"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[11px] font-bold text-slate-950">
                        {{ activeFilterCount }}
                    </span>
                </button>
                <button @click="openSheet('sort')"
                    class="flex min-h-[52px] items-center justify-center gap-2 text-sm font-semibold text-foreground active:bg-muted">
                    <ArrowDownWideNarrow class="h-4 w-4" />
                    Sort
                </button>
            </div>
        </div>

        <!-- Mobile bottom sheets -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="activeSheet" class="mobile-sheet-overlay fixed inset-0 z-50 md:hidden" @click="closeSheet" />
            </Transition>

            <Transition enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full" enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-y-0"
                leave-to-class="translate-y-full">
                <div v-if="activeSheet"
                    class="mobile-sheet fixed inset-x-0 bottom-0 z-50 rounded-t-3xl border-t border-border md:hidden">
                    <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-muted-foreground/30" />

                    <div class="flex items-center justify-between px-5 pt-4 pb-2">
                        <h3 class="text-base font-semibold text-foreground">
                            {{ activeSheet === 'filter' ? 'Filters' : 'Sort by' }}
                        </h3>
                        <button @click="closeSheet"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-foreground active:scale-95"
                            aria-label="Close">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Sort sheet -->
                    <div v-if="activeSheet === 'sort'" class="px-3 pb-6">
                        <button v-for="option in sortOptions" :key="option.value" @click="selectSort(option.value)"
                            class="flex min-h-[48px] w-full items-center justify-between rounded-xl px-4 text-left text-sm transition-colors"
                            :class="sortBy === option.value
                                ? 'bg-amber-500/10 font-semibold text-amber-500'
                                : 'text-foreground active:bg-muted'">
                            {{ option.label }}
                            <Check v-if="sortBy === option.value" class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Filter sheet -->
                    <div v-else class="max-h-[65vh] overflow-y-auto px-5 pb-6">
                        <div class="mb-6">
                            <h4 class="mb-3 text-sm font-semibold text-foreground">Category</h4>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="category in categoryOptions" :key="category.value"
                                    @click="closeSheet(); updateFilter('category', category.value);"
                                    class="rounded-full border px-4 py-2 text-[13px] font-medium transition-colors"
                                    :class="selectedCategory === category.value
                                        ? 'border-amber-500 bg-amber-500 text-slate-950 font-bold'
                                        : 'border-border bg-background text-muted-foreground'">
                                    {{ category.label }}
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="mb-3 text-sm font-semibold text-foreground">Max Price</h4>
                            <input
                                type="range"
                                :min="minSelectablePrice"
                                :max="maxSelectablePrice"
                                step="50"
                                :value="localMaxPrice"
                                @input="updateMaxPrice($event.target.value)"
                                :style="{ '--range-progress': `${rangeProgress}%` }"
                                class="public-range-slider h-2 w-full cursor-pointer appearance-none"
                            />
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">{{ formatPrice(minSelectablePrice) }}</span>
                                <span class="font-semibold text-amber-500">{{ formatPrice(localMaxPrice) }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="clearFilters"
                                class="flex h-11 flex-1 items-center justify-center rounded-full border border-border text-sm font-medium text-muted-foreground active:bg-muted">
                                Clear All
                            </button>
                            <button @click="applyMobilePrice"
                                class="flex h-11 flex-1 items-center justify-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-sm font-bold text-white shadow-sm active:scale-[0.98]">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PublicLayout>
</template>
