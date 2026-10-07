<script setup>
import { usePage } from '@inertiajs/vue3';
import { HelpCircle, ChevronDown } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();

const faqs = [
    {
        q: 'What payment methods do you accept?',
        a: 'We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and bank transfers for custom orders. All transactions are processed securely.',
    },
    {
        q: 'How long does shipping take?',
        a: 'Domestic orders typically arrive within 5-10 business days. International shipping can take 10-20 business days depending on the destination and customs clearance.',
    },
    {
        q: 'Do you offer international shipping?',
        a: 'Yes, we ship worldwide. International shipping rates and delivery times vary by location. Duties and taxes may apply and are the responsibility of the customer.',
    },
    {
        q: 'Can I track my order?',
        a: 'Yes, once your order is dispatched, you will receive a tracking number via email. You can also track your order on our Order Tracking page.',
    },
    {
        q: 'What is your return policy?',
        a: 'We offer a 14-day return policy for unworn items in original condition. Custom and personalized items are non-returnable unless defective.',
    },
    {
        q: 'How do I care for my items?',
        a: 'Keep products stored in a dry, temperate environment. Follow product-specific care instructions included with your packaging for cleaning and upkeep.',
    },
    {
        q: 'Do you offer warranty?',
        a: 'All our products come with a 1-year warranty against manufacturing defects. The warranty does not cover accidental damage or normal wear and tear.',
    },
    {
        q: 'Can I request a custom design?',
        a: 'Absolutely! Visit our Custom Order page to submit your design ideas. Our team will work with you to create a one-of-a-kind piece tailored to your preferences.',
    },
];
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background">
            <section class="py-8 sm:py-20 bg-background">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader title="Frequently Asked Questions" subtitle="Find answers to common questions about our products and services." centered />
                    <div v-stagger="{ preset: 'fadeUp', stagger: 80, duration: 600 }" class="mt-8 sm:mt-12 space-y-3 sm:space-y-4">
                        <details v-for="(faq, index) in faqs" :key="index"
                            class="group rounded-xl border border-border bg-card overflow-hidden">
                            <summary
                                class="flex items-center justify-between px-4 py-3.5 sm:px-6 sm:py-5 cursor-pointer text-sm sm:text-base text-foreground font-medium hover:bg-muted/50 transition-colors [&::-webkit-details-marker]:hidden">
                                <span class="flex items-center gap-2.5 sm:gap-3">
                                    <HelpCircle class="w-4 h-4 sm:w-5 sm:h-5 text-amber-500 flex-shrink-0" />
                                    <span>{{ faq.q }}</span>
                                </span>
                                <ChevronDown
                                    class="w-4 h-4 sm:w-5 sm:h-5 text-muted-foreground transition-transform duration-300 group-open:rotate-180 shrink-0 ml-2" />
                            </summary>
                            <div class="px-4 pb-4 sm:px-6 sm:pb-5 text-sm sm:text-base text-muted-foreground leading-relaxed border-t border-border pt-3 sm:pt-4">
                                {{ faq.a }}
                            </div>
                        </details>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
