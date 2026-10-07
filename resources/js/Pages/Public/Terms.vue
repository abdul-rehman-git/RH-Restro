<script setup>
import { usePage } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
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
                    <SectionHeader tag="h1" title="Terms of Service" subtitle="Please read these terms carefully before using our website." centered />
                    <div class="mt-8 sm:mt-12 space-y-6 sm:space-y-8 text-sm sm:text-base text-muted-foreground leading-relaxed">
                        <div v-reveal="{ preset: 'fadeUp', duration: 600 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Shield class="w-5 h-5 text-amber-500" /> 1. Acceptance of Terms
                            </h2>
                            <p>By accessing and using this website, you accept and agree to be bound by the terms and conditions of this agreement. If you do not agree to these terms, please do not use our website.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 50 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Shield class="w-5 h-5 text-amber-500" /> 2. Products and Pricing
                            </h2>
                            <p>All product descriptions, images, and pricing are subject to change without notice. We reserve the right to modify or discontinue any product at any time. We make every effort to display accurate colors and details, but we cannot guarantee that your monitor's display is accurate.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 100 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Shield class="w-5 h-5 text-amber-500" /> 3. Orders and Payment
                            </h2>
                            <p>All orders are subject to acceptance and kitchen availability. We reserve the right to refuse or cancel any order. Payment must be confirmed or selected as Cash on Delivery before your order is prepared. Prices are in PKR (Rs.) and include applicable taxes.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 150 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Shield class="w-5 h-5 text-amber-500" /> 4. Intellectual Property
                            </h2>
                            <p>All content on this website, including text, graphics, logos, images, and software, is the property of our company and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without our explicit written consent.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 200 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Shield class="w-5 h-5 text-amber-500" /> 5. Limitation of Liability
                            </h2>
                            <p>We shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or relating to your use of the website or products purchased through the website. Our total liability shall not exceed the amount paid by you for the product in question.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
