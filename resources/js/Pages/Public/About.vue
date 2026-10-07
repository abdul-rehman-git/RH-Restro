<script setup>
import { computed, inject } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Award, Heart, Palette, Users } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();
const about = page.props.about || {};
const actualTheme = inject('actualTheme', 'light');
const statIcons = [Palette, Users, Award, Heart];
const fallbackImage = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800';

const stats = computed(() => about.stats || []);
const storyParagraphs = computed(() =>
    (about.story_content || '')
        .split('\n\n')
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background">
            <section :class="`${actualTheme === 'dark' ? 'hero-marble-dark' : 'hero-marble-light'} py-10 sm:py-20`">
                <div v-stagger="{ preset: 'fadeUp', stagger: 120, duration: 700 }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <span
                        class="inline-block px-4 py-2 bg-amber-500/10 border border-amber-500/30 rounded-full text-sm font-bold text-amber-500 mb-4"
                    >
                        Our Story
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold text-foreground mb-4 sm:mb-6">
                        {{ about.hero_title?.split(' ').slice(0, -1).join(' ') || 'Culinary Passion &' }}
                        <span class="block text-amber-gradient">
                            {{ about.hero_title?.split(' ').slice(-1).join(' ') || 'Heritage' }}
                        </span>
                    </h1>
                    <p class="text-lg text-muted-foreground max-w-3xl mx-auto">{{ about.hero_subtitle }}</p>
                </div>
            </section>

            <section class="py-8 sm:py-16 bg-background">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-8">
                        <div
                            v-for="(stat, index) in stats"
                            :key="`${stat.label}-${stat.value}`"
                            class="text-center"
                        >
                            <div class="public-icon-float mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10">
                                <component :is="statIcons[index % statIcons.length]" class="w-8 h-8 text-amber-500" />
                            </div>
                            <div v-count-up="stat.value" class="mb-2 text-3xl font-bold text-foreground">{{ stat.value }}</div>
                            <div class="text-muted-foreground">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-8 sm:py-20 bg-background">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-2 gap-8 sm:gap-12 items-center">
                        <div v-reveal="{ preset: 'fadeRight', duration: 700 }" class="order-2 lg:order-1">
                            <SectionHeader :title="about.story_title || 'Our Kitchen & Heritage'" />
                            <div class="space-y-4 text-muted-foreground">
                                <p v-for="(paragraph, index) in storyParagraphs" :key="index">{{ paragraph }}</p>
                                <p v-if="about.artist_quote">
                                    {{ about.artist_quote }}
                                    <span class="block mt-2 text-amber-500 font-bold">- {{ about.artist_name || 'Chef Marco & Culinary Team' }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-reveal="{ preset: 'zoom', duration: 760, delay: 120 }" class="order-1 lg:order-2">
                            <div class="relative overflow-hidden rounded-2xl shadow-luxury-hover">
                                <img
                                    :src="about.artist_image_url || fallbackImage"
                                    :alt="about.artist_name || 'RH Studio'"
                                    class="h-[500px] w-full object-cover transition-luxury duration-700 hover:scale-[1.02]"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section :class="`py-8 sm:py-20 ${actualTheme === 'dark' ? 'marble-dark' : 'marble-light'}`">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader
                        title="From Kitchen to Table"
                        subtitle="How each dish is thoughtfully sourced, flame-cooked, and plated with artistry."
                        centered
                    />
                    <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 650 }" class="grid md:grid-cols-3 gap-4 sm:gap-8">
                        <div
                            v-for="(step, index) in about.process_steps || []"
                            :key="`${step.title}-${index}`"
                            class="public-card rounded-xl border border-border bg-card p-4 sm:p-6 shadow-luxury"
                        >
                            <div class="w-12 h-12 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xl mb-4 shadow-sm">
                                {{ index + 1 }}
                            </div>
                            <h3 class="text-xl font-semibold text-foreground mb-3">{{ step.title }}</h3>
                            <p class="text-muted-foreground">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-8 sm:py-20 bg-background">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <SectionHeader
                        title="Our Values"
                        subtitle="The principles that guide everything we create"
                        centered
                    />
                    <div v-stagger="{ preset: 'fadeUp', stagger: 90, duration: 620 }" class="space-y-4 sm:space-y-6">
                        <div
                            v-for="(value, index) in about.values || []"
                            :key="`${value.title}-${index}`"
                            class="rounded-xl border border-border bg-card p-4 sm:p-6 flex items-start space-x-3 sm:space-x-4"
                        >
                            <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                                <span class="text-lg font-bold text-amber-500">{{ index + 1 }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-foreground mb-2">{{ value.title }}</h3>
                                <p class="text-muted-foreground">{{ value.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
