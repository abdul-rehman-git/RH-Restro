<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { MessageCircle, ArrowRight, CheckCircle2, Clock3, UtensilsCrossed, Sparkles, ShieldCheck, PhoneCall } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();

const whatsappMessage = computed(() => encodeURIComponent(
    'Hello, I would like to inquire about reserving a table or booking private catering/event dining at RH Restro. Please guide me regarding menu packages, availability, and seating.'
));
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background py-8 sm:py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <SectionHeader title="Table Reservations & Event Catering"
                    subtitle="Connect with our dining manager directly on WhatsApp for VIP table bookings, birthday celebrations, corporate lunches, or private catering."
                    centered />

                <section v-reveal="{ preset: 'zoom', duration: 760 }" class="mb-8 overflow-hidden rounded-2xl border border-border bg-card shadow-luxury">
                    <div class="mb-12 grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:gap-0">
                        <div v-stagger="{ preset: 'fadeUp', stagger: 110, duration: 680 }" class="relative overflow-hidden p-8 sm:p-10 lg:p-12">
                            <div
                                class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.18),transparent_40%),linear-gradient(135deg,rgba(245,158,11,0.08),transparent_55%)]" />
                            <div class="relative">
                                <div
                                    class="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-amber-500">
                                    <Sparkles class="h-4 w-4" />
                                    Bespoke Dining & Event Catering
                                </div>

                                <h1
                                    class="mb-6 max-w-2xl text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                                    Planning a special dinner, birthday party, or event catering? Let's connect.
                                </h1>

                                <p class="max-w-2xl text-base leading-7 text-muted-foreground sm:text-lg">
                                    Tell us your guest count, preferred date and time, dietary preferences, or event theme. Our dining manager will coordinate your table reservation or design a customized chef menu package for you.
                                </p>

                                <div class="mb-8 mt-8 flex flex-col gap-4 sm:flex-row">
                                    <a :href="$page.props.site?.whatsapp_url ? `${$page.props.site.whatsapp_url}?text=${whatsappMessage}` : '/contact'"
                                        target="_blank" rel="noreferrer"
                                        class="group public-interactive inline-flex items-center justify-center gap-3 rounded-2xl bg-[#25D366] px-6 py-4 text-base font-semibold text-white shadow-[0_18px_40px_-18px_rgba(37,211,102,0.75)] transition hover:bg-[#1ebe5b]">
                                        <MessageCircle class="h-5 w-5" />
                                        <span>Reserve / Inquire on WhatsApp</span>
                                        <ArrowRight class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" />
                                    </a>

                                    <a v-if="$page.props.site?.phone" :href="`tel:${$page.props.site.phone}`"
                                        class="public-interactive inline-flex items-center justify-center gap-3 rounded-2xl border border-border bg-background px-6 py-4 text-base font-semibold text-foreground transition hover:bg-muted">
                                        <PhoneCall class="h-5 w-5 text-amber-500" />
                                        <span>{{ $page.props.site.phone }}</span>
                                    </a>
                                </div>

                                <div v-stagger="{ preset: 'fadeUp', stagger: 90, delay: 120 }" class="mt-10 grid gap-4 sm:grid-cols-3">
                                    <div class="rounded-2xl border border-border bg-background/80 p-5">
                                        <UtensilsCrossed class="mb-3 h-6 w-6 text-amber-500" />
                                        <h3 class="font-semibold text-foreground">Custom Menus</h3>
                                        <p class="mt-2 text-sm leading-6 text-muted-foreground">Charbroiled steaks, artisanal pizzas, live BBQ grills, and signature mocktails.</p>
                                    </div>
                                    <div class="rounded-2xl border border-border bg-background/80 p-5">
                                        <Clock3 class="mb-3 h-6 w-6 text-amber-500" />
                                        <h3 class="font-semibold text-foreground">Prompt Coordination</h3>
                                        <p class="mt-2 text-sm leading-6 text-muted-foreground">Instant seat availability confirmation and rapid menu proposal turnaround.</p>
                                    </div>
                                    <div class="rounded-2xl border border-border bg-background/80 p-5">
                                        <ShieldCheck class="mb-3 h-6 w-6 text-amber-500" />
                                        <h3 class="font-semibold text-foreground">Dedicated Manager</h3>
                                        <p class="mt-2 text-sm leading-6 text-muted-foreground">A personal hospitality coordinator to oversee every detail of your gathering.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-reveal="{ preset: 'fadeLeft', duration: 680, delay: 100 }" class="border-t border-border bg-muted/30 p-8 sm:p-10 lg:border-l lg:border-t-0">
                            <div class="rounded-[28px] border border-border bg-card p-6 shadow-sm">
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-500">
                                    What to Share
                                </p>
                                <h2 class="mt-3 text-2xl font-semibold text-foreground">
                                    Make your reservation quick & seamless
                                </h2>

                                <div class="mt-6 space-y-4">
                                    <div class="flex items-start gap-3">
                                        <CheckCircle2 class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#25D366]" />
                                        <div>
                                            <p class="font-medium text-foreground">Occasion & Gathering Type</p>
                                            <p class="text-sm text-muted-foreground">Romantic dinner, family get-together, birthday party, or corporate event.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <CheckCircle2 class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#25D366]" />
                                        <div>
                                            <p class="font-medium text-foreground">Guest Count & Preferred Time</p>
                                            <p class="text-sm text-muted-foreground">Number of guests attending and your preferred date and seating time.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <CheckCircle2 class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#25D366]" />
                                        <div>
                                            <p class="font-medium text-foreground">Menu Preferences</p>
                                            <p class="text-sm text-muted-foreground">Preferred dishes (steaks, burgers, pasta, BBQ, desserts) or dietary needs.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <CheckCircle2 class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#25D366]" />
                                        <div>
                                            <p class="font-medium text-foreground">Special Decor & Customizations</p>
                                            <p class="text-sm text-muted-foreground">Candlelight arrangements, birthday cakes, customized dessert lettering, or floral setups.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="mt-10 grid gap-6 md:grid-cols-3">
                    <div class="public-card rounded-2xl border border-border bg-card p-6 shadow-sm">
                        <div
                            class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
                            <span class="text-lg font-bold">1</span>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Send Your Reservation Details</h3>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Send us a message on WhatsApp with your party size, date, time, and special dining requests.
                        </p>
                    </div>

                    <div class="public-card rounded-2xl border border-border bg-card p-6 shadow-sm">
                        <div
                            class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
                            <span class="text-lg font-bold">2</span>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Receive Table & Menu Confirmation</h3>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Our team confirms your prime table reservation or shares custom multi-course catering options.
                        </p>
                    </div>

                    <div class="public-card rounded-2xl border border-border bg-card p-6 shadow-sm">
                        <div
                            class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
                            <span class="text-lg font-bold">3</span>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Savor Exquisite Dining</h3>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Arrive to a welcoming ambiance, personalized table service, and fresh gourmet dishes cooked to perfection.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
