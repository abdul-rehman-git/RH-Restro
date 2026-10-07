<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { Star } from "lucide-vue-next";
import SeoHead from "@/Components/SeoHead.vue";
import PaginationNav from "@/Components/PaginationNav.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import ReviewImageLightbox from "@/public/components/ReviewImageLightbox.vue";
import SectionHeader from "@/public/components/SectionHeader.vue";

const page = usePage();
const reviews = computed(
    () =>
        page.props.reviews || {
            data: [],
            total: 0,
            current_page: 1,
            last_page: 1,
        },
);
const summary = computed(
    () =>
        page.props.summary || {
            average_rating: 0,
            total_reviews: 0,
            breakdown: [],
        },
);
const productFilter = computed(() => page.props.productFilter || null);
const currentPage = computed(() => reviews.value.current_page || 1);
const reviewGallery = ref({
    open: false,
    images: [],
    initialIndex: 0,
    title: "",
});

const handlePageChange = (nextPage) => {
    if (nextPage < 1 || nextPage > (reviews.value.last_page || 1)) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    params.set("page", String(nextPage));
    router.get(`/reviews?${params.toString()}`);
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

const initials = (name) =>
    name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background py-8 sm:py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    :title="
                        productFilter
                            ? `${productFilter.title} Reviews`
                            : 'Customer Reviews'
                    "
                />

                <div class="mb-6 sm:mb-8 flex flex-col gap-3 rounded-xl border border-border bg-card p-3.5 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4 sm:p-5">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-bold text-foreground">{{ summary.average_rating || 0 }}</span>
                        <div>
                            <div class="flex items-center space-x-0.5">
                                <Star v-for="i in 5" :key="i"
                                    :class="`h-4 w-4 ${i <= Math.round(summary.average_rating || 0) ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}`" />
                            </div>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ summary.total_reviews || 0 }} reviews</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:ml-auto">
                        <Link v-if="productFilter" :href="`/product/${productFilter.slug}#product-reviews`"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 transition-colors hover:bg-amber-600">
                            Back to Dish
                        </Link>
                        <Link v-else href="/shop"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 transition-colors hover:bg-amber-600">
                            Write a Review
                        </Link>
                    </div>
                </div>

                <div v-if="reviews.data?.length" class="space-y-4">
                    <div v-for="review in reviews.data || []" :key="review.id"
                        class="rounded-xl border border-border bg-card p-4 sm:p-5">
                        <div class="flex items-start gap-3 mb-3">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-xs font-bold text-amber-500">
                                {{ initials(review.name) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-foreground">{{ review.name }}</span>
                                    <span v-if="review.verified"
                                        class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-500">Verified</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="flex items-center space-x-0.5">
                                        <Star v-for="i in 5" :key="i"
                                            :class="`h-3.5 w-3.5 ${i <= review.rating ? 'text-amber-500 fill-amber-500' : 'text-gray-300'}`" />
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ review.date }}</span>
                                </div>
                            </div>
                        </div>

                        <p v-if="review.product && !productFilter"
                            class="mb-2 text-[10px] uppercase font-bold tracking-wider text-amber-500">
                            {{ review.product.title }}
                        </p>

                        <p class="text-sm text-foreground leading-relaxed">{{ review.comment }}</p>

                        <div v-if="review.images?.length" class="relative mt-3 h-20 w-20 sm:h-24 sm:w-24">
                            <button type="button" class="block h-full w-full" @click="openReviewGallery(review, 0)">
                                <span v-for="(image, i) in review.images.slice(0, 3)" :key="i"
                                    class="absolute h-full w-full overflow-hidden rounded-xl bg-background shadow-sm"
                                    :style="{
                                        left: i * 4 + 'px',
                                        top: i * 5 + 'px',
                                        zIndex: 10 - i,
                                    }">
                                    <img :src="image" :alt="`${review.name} review`" loading="lazy" class="h-full w-full object-cover" />
                                </span>
                                <span v-if="review.images.length > 3"
                                    class="absolute -bottom-1 -right-1 z-20 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-black/60 px-1 text-[9px] font-medium text-white">
                                    +{{ review.images.length - 3 }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-xl border border-border bg-card p-6 text-center sm:p-8">
                    <p class="text-base font-medium text-foreground">
                        {{ productFilter
                            ? `No reviews yet for ${productFilter.title}.`
                            : "No reviews yet."
                        }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">Be the first to share your experience.</p>
                    <div class="mt-4">
                        <Link v-if="productFilter" :href="`/product/${productFilter.slug}#product-reviews`"
                            class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950 transition-colors hover:bg-amber-600">
                            Write a Review
                        </Link>
                        <Link v-else href="/shop"
                            class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950 transition-colors hover:bg-amber-600">
                            Browse Menu
                        </Link>
                    </div>
                </div>

                <div v-if="(reviews.last_page || 1) > 1" class="pt-6">
                    <PaginationNav
                        :current-page="currentPage"
                        :last-page="reviews.last_page || 1"
                        :on-page-change="handlePageChange"
                        variant="public"
                    />
                </div>
            </div>
        </div>

        <ReviewImageLightbox
            :open="reviewGallery.open"
            :images="reviewGallery.images"
            :initial-index="reviewGallery.initialIndex"
            :title="reviewGallery.title"
            @update:open="reviewGallery.open = $event"
        />
    </PublicLayout>
</template>
