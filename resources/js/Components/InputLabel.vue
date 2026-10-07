<template>
    <label
        :for="forId"
        class="block text-sm font-medium text-slate-700 dark:text-slate-300"
    >
        <span v-if="value">{{ value }}</span>
        <slot v-else />
    </label>
</template>

<script setup>
defineProps({
    forId: {
        type: String,
        default: '',
    },
    value: {
        type: String,
        default: '',
    },
});
</script>
