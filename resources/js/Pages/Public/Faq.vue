<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { HelpCircle, ChevronDown } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();

const defaultFaqs = [
    {
        q: 'What dining and payment options do you accept?',
        a: 'We welcome dine-in, takeaway, and online delivery orders. We accept all major credit/debit cards (Visa, MasterCard, Amex), PayPal, cash, and secure online payment gateways.',
    },
    {
        q: 'How does hot express food delivery work?',
        a: 'All delivery orders are prepared hot and packed in temperature-sealed, heat-locking containers. Delivery typically arrives within 30-45 minutes depending on destination.',
    },
    {
        q: 'Are all meats and ingredients halal certified?',
        a: 'Yes, 100% of our meat cuts, chicken, and ingredients are certified Halal and sourced fresh daily from verified organic suppliers.',
    },
    {
        q: 'How can I reserve a table for lunch or dinner?',
        a: 'You can easily reserve a table online via our Reservations page, call our host desk directly, or message us on WhatsApp for instant confirmation.',
    },
    {
        q: 'Do you provide private catering and event hosting?',
        a: 'Yes! We cater corporate lunches, birthday parties, weddings, and private dinners with customized chef menus, live stations, and full dining setup.',
    },
    {
        q: 'Can I track my online order in real time?',
        a: 'Yes, once you place an order, you will receive an order number to track kitchen preparation, dispatch, and delivery status on our Order Tracking page.',
    },
    {
        q: 'What is your cancellation and refund policy?',
        a: 'Orders can be modified or cancelled before kitchen preparation begins. If you experience any quality issue, our manager will issue a prompt replacement or full refund.',
    },
    {
        q: 'Do you accommodate allergies and dietary restrictions?',
        a: 'Our master chefs gladly customize dishes for gluten-free, vegetarian, nut-free, or dairy-free preferences. Please add a note to your order or inform your server.',
    },
];

const faqs = computed(() => {
    const passed = page.props.faqs;
    if (Array.isArray(passed) && passed.length > 0) {
        return passed.map((f) => ({
            q: f.question || f.q || f.title || '',
            a: f.answer || f.a || f.description || '',
        }));
    }
    return defaultFaqs;
});
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background">
            <section class="py-8 sm:py-20 bg-background">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader tag="h1" title="Frequently Asked Questions" subtitle="Find answers to common questions about dining, ordering, reservations, and catering." centered />
                    <div v-stagger="{ preset: 'fadeUp', stagger: 80, duration: 600 }" class="mt-8 sm:mt-12 space-y-3 sm:space-y-4">
                        <details v-for="(faq, index) in faqs" :key="index"
                            class="group rounded-xl border border-border bg-card overflow-hidden">
                            <summary
                                class="flex items-center justify-between px-4 sm:px-6 py-3.5 sm:py-5 cursor-pointer text-sm sm:text-base text-foreground font-medium hover:bg-muted/50 transition-colors [&::-webkit-details-marker]:hidden">
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
