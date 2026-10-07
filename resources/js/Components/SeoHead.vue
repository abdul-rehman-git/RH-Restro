<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    seo: {
        type: Object,
        default: null,
    },
});

const scriptElements = [];

function injectJsonLd() {
    cleanupJsonLd();

    if (!props.seo?.jsonLd?.length) return;

    for (const schema of props.seo.jsonLd) {
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(schema);
        document.head.appendChild(script);
        scriptElements.push(script);
    }
}

function cleanupJsonLd() {
    while (scriptElements.length) {
        const el = scriptElements.pop();
        el.parentNode?.removeChild(el);
    }
}

onMounted(injectJsonLd);

watch(() => props.seo?.jsonLd, injectJsonLd, { deep: true });

onUnmounted(cleanupJsonLd);
</script>

<template>
    <Head v-if="seo">
        <!-- Title -->
        <title>{{ seo.title }}</title>

        <!-- Meta description -->
        <meta v-if="seo.description" name="description" :content="seo.description" />

        <!-- Robots -->
        <meta v-if="seo.robots" name="robots" :content="seo.robots" />

        <!-- Canonical URL -->
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />

        <!-- Open Graph -->
        <template v-if="seo.og">
            <meta v-if="seo.og.title" property="og:title" :content="seo.og.title" />
            <meta v-if="seo.og.description" property="og:description" :content="seo.og.description" />
            <meta v-if="seo.og.image" property="og:image" :content="seo.og.image" />
            <meta v-if="seo.og.url" property="og:url" :content="seo.og.url" />
            <meta v-if="seo.og.type" property="og:type" :content="seo.og.type" />
            <meta v-if="seo.og.site_name" property="og:site_name" :content="seo.og.site_name" />
        </template>

        <!-- Twitter Card -->
        <template v-if="seo.twitter">
            <meta v-if="seo.twitter.card" name="twitter:card" :content="seo.twitter.card" />
            <meta v-if="seo.twitter.title" name="twitter:title" :content="seo.twitter.title" />
            <meta v-if="seo.twitter.description" name="twitter:description" :content="seo.twitter.description" />
            <meta v-if="seo.twitter.image" name="twitter:image" :content="seo.twitter.image" />
        </template>
    </Head>
</template>
