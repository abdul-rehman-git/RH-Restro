<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ShoppingBag, Heart, Star, Sparkles } from 'lucide-vue-next';
import { formatPrice } from '@/public/utils/formatPrice';
import { useCustomer } from '@/composables/useCustomer';
import { toast } from 'vue-sonner';

const { wishlistIds, toggleWishlist, addToCart } = useCustomer();

const props = defineProps({
    id: { type: [Number, String], required: true },
    slug: { type: String, required: true },
    title: { type: String, required: true },
    price: { type: [String, Number], required: true },
    comparePrice: { type: [String, Number], default: null },
    image: { type: String, default: '' },
    category: { type: String, default: '' },
    rating: { type: Number, default: 0 },
    reviewsCount: { type: Number, default: 0 },
    inStock: { type: Boolean, default: true },
    stockQuantity: { type: Number, default: null },
});

const isLiked = computed(() => wishlistIds.value.has(props.id));
const imageLoaded = ref(false);

const productPath = `/product/${props.slug}`;

const isOutOfStock = computed(() => {
    return !props.inStock || (props.stockQuantity !== null && props.stockQuantity <= 0);
});

const discountPercent = computed(() => {
    const current = Number(props.price);
    const compare = Number(props.comparePrice);

    if (!compare || !Number.isFinite(compare) || compare <= current) {
        return null;
    }

    return Math.round(((compare - current) / compare) * 100);
});

const toggleSavedState = () => {
    toggleWishlist(props.id);
};

const handleCartAction = () => {
    if (isOutOfStock.value) {
        toast.error('This dish is currently sold out.');
        return;
    }
    addToCart(props.id);
};
</script>

<template>
    <div
        class="public-card group relative flex h-full flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-md transition-all duration-300 hover:-translate-y-1 hover:border-[#F59E0B]/50 hover:shadow-xl">
        <!-- Dish Thumbnail Container -->
        <Link :href="productPath" class="block relative aspect-[4/3] overflow-hidden bg-muted">
            <template v-if="image">
                <div v-if="!imageLoaded" class="pc-skeleton absolute inset-0" aria-hidden="true" />
                <img :src="image" :alt="title" loading="lazy" @load="imageLoaded = true"
                    :class="[
                        'h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-108',
                        imageLoaded ? 'opacity-100' : 'opacity-0',
                        isOutOfStock ? 'grayscale-[50%] opacity-80' : '',
                    ]" />
            </template>
            <div v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#F59E0B]/15 to-transparent px-6 text-center text-sm font-semibold text-foreground">
                {{ title }}
            </div>

            <!-- Soft bottom image vignette -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition-opacity" />

            <!-- Category Pill -->
            <span v-if="category"
                class="absolute left-3 top-3 z-10 inline-flex items-center gap-1 rounded-full border border-white/20 bg-black/70 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-[#FDE68A] backdrop-blur-md shadow-sm">
                {{ category }}
            </span>

            <!-- Out of Stock / Discount Badge -->
            <span v-if="isOutOfStock"
                class="absolute right-3 bottom-3 z-10 inline-flex items-center rounded-full bg-red-600 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-md">
                Sold Out
            </span>
            <span v-else-if="discountPercent"
                class="absolute left-3 bottom-3 z-10 inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-0.5 text-[11px] font-bold text-white shadow-md">
                -{{ discountPercent }}% OFF
            </span>

            <!-- Floating Wishlist Button -->
            <div
                class="absolute top-3 right-3 z-10 transition-transform duration-300">
                <button @click.prevent="toggleSavedState"
                    class="pc-wishlist-btn public-interactive flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/60 backdrop-blur-md text-white shadow-md transition-all hover:bg-[#F59E0B] hover:text-black hover:border-transparent active:scale-95"
                    aria-label="Save dish">
                    <Heart
                        :class="`h-4 w-4 ${isLiked ? 'fill-red-500 text-red-500' : 'text-white'}`" />
                </button>
            </div>
        </Link>

        <!-- Dish Details Body -->
        <div class="flex flex-1 flex-col p-4 sm:p-5">
            <!-- Title -->
            <Link :href="productPath" class="block mb-1.5">
                <h3
                    class="font-display line-clamp-1 text-base sm:text-lg font-bold text-foreground transition-colors group-hover:text-[#F59E0B]">
                    {{ title }}
                </h3>
            </Link>

            <!-- Star Rating & Review Count -->
            <div class="mb-3 flex items-center gap-1.5">
                <div class="flex items-center space-x-0.5">
                    <Star v-for="i in 5" :key="i"
                        :class="`h-3.5 w-3.5 ${i <= Math.round(rating || 5) ? 'fill-[#F59E0B] text-[#F59E0B]' : 'fill-slate-300 text-slate-300 dark:fill-slate-700 dark:text-slate-700'}`" />
                </div>
                <span class="text-xs font-semibold text-foreground">{{ (rating || 5).toFixed(1) }}</span>
                <span v-if="reviewsCount > 0" class="text-xs text-muted-foreground">({{ reviewsCount }})</span>
            </div>

            <!-- Price & Action Footer -->
            <div class="mt-auto pt-2 flex items-center justify-between gap-2 border-t border-border/50">
                <!-- Price tag -->
                <div class="flex flex-col">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight text-[#F59E0B]">
                            {{ formatPrice(price) }}
                        </span>
                        <span v-if="comparePrice" class="text-xs text-muted-foreground line-through font-medium">
                            {{ formatPrice(comparePrice) }}
                        </span>
                    </div>
                </div>

                <!-- Add to Order Button -->
                <button @click="handleCartAction"
                    :disabled="isOutOfStock"
                    :class="[
                        'public-interactive group/btn inline-flex items-center justify-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-bold transition-all shadow-sm active:scale-95',
                        isOutOfStock
                            ? 'bg-muted text-muted-foreground cursor-not-allowed opacity-60'
                            : 'bg-gradient-to-r from-[#F59E0B] to-[#EA580C] text-white hover:from-[#EA580C] hover:to-[#D97706] hover:shadow-md'
                    ]"
                    :title="isOutOfStock ? 'Sold Out' : 'Add to Order'"
                    aria-label="Add to order">
                    <ShoppingBag class="h-3.5 w-3.5 transition-transform group-hover/btn:scale-110" />
                    <span>{{ isOutOfStock ? 'Sold Out' : 'Order' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
