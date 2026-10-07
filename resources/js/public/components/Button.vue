<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'outline'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['md', 'lg'].includes(value),
    },
    className: {
        type: String,
        default: '',
    },
});

const baseClasses = 'public-interactive inline-flex items-center justify-center rounded-lg font-medium transition-luxury disabled:cursor-not-allowed disabled:opacity-70';

const variantClasses = computed(() => {
    return props.variant === 'primary'
        ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold shadow-md hover:shadow-amber-500/20 active:scale-[0.98]'
        : 'border border-border bg-card hover:bg-muted text-foreground';
});

const sizeClasses = computed(() => {
    return props.size === 'lg'
        ? 'px-8 py-4 text-lg'
        : 'px-6 py-3 text-sm';
});
</script>

<template>
    <button
        :class="[baseClasses, variantClasses, sizeClasses, className]"
    >
        <slot />
    </button>
</template>
