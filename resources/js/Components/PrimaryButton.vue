<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center rounded-xl border border-transparent bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-700 focus:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-slate-200 dark:text-slate-800 dark:hover:bg-white dark:focus:bg-white',
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
        default: 'submit',
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
