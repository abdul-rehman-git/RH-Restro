<script setup>
import { ref, provide, watch, onMounted, onUnmounted } from 'vue';

const theme = ref('light');
const actualTheme = ref('light');
let mediaQuery = null;

const setTheme = (newTheme) => {
    theme.value = newTheme;
};

const applyTheme = (nextTheme) => {
    const root = document.documentElement;

    const resolvedTheme =
        nextTheme === 'system'
            ? mediaQuery?.matches
                ? 'dark'
                : 'light'
            : nextTheme;

    root.classList.remove('light', 'dark');
    root.classList.add(resolvedTheme);
    root.style.colorScheme = resolvedTheme;
    localStorage.setItem('rh-commerce-theme', nextTheme);
    actualTheme.value = resolvedTheme;
};

const handleSystemThemeChange = () => {
    if (theme.value === 'system') {
        applyTheme('system');
    }
};

watch(theme, (newTheme) => {
    applyTheme(newTheme);
});

onMounted(() => {
    if (typeof window !== 'undefined') {
        mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener?.('change', handleSystemThemeChange);
        const stored = localStorage.getItem('rh-commerce-theme') || 'light';
        theme.value = stored;
        applyTheme(stored);
    }
});

onUnmounted(() => {
    mediaQuery?.removeEventListener?.('change', handleSystemThemeChange);
});

provide('theme', theme);
provide('setTheme', setTheme);
provide('actualTheme', actualTheme);
</script>

<template>
    <slot />
</template>
