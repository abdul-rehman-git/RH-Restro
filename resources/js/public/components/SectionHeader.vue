<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    centered: { type: Boolean, default: false },
});

const titleWords = computed(() => props.title.split(/\s+/).filter(Boolean));
</script>

<template>
    <div
        v-reveal="{ preset: 'fadeUp', duration: 650 }"
        :class="`mb-10 sm:mb-12 ${props.centered ? 'text-center' : ''}`"
    >
        <h2 class="mb-3 text-3xl font-bold leading-tight text-foreground sm:mb-4 sm:text-4xl">
            <span
                :class="[
                    'inline-flex flex-wrap items-baseline gap-x-[0.24em] gap-y-1',
                    props.centered ? 'justify-center' : 'justify-start',
                ]"
            >
                <template v-for="(word, i) in titleWords" :key="`${word}-${i}`">
                    <span :class="{ 'text-gold-gradient': i === titleWords.length - 1 }">{{ word }}</span>
                </template>
            </span>
        </h2>
        <p
            v-if="props.subtitle"
            class="max-w-2xl text-muted-foreground leading-7 sm:text-lg"
            :class="{ 'mx-auto': props.centered }"
        >
            {{ props.subtitle }}
        </p>
    </div>
</template>
