<script setup>
import { usePage } from '@inertiajs/vue3';
import { Lock } from 'lucide-vue-next';
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
                    <SectionHeader tag="h1" title="Privacy Policy" subtitle="How we collect, use, and protect your personal information." centered />
                    <div class="mt-8 sm:mt-12 space-y-6 sm:space-y-8 text-sm sm:text-base text-muted-foreground leading-relaxed">
                        <div v-reveal="{ preset: 'fadeUp', duration: 600 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-amber-500" /> 1. Information We Collect
                            </h2>
                            <p>We collect information you provide when placing an order, creating an account, or contacting us. This may include your name, email address, shipping address, phone number, and payment information. We also automatically collect certain technical information about your device and browsing behavior.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 50 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-amber-500" /> 2. How We Use Your Information
                            </h2>
                            <p>We use your information to process orders, communicate with you about your purchases, improve our products and services, and send marketing communications if you have opted in. We do not sell your personal information to third parties.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 100 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-amber-500" /> 3. Data Security
                            </h2>
                            <p>We implement industry-standard security measures to protect your personal information, including SSL encryption for data transmission and secure storage of payment information. However, no method of electronic storage is 100% secure.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 150 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-amber-500" /> 4. Cookies
                            </h2>
                            <p>We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. You can control cookie settings through your browser preferences. Disabling cookies may affect certain features of our website.</p>
                        </div>
                        <div v-reveal="{ preset: 'fadeUp', duration: 600, delay: 200 }">
                            <h2 class="text-xl font-semibold text-foreground mb-3 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-amber-500" /> 5. Your Rights
                            </h2>
                            <p>You have the right to access, correct, or delete your personal information at any time. You may also opt out of marketing communications. To exercise these rights, please contact us using the information on our Contact page.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
