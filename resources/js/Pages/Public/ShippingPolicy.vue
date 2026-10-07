<script setup>
import { usePage } from '@inertiajs/vue3';
import { Truck } from 'lucide-vue-next';
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
                    <SectionHeader title="Shipping Policy" subtitle="Everything you need to know about shipping and delivery." centered />
                    <div class="mt-8 sm:mt-12 space-y-6 sm:space-y-8 text-sm sm:text-base text-muted-foreground leading-relaxed">
                        <div v-reveal="{ preset: 'fadeUp', duration: 600 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Truck class="w-5 h-5 text-amber-500" /> 1. Processing Time
                            </h2>
                            <p>Orders are processed within 1-3 business days after payment confirmation. Custom and personalized items may require additional processing time, which will be communicated to you at the time of order.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 50 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Truck class="w-5 h-5 text-amber-500" /> 2. Domestic Shipping & Delivery
                            </h2>
                            <p>We offer free express delivery on all orders over Rs. 2,500. Standard delivery typically takes 30-45 minutes within city limits. Thermal insulated heat-sealed packaging is used for all orders.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 100 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Truck class="w-5 h-5 text-amber-500" /> 3. International Shipping
                            </h2>
                            <p>We ship worldwide. International delivery times range from 10-20 business days depending on the destination and customs processing. Customers are responsible for any applicable duties, taxes, and customs fees.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 150 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Truck class="w-5 h-5 text-amber-500" /> 4. Order Tracking
                            </h2>
                            <p>Once your order is shipped, you will receive a confirmation email with a tracking number. You can monitor your delivery status on our Order Tracking page or directly through the carrier's website.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 200 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Truck class="w-5 h-5 text-amber-500" /> 5. Shipping Delays
                            </h2>
                            <p>While we strive to meet delivery estimates, we are not responsible for delays caused by carriers, weather conditions, customs processing, or other unforeseen circumstances. If your order is significantly delayed, please contact us for assistance.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
