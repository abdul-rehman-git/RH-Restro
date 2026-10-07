<script setup>
import { usePage } from '@inertiajs/vue3';
import { RotateCcw } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background">
            <section class="py-8 sm:py-20 bg-background">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader title="Returns & Exchanges" subtitle="Our hassle-free return and exchange policy." centered />
                    <div class="mt-8 sm:mt-12 space-y-6 sm:space-y-8 text-sm sm:text-base text-muted-foreground leading-relaxed">
                        <div v-reveal="{ preset: 'fadeUp', duration: 600 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <RotateCcw class="w-5 h-5 text-amber-500" /> 1. Return Window
                            </h2>
                            <p>We accept returns within 14 days of delivery. Items must be unworn, in their original condition, and with all tags and packaging intact. Custom and personalized items are non-returnable unless defective.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 50 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <RotateCcw class="w-5 h-5 text-amber-500" /> 2. How to Initiate a Return
                            </h2>
                            <p>To start a return, please contact us through our Contact page with your order number and reason for return. Our team will provide you with a return authorization and shipping instructions.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 100 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <RotateCcw class="w-5 h-5 text-amber-500" /> 3. Refunds
                            </h2>
                            <p>Once we receive and inspect your return, we will notify you of the approval status. Approved refunds will be processed within 5-10 business days and credited to your original payment method. Shipping costs are non-refundable.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 150 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <RotateCcw class="w-5 h-5 text-amber-500" /> 4. Exchanges
                            </h2>
                            <p>We offer exchanges for items of equal value. If you wish to exchange your purchase for a different size or model, please initiate a return and place a new order for the desired item. This ensures the fastest processing time.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 200 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <RotateCcw class="w-5 h-5 text-amber-500" /> 5. Damaged or Defective Items
                            </h2>
                            <p>If you receive a damaged or defective item, please contact us within 48 hours of delivery with photos of the damage. We will arrange a replacement or full refund, including return shipping costs.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
