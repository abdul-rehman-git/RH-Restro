import { Head } from '@inertiajs/vue3';
import { h } from 'vue';

export function useSeo(seo) {
    if (!seo) {
        return;
    }

    const nodes = [];

    // Title
    if (seo.title) {
        nodes.push(h('title', {}, seo.title));
    }

    // Meta description
    if (seo.description) {
        nodes.push(h('meta', { name: 'description', content: seo.description, 'head-key': 'description' }));
    }

    // Keywords
    if (seo.keywords) {
        nodes.push(h('meta', { name: 'keywords', content: seo.keywords, 'head-key': 'keywords' }));
    }

    // Robots
    if (seo.robots) {
        nodes.push(h('meta', { name: 'robots', content: seo.robots, 'head-key': 'robots' }));
    }

    // Canonical URL
    if (seo.canonical) {
        nodes.push(h('link', { rel: 'canonical', href: seo.canonical, 'head-key': 'canonical' }));
    }

    // Open Graph tags
    if (seo.og) {
        if (seo.og.title) {
            nodes.push(h('meta', { property: 'og:title', content: seo.og.title, 'head-key': 'og:title' }));
        }
        if (seo.og.description) {
            nodes.push(h('meta', { property: 'og:description', content: seo.og.description, 'head-key': 'og:description' }));
        }
        if (seo.og.image) {
            nodes.push(h('meta', { property: 'og:image', content: seo.og.image, 'head-key': 'og:image' }));
        }
        if (seo.og.url) {
            nodes.push(h('meta', { property: 'og:url', content: seo.og.url, 'head-key': 'og:url' }));
        }
        if (seo.og.type) {
            nodes.push(h('meta', { property: 'og:type', content: seo.og.type, 'head-key': 'og:type' }));
        }
        if (seo.og.site_name) {
            nodes.push(h('meta', { property: 'og:site_name', content: seo.og.site_name, 'head-key': 'og:site_name' }));
        }
        nodes.push(h('meta', { property: 'og:locale', content: 'en_US', 'head-key': 'og:locale' }));
    }

    // Twitter Card tags
    if (seo.twitter) {
        if (seo.twitter.card) {
            nodes.push(h('meta', { name: 'twitter:card', content: seo.twitter.card, 'head-key': 'twitter:card' }));
        }
        if (seo.twitter.title) {
            nodes.push(h('meta', { name: 'twitter:title', content: seo.twitter.title, 'head-key': 'twitter:title' }));
        }
        if (seo.twitter.description) {
            nodes.push(h('meta', { name: 'twitter:description', content: seo.twitter.description, 'head-key': 'twitter:description' }));
        }
        if (seo.twitter.image) {
            nodes.push(h('meta', { name: 'twitter:image', content: seo.twitter.image, 'head-key': 'twitter:image' }));
        }
    }

    // JSON-LD structured data
    if (seo.jsonLd && seo.jsonLd.length) {
        seo.jsonLd.forEach((schema) => {
            nodes.push(
                h('script', {
                    type: 'application/ld+json',
                    innerHTML: JSON.stringify(schema),
                }),
            );
        });
    }

    return h(Head, {}, () => nodes);
}
