<script setup>
import { computed, onBeforeUnmount, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, ChevronLeft, ChevronRight, Heart, ShoppingCart, Shield, Star, Truck, Upload, X } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from '@/public/components/Button.vue';
import ProductCard from '@/public/components/ProductCard.vue';
import ReviewImageLightbox from '@/public/components/ReviewImageLightbox.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';
import { formatPrice } from '@/public/utils/formatPrice';
import { useCustomer } from '@/composables/useCustomer';
import { toast } from 'vue-sonner';

const { customer, wishlistIds, toggleWishlist, addToCart, openAuthModal } = useCustomer();

const page = usePage();
const product = computed(() => page.props.product || null);
const reviews = computed(() => page.props.reviews || []);
const relatedProducts = computed(() => page.props.relatedProducts || []);
const productReviewsHref = computed(() => {
    const slug = product.value?.slug;

    return slug ? `/reviews?product=${slug}` : '/reviews';
});

const quantity = ref(1);
const selectedImage = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

const nextImage = () => {
    const total = displayImages.value?.length || 0;
    if (!total) return;
    selectedImage.value = (selectedImage.value + 1) % total;
};

const prevImage = () => {
    const total = displayImages.value?.length || 0;
    if (!total) return;
    selectedImage.value = (selectedImage.value - 1 + total) % total;
};

const handleTouchStart = (e) => {
    touchStartX.value = e.touches[0].clientX;
};

const handleTouchMove = (e) => {
    touchEndX.value = e.touches[0].clientX;
};

const handleTouchEnd = () => {
    if (!touchStartX.value || !touchEndX.value) return;
    const distance = touchStartX.value - touchEndX.value;
    const minSwipeDistance = 40;
    if (distance > minSwipeDistance) {
        nextImage();
    } else if (distance < -minSwipeDistance) {
        prevImage();
    }
    touchStartX.value = 0;
    touchEndX.value = 0;
};
const isLiked = computed(() => wishlistIds.value.has(product.value?.id));

// Sticky mobile bottom bar
const showStickyBar = ref(false);
const buySectionRef = ref(null);
const handleStickyScroll = () => {
    if (!buySectionRef.value) return;
    const rect = buySectionRef.value.getBoundingClientRect();
    showStickyBar.value = rect.bottom < 0;
};
onMounted(() => window.addEventListener('scroll', handleStickyScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', handleStickyScroll));

const activeReviewTab = ref('reviews');
const reviewSubmitting = ref(false);
const reviewSuccessMessage = ref('');
const reviewErrors = ref({});
const reviewImages = ref([]);
const maxReviewImageSize = 3 * 1024 * 1024;
const reviewGallery = ref({
    open: false,
    images: [],
    initialIndex: 0,
    title: '',
});
const hoveredRating = ref(0);
const reviewForm = ref({
    rating: 5,
    comment: '',
});

const selectedVariantId = ref(product.value?.variants?.[0]?.id || null);

const activeVariant = computed(() => {
    if (!product.value?.variants?.length) return null;
    return product.value.variants.find((v) => v.id === selectedVariantId.value) || product.value.variants[0];
});

const displayImages = computed(() => {
    const list = [...(product.value?.images || [])];
    if (activeVariant.value?.image && !list.includes(activeVariant.value.image)) {
        list.unshift(activeVariant.value.image);
    }
    return list;
});

const selectVariant = (variant) => {
    selectedVariantId.value = variant.id;
    if (variant.image) {
        const idx = displayImages.value.indexOf(variant.image);
        if (idx !== -1) {
            selectedImage.value = idx;
        }
    }
};

const activePrice = computed(() => {
    if (activeVariant.value) return activeVariant.value.price;
    return product.value?.price || 0;
});

const activeOriginalPrice = computed(() => {
    if (activeVariant.value) return activeVariant.value.original_price;
    return product.value?.original_price || null;
});

const activeStockQuantity = computed(() => {
    if (activeVariant.value) return Number(activeVariant.value.stock_quantity ?? 0);
    return Number(product.value?.stock_quantity ?? 0);
});

const activeInStock = computed(() => {
    if (activeVariant.value) {
        return Boolean(activeVariant.value.in_stock && Number(activeVariant.value.stock_quantity ?? 0) > 0);
    }
    return Boolean((product.value?.in_stock ?? true) && Number(product.value?.stock_quantity ?? 0) > 0);
});

const handleAddToCart = () => {
    if (!activeInStock.value) {
        toast.error('This item is currently out of stock.');
        return;
    }
    if (quantity.value > activeStockQuantity.value) {
        toast.error(`Only ${activeStockQuantity.value} unit(s) available in stock.`);
        return;
    }
    addToCart(product.value.id, quantity.value, selectedVariantId.value);
};

const totalPrice = computed(() => Number(activePrice.value) * quantity.value);
const savingsAmount = computed(() =>
    Math.max(Number(activeOriginalPrice.value || 0) - Number(activePrice.value), 0),
);
const discountPercentage = computed(() => {
    if (!activeOriginalPrice.value || Number(activeOriginalPrice.value) <= Number(activePrice.value)) return null;
    const discount = ((Number(activeOriginalPrice.value) - Number(activePrice.value)) / Number(activeOriginalPrice.value)) * 100;
    return Math.round(discount);
});
const reviewImagePreviews = computed(() =>
    reviewImages.value.map((file) => ({
        file,
        url: URL.createObjectURL(file),
    })),
);

const openReviewForm = () => {
    activeReviewTab.value = 'write';
    reviewErrors.value = {};
    reviewSuccessMessage.value = '';
};

const handleReviewImageSelection = (event) => {
    const selectedFiles = Array.from(event.target.files || []);

    if (!selectedFiles.length) {
        return;
    }

    const oversizedFiles = selectedFiles.filter((file) => file.size > maxReviewImageSize);

    if (oversizedFiles.length) {
        reviewErrors.value = {
            ...reviewErrors.value,
            images: ['Each image must be 3MB or smaller.'],
        };
        event.target.value = '';
        return;
    }

    reviewErrors.value = {
        ...reviewErrors.value,
        images: undefined,
    };

    reviewImages.value = [...reviewImages.value, ...selectedFiles].slice(0, 5);
    event.target.value = '';
};

const removeReviewImage = (indexToRemove) => {
    reviewImages.value = reviewImages.value.filter((_, index) => index !== indexToRemove);
};

const openReviewGallery = (review, initialIndex = 0) => {
    if (!review?.images?.length) {
        return;
    }

    reviewGallery.value = {
        open: true,
        images: review.images,
        initialIndex,
        title: `${review.name} review images`,
    };
};

const submitReview = async () => {
    if (!product.value?.id) {
        return;
    }

    reviewSubmitting.value = true;
    reviewErrors.value = {};
    reviewSuccessMessage.value = '';

    if (reviewImages.value.some((file) => file.size > maxReviewImageSize)) {
        reviewErrors.value = {
            images: ['Each image must be 3MB or smaller.'],
        };
        reviewSubmitting.value = false;
        return;
    }

    const payload = new FormData();
    payload.append('product_id', product.value.id);
    payload.append('rating', Number(reviewForm.value.rating));
    payload.append('comment', reviewForm.value.comment);
    reviewImages.value.forEach((file) => payload.append('images[]', file));

    try {
        const { data } = await window.axios.post('/api/public/reviews', payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        reviewSuccessMessage.value = data.message;
        reviewForm.value = {
            rating: 5,
            comment: '',
        };
        reviewImages.value = [];
        activeReviewTab.value = 'reviews';
    } catch (error) {
        reviewErrors.value = error.response?.data?.errors || {};
    } finally {
        reviewSubmitting.value = false;
    }
};

onBeforeUnmount(() => {
    reviewImagePreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
});
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div v-if="product" class="min-h-screen bg-background py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-2 text-sm mb-8">
                    <Link href="/" class="text-muted-foreground hover:text-amber-500 transition-colors">Home</Link>
                    <span class="text-muted-foreground">/</span>
                    <Link href="/shop" class="text-muted-foreground hover:text-amber-500 transition-colors">Shop</Link>
                    <span class="text-muted-foreground">/</span>
                    <span class="text-foreground">{{ product.title }}</span>
                </div>

                <div class="grid lg:grid-cols-2 gap-12 mb-16">
                    <div v-reveal="{ preset: 'zoom', duration: 760 }" class="space-y-4">
                        <!-- Main Image Carousel Frame -->
                        <div
                            class="relative aspect-square overflow-hidden rounded-xl border border-border bg-card shadow-luxury group select-none"
                            @touchstart="handleTouchStart"
                            @touchmove="handleTouchMove"
                            @touchend="handleTouchEnd"
                        >
                            <div
                                v-if="displayImages?.length"
                                class="flex h-full w-full transition-transform duration-500 ease-out"
                                :style="{ transform: `translateX(-${selectedImage * 100}%)` }"
                            >
                                <img
                                    v-for="(image, index) in displayImages"
                                    :key="image + index"
                                    :src="image"
                                    :alt="`${product.title} ${index + 1}`"
                                    class="h-full w-full flex-shrink-0 object-cover"
                                />
                            </div>
                            <div v-else
                                class="flex h-full w-full items-center justify-center px-10 text-center text-lg font-medium text-muted-foreground">
                                No gallery image available
                            </div>

                            <!-- Savings & Badge Labels -->
                            <div v-if="activeOriginalPrice"
                                class="absolute top-4 left-4 z-10 px-3 py-1 bg-amber-500 text-slate-950 font-bold rounded-full text-sm shadow-sm">
                                Save {{ formatPrice(savingsAmount) }}
                            </div>
                            <div v-if="product.badge_label"
                                class="absolute top-4 right-4 z-10 px-3 py-1 bg-card/90 text-foreground rounded-full text-sm font-medium border border-border shadow-sm">
                                {{ product.badge_label }}
                            </div>

                            <!-- Carousel Controls (When multiple images exist) -->
                            <template v-if="displayImages && displayImages.length > 1">
                                <!-- Previous Button -->
                                <button
                                    @click.prevent="prevImage"
                                    class="public-interactive absolute left-3 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-md transition-all hover:bg-amber-500 hover:text-black focus:outline-none opacity-90 md:opacity-0 md:group-hover:opacity-100 shadow-lg"
                                    aria-label="Previous product image"
                                >
                                    <ChevronLeft class="h-6 w-6" />
                                </button>

                                <!-- Next Button -->
                                <button
                                    @click.prevent="nextImage"
                                    class="public-interactive absolute right-3 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-md transition-all hover:bg-amber-500 hover:text-black focus:outline-none opacity-90 md:opacity-0 md:group-hover:opacity-100 shadow-lg"
                                    aria-label="Next product image"
                                >
                                    <ChevronRight class="h-6 w-6" />
                                </button>

                                <!-- Slide Counter Pill -->
                                <div class="absolute bottom-3 right-3 z-10 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white backdrop-blur-sm shadow-sm">
                                    {{ selectedImage + 1 }} / {{ displayImages.length }}
                                </div>

                                <!-- Dot Indicators -->
                                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex items-center space-x-1.5 rounded-full bg-black/40 px-3 py-1.5 backdrop-blur-sm">
                                    <button
                                        v-for="(_, index) in displayImages"
                                        :key="'dot-' + index"
                                        @click="selectedImage = index"
                                        :class="[
                                            'h-2 rounded-full transition-all duration-300',
                                            selectedImage === index ? 'w-5 bg-amber-500' : 'w-2 bg-white/60 hover:bg-white'
                                        ]"
                                        :aria-label="`Go to image slide ${index + 1}`"
                                    />
                                </div>
                            </template>
                        </div>

                        <!-- Thumbnail Carousel Strip -->
                        <div v-if="displayImages && displayImages.length > 1" class="flex items-center gap-3 overflow-x-auto pb-1 pt-1 scrollbar-thin">
                            <button v-for="(image, index) in displayImages" :key="'thumb-' + index" @click="selectedImage = index"
                                class="public-interactive relative flex-shrink-0 aspect-square w-20 overflow-hidden rounded-lg border-2 transition-all"
                                :class="selectedImage === index ? 'border-amber-500 ring-2 ring-amber-500/40 shadow-md scale-[1.03]' : 'border-border opacity-70 hover:opacity-100 hover:border-amber-500/50'">
                                <img :src="image" :alt="`${product.title} thumbnail ${index + 1}`" loading="lazy"
                                    class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 680 }" class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-amber-500 uppercase tracking-wider">{{
                                    product.category || 'Specialty Dish' }}</span>
                                <button @click="toggleWishlist(product.id)"
                                    class="public-interactive flex h-10 w-10 items-center justify-center rounded-full border border-border transition-colors hover:border-amber-500"
                                    aria-label="Save product">
                                    <Heart
                                        :class="`w-5 h-5 ${isLiked ? 'fill-red-500 text-red-500' : 'text-muted-foreground'}`" />
                                </button>
                            </div>

                            <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-4 font-heading">{{ product.title }}</h1>

                            <div class="flex items-center space-x-2 mb-4">
                                <div class="flex items-center space-x-1">
                                    <Star v-for="i in 5" :key="i"
                                        :class="`w-5 h-5 ${i <= Math.round(product.rating || 0) ? 'text-amber-500 fill-amber-500' : 'text-gray-300 dark:text-gray-700'}`" />
                                </div>
                                <span class="text-sm text-foreground font-medium">{{ product.rating || 0 }}</span>
                                <Link :href="productReviewsHref"
                                    class="text-sm text-muted-foreground transition-colors hover:text-amber-500">
                                    ({{ product.reviews_count }} reviews)
                                </Link>
                            </div>

                            <div class="flex items-center gap-3 mb-6 flex-wrap">
                                <span class="text-4xl font-extrabold text-foreground tracking-tight">{{ formatPrice(activePrice) }}</span>
                                <span v-if="activeOriginalPrice" class="text-xl text-muted-foreground line-through decoration-red-500/60 font-medium">
                                    {{ formatPrice(activeOriginalPrice) }}
                                </span>
                                <span v-if="discountPercentage" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-slate-950 shadow-xs">
                                    SAVE {{ discountPercentage }}%
                                </span>
                            </div>

                            <p class="text-muted-foreground leading-relaxed mb-6">{{ product.description ||
                                product.short_description }}</p>

                            <!-- Variant Selector -->
                            <div v-if="product.variants && product.variants.length > 0" class="mb-8 rounded-2xl border border-border/80 bg-card/60 p-5 backdrop-blur-xs shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                        Select Option / Portion Size
                                    </label>
                                    <span v-if="activeVariant?.name" class="text-xs font-semibold text-amber-500">
                                        Selected: <span class="text-foreground font-bold">{{ activeVariant.name }}</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button
                                        v-for="variant in product.variants"
                                        :key="variant.id"
                                        @click="selectVariant(variant)"
                                        :class="[
                                            'public-interactive relative flex items-center gap-3.5 p-3 rounded-xl border-2 transition-all duration-200 text-left cursor-pointer group',
                                            selectedVariantId === variant.id
                                                ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/20 shadow-md scale-[1.01]'
                                                : 'border-border bg-background hover:border-amber-500/50 hover:bg-muted/30'
                                        ]"
                                    >
                                        <div class="relative shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-border group-hover:border-amber-500/60">
                                            <img
                                                v-if="variant.image"
                                                :src="variant.image"
                                                :alt="variant.name"
                                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                            />
                                            <div v-else class="w-full h-full bg-muted flex items-center justify-center text-[10px] font-bold text-muted-foreground uppercase">
                                                No Image
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-foreground truncate group-hover:text-amber-500 transition-colors">
                                                {{ variant.name }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs font-extrabold text-amber-500">{{ formatPrice(variant.price) }}</span>
                                                <span v-if="variant.sku" class="text-[10px] text-muted-foreground font-mono truncate">SKU: {{ variant.sku }}</span>
                                            </div>
                                        </div>
                                        <div v-if="selectedVariantId === variant.id" class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-slate-950 text-xs font-bold shadow-xs">
                                            ✓
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 mb-6">
                                <template v-if="activeInStock">
                                    <div class="w-2 h-2 rounded-full bg-green-500" />
                                    <span class="text-sm text-green-600 font-medium">In Stock ({{ activeStockQuantity }} available)</span>
                                </template>
                                <template v-else>
                                    <div class="w-2 h-2 rounded-full bg-red-500" />
                                    <span class="text-sm text-red-600 font-medium">Out of Stock</span>
                                </template>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-foreground mb-2">Quantity</label>
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center border border-border rounded-lg">
                                        <button @click="quantity = Math.max(1, quantity - 1)"
                                            class="public-interactive px-4 py-2 transition-colors hover:bg-muted"
                                            :disabled="quantity <= 1">-</button>
                                        <span class="px-6 py-2 border-x border-border font-medium">{{ quantity }}</span>
                                        <button @click="quantity = Math.min(activeStockQuantity || 1, quantity + 1)"
                                            class="public-interactive px-4 py-2 transition-colors hover:bg-muted"
                                            :disabled="!activeInStock || quantity >= activeStockQuantity">+</button>
                                    </div>
                                    <span class="text-sm text-muted-foreground">Total: <span
                                            class="font-bold text-foreground">{{
                                                formatPrice(totalPrice) }}</span></span>
                                </div>
                            </div>

                            <div ref="buySectionRef" class="flex flex-col sm:flex-row gap-4 mb-8">
                                <Button size="lg" class="flex-1" :disabled="!activeInStock" @click="handleAddToCart">
                                    <ShoppingCart class="w-5 h-5 mr-2" />
                                    {{ activeInStock ? 'Add to Order' : 'Sold Out for Today' }}
                                </Button>
                                <Link href="/custom-order"
                                    class="public-interactive inline-flex flex-1 items-center justify-center rounded-lg border border-border bg-card px-6 py-3 text-sm font-medium text-foreground transition-luxury hover:bg-muted">
                                    Special Table / Dietary Request</Link>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 mt-6">
                                <div
                                    class="public-card flex items-center space-x-3 rounded-lg border border-border bg-card p-4">
                                    <Truck class="w-6 h-6 text-amber-500" />
                                    <div>
                                        <div class="text-sm font-medium text-foreground">Hot & Prompt Delivery</div>
                                        <div class="text-xs text-muted-foreground">{{ product.shipping_note || 'Delivered hot in insulated packaging to maintain freshness.' }}</div>
                                    </div>
                                </div>
                                <div
                                    class="public-card flex items-center space-x-3 rounded-lg border border-border bg-card p-4">
                                    <Shield class="w-6 h-6 text-amber-500" />
                                    <div>
                                        <div class="text-sm font-medium text-foreground">Chef's Serving Tips</div>
                                        <div class="text-xs text-muted-foreground">{{ product.care_instructions || 'Best enjoyed fresh and piping hot upon serving.' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-reveal="{ preset: 'fadeUp', duration: 680 }" class="mb-16" id="product-reviews">
                    <SectionHeader title="Customer Reviews"
                        subtitle="A focused preview of the latest customer feedback for this item." />

                    <div class="mb-8 rounded-full border border-amber-500/30 bg-card p-1 shadow-luxury">
                        <div class="grid grid-cols-2 gap-1">
                            <button type="button" @click="activeReviewTab = 'reviews'"
                                class="rounded-full px-5 py-3 text-sm font-bold transition-colors"
                                :class="activeReviewTab === 'reviews' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-foreground hover:bg-amber-500/10'">Latest
                                Reviews</button>
                            <button type="button" @click="openReviewForm"
                                class="rounded-full px-5 py-3 text-sm font-bold transition-colors"
                                :class="activeReviewTab === 'write' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-foreground hover:bg-amber-500/10'">Write
                                a Review</button>
                        </div>
                    </div>

                    <p v-if="reviewSuccessMessage"
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-300">
                        {{ reviewSuccessMessage }}
                    </p>

                    <div v-if="activeReviewTab === 'write'" id="product-review-form"
                        class="rounded-2xl border border-border bg-card p-5 shadow-luxury sm:p-8">
                        <template v-if="customer">
                            <h3 class="text-xl font-semibold text-foreground mb-2 sm:text-2xl">Add a review for {{ product.title }}
                            </h3>
                            <p class="text-sm text-muted-foreground mb-6 sm:mb-8">Reviewing as <span
                                     class="font-semibold text-foreground">{{ customer.name }}</span>. Your review will
                                be submitted for admin approval before it appears publicly.</p>

                            <form @submit.prevent="submitReview" class="space-y-5 sm:space-y-6">
                                <div class="grid lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] gap-5 sm:gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Rating *</label>
                                    <div class="flex items-center gap-1">
                                        <button v-for="i in 5" :key="i" type="button"
                                            @click="reviewForm.rating = i"
                                            @mouseenter="hoveredRating = i"
                                            @mouseleave="hoveredRating = 0"
                                            class="public-interactive rounded-lg p-1.5 transition-colors hover:scale-110"
                                            :aria-label="`${i} star${i > 1 ? 's' : ''}`">
                                            <Star :class="`w-7 h-7 ${(hoveredRating || reviewForm.rating) >= i ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}`" />
                                        </button>
                                    </div>
                                    <p v-if="reviewErrors.rating" class="mt-2 text-sm text-red-600">{{
                                        reviewErrors.rating[0] }}</p>

                                    <div
                                        class="mt-5 rounded-2xl border border-dashed border-amber-500/30 bg-amber-500/5 p-4 sm:mt-6 sm:p-5">
                                        <label class="block text-sm font-medium text-foreground mb-2">Review
                                            Images</label>
                                        <label
                                            class="public-interactive flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-border bg-card px-5 py-4 text-center transition-colors hover:border-amber-500/50 sm:px-6 sm:py-8">
                                            <Upload class="mb-3 h-7 w-7 text-amber-500 sm:h-9 sm:w-9" />
                                            <span class="text-sm font-medium text-foreground">Upload customer
                                                photos</span>
                                            <span class="mt-1 text-xs text-muted-foreground">JPG, PNG, or WEBP up to 5
                                                images and 3MB
                                                each</span>
                                            <input type="file" accept="image/png,image/jpeg,image/webp" multiple
                                                class="hidden" @change="handleReviewImageSelection" />
                                        </label>
                                    </div>

                                    <div v-if="reviewImagePreviews.length" class="mt-4 grid grid-cols-2 gap-3">
                                        <div v-for="(preview, index) in reviewImagePreviews"
                                            :key="`${preview.file.name}-${preview.file.size}`"
                                            class="relative overflow-hidden rounded-xl border border-border">
                                            <img :src="preview.url" :alt="preview.file.name" loading="lazy"
                                                class="h-20 w-full object-cover sm:h-28" />
                                            <button type="button" @click="removeReviewImage(index)"
                                                class="absolute right-2 top-2 rounded-full bg-black/70 p-1 text-white">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="reviewErrors.images" class="mt-2 text-sm text-red-600">{{
                                        reviewErrors.images[0] }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Review *</label>
                                    <textarea v-model="reviewForm.comment" rows="4"
                                        class="public-field w-full resize-none rounded-lg border border-border bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark] sm:rows-6"
                                        placeholder="Share your honest experience..." />
                                    <p v-if="reviewErrors.comment" class="mt-2 text-sm text-red-600">{{
                                        reviewErrors.comment[0] }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <Button type="submit" size="lg" :class="{ 'public-loading': reviewSubmitting }"
                                    :disabled="reviewSubmitting" class="w-full sm:w-auto">{{ reviewSubmitting ? 'Submitting Review...' : 'Submit Review' }}</Button>
                                <Button type="button" variant="outline" @click="activeReviewTab = 'reviews'" class="w-full sm:w-auto">Back to
                                    Reviews</Button>
                            </div>
                        </form>
                    </template>
                    <template v-else>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <p class="text-lg font-medium text-foreground mb-2">Sign in to write a review</p>
                            <p class="text-sm text-muted-foreground mb-6">You need to be logged in to share your
                                experience.</p>
                            <Button @click="openAuthModal">Sign In</Button>
                        </div>
                    </template>
                    </div>

                    <div v-else class="space-y-6">
                        <div class="rounded-2xl border border-border bg-card/70 p-5 shadow-luxury backdrop-blur-sm">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 text-xl font-extrabold text-slate-950 shadow-sm">
                                        {{ product.rating || 0 }}
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-1">
                                            <Star v-for="i in 5" :key="i"
                                                :class="`h-4 w-4 ${i <= Math.round(product.rating || 0) ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}`" />
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-foreground">{{ product.reviews_count }}
                                            customer reviews
                                        </p>
                                        <p class="text-xs text-muted-foreground">Showing the latest 3 reviews for a
                                            cleaner preview.</p>
                                    </div>
                                </div>

                                <div
                                    class="inline-flex self-start rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-500">
                                    Latest Feedback
                                </div>
                            </div>
                        </div>

                        <div v-if="reviews.length" v-stagger="{ preset: 'fadeUp', stagger: 90, duration: 620 }"
                            class="grid gap-4">
                            <article v-for="review in reviews" :key="review.id"
                                class="public-card rounded-2xl border border-border bg-card p-4 shadow-luxury transition-luxury hover:border-amber-500/40 sm:p-5">
                                <div class="grid gap-4 sm:gap-5 lg:grid-cols-[minmax(0,1fr)_180px] lg:items-start">
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-xs font-bold uppercase text-amber-500 sm:h-12 sm:w-12">
                                                {{review.name.split(' ').slice(0, 2).map((word) => word[0]).join('')}}
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                                    <h4 class="text-sm font-semibold text-foreground sm:text-base">{{ review.name
                                                        }}</h4>
                                                    <span v-if="review.verified"
                                                        class="rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-1 text-[10px] font-semibold text-amber-500 sm:px-2.5 sm:text-[11px]">Verified</span>
                                                </div>

                                                <div
                                                    class="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground sm:text-sm">
                                                    <span v-if="review.date">{{ review.date }}</span>
                                                </div>

                                                <div class="mt-2 flex items-center gap-2 sm:mt-3 sm:gap-3">
                                                    <div class="flex items-center space-x-0.5 sm:space-x-1">
                                                        <Star v-for="i in 5" :key="i"
                                                            :class="`h-3.5 w-3.5 sm:h-4 sm:w-4 ${i <= review.rating ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}`" />
                                                    </div>
                                                    <span
                                                        class="text-[10px] font-medium uppercase tracking-[0.18em] text-muted-foreground sm:text-xs">Helpful
                                                        {{ review.helpful ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="mt-3 text-sm leading-6 text-foreground/85 line-clamp-3 sm:mt-4 sm:leading-7">{{
                                            review.comment }}</p>
                                    </div>

                                    <div v-if="review.images?.length"
                                        class="relative h-20 w-20 sm:h-28 sm:w-28">
                                        <button type="button" class="block h-full w-full"
                                            @click="openReviewGallery(review, 0)">
                                            <span v-for="(image, i) in review.images.slice(0, 3)" :key="i"
                                                class="absolute h-full w-full overflow-hidden rounded-xl bg-background shadow-sm"
                                                :style="{
                                                    left: i * 4 + 'px',
                                                    top: i * 4 + 'px',
                                                    zIndex: 10 - i,
                                                }">
                                                <img :src="image" :alt="`${review.name} review`" loading="lazy"
                                                    class="h-full w-full object-cover" />
                                            </span>
                                            <span v-if="review.images.length > 3"
                                                class="absolute -bottom-1 -right-1 z-20 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-black/60 px-1 text-[9px] font-medium text-white">
                                                +{{ review.images.length - 3 }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="rounded-2xl border border-border bg-card p-8 text-center">
                                <p class="text-lg font-medium text-foreground">No approved reviews are available for this
                                    product yet.</p>
                            <p class="mt-2 text-sm text-muted-foreground">Be the first to share your experience and help
                                future buyers.
                            </p>
                            <Button class="mt-5" @click="openReviewForm">Write the First Review</Button>
                        </div>

                        <div v-if="product.reviews_count > reviews.length" class="pt-2">
                            <Link :href="productReviewsHref"
                                class="group flex items-center justify-between rounded-2xl border border-amber-500/20 bg-gradient-to-r from-amber-500/10 to-transparent px-5 py-4 transition-colors hover:border-amber-500/40 hover:from-amber-500/20">
                                <div>
                                    <p class="text-sm font-semibold text-foreground">See all reviews for {{
                                        product.title }}</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Open the dedicated review page for the
                                        full product
                                        feedback list.</p>
                                </div>
                                <span
                                    class="rounded-full bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 transition-transform group-hover:translate-x-1">
                                    View More
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="relatedProducts.length" v-reveal="{ preset: 'fadeUp', duration: 680 }">
                    <div class="flex items-center justify-between mb-8">
                        <SectionHeader title="Related Dishes" />
                        <Link href="/shop"
                            class="public-interactive text-sm font-semibold text-amber-500 hover:text-amber-600">View All Menu
                        </Link>
                    </div>
                    <div v-stagger="{ preset: 'fadeUp', stagger: 85, duration: 620 }"
                        class="grid grid-cols-2 gap-3 md:gap-6 lg:grid-cols-4">
                        <ProductCard v-for="related in relatedProducts" :key="related.slug" :id="related.id"
                            :slug="related.slug" :title="related.title" :price="related.price"
                            :compare-price="related.compare_price" :image="related.image"
                            :category="related.category" :rating="related.rating"
                            :reviews-count="related.reviews_count"
                            :in-stock="related.in_stock" :stock-quantity="related.stock_quantity" />
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="min-h-screen bg-background py-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl font-bold text-foreground mb-4">Dish not found</h1>
                <p class="text-muted-foreground mb-8">This dish is unavailable or no longer on the menu.</p>
                <Link href="/shop"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-6 py-3 text-sm font-bold text-slate-950 shadow-sm transition-luxury hover:bg-amber-600 hover:shadow-md">
                    Back to Menu</Link>
            </div>
        </div>

        <ReviewImageLightbox :open="reviewGallery.open" :images="reviewGallery.images"
            :initial-index="reviewGallery.initialIndex" :title="reviewGallery.title"
            @update:open="reviewGallery.open = $event" />

        <!-- Sticky Mobile Bottom Add-to-Cart Bar -->
        <Transition
            enter-active-class="transition duration-300 ease-out transform"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition duration-200 ease-in transform"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="product && showStickyBar"
                class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/95 backdrop-blur-md px-4 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.1)] md:hidden"
            >
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-muted-foreground truncate">{{ activeVariant?.name || product.title }}</p>
                        <p class="text-lg font-extrabold text-foreground leading-tight">{{ formatPrice(activePrice) }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 rounded-lg border border-border">
                        <button @click="quantity = Math.max(1, quantity - 1)" :disabled="quantity <= 1" class="px-2.5 py-1.5 text-sm text-foreground disabled:opacity-40">−</button>
                        <span class="px-2 py-1.5 text-sm font-semibold text-foreground border-x border-border min-w-[2rem] text-center">{{ quantity }}</span>
                        <button @click="quantity = Math.min(activeStockQuantity || 1, quantity + 1)" :disabled="!activeInStock || quantity >= activeStockQuantity" class="px-2.5 py-1.5 text-sm text-foreground disabled:opacity-40">+</button>
                    </div>
                    <button
                        @click="handleAddToCart"
                        :disabled="!activeInStock"
                        :class="[
                            'shrink-0 inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition-transform',
                            activeInStock
                                ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold active:scale-95 shadow-md'
                                : 'bg-muted text-muted-foreground cursor-not-allowed opacity-60'
                        ]"
                    >
                        <ShoppingCart class="w-4 h-4" />
                        {{ activeInStock ? 'Add' : 'Out of Stock' }}
                    </button>
                </div>
            </div>
        </Transition>
    </PublicLayout>
</template>
