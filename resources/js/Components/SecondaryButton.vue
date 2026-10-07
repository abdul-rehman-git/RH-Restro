<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:focus:bg-slate-800',
            className,
        ]"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    className: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>
