<template>
    <input
        :id="id"
        type="checkbox"
        :checked="modelValue"
        :class="[
            'h-4 w-4 rounded border-slate-300 text-slate-600 shadow-sm focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:focus:ring-slate-600 dark:focus:ring-offset-slate-800',
            className,
        ]"
        @change="$emit('update:modelValue', $event.target.checked)"
        ref="checkbox"
    />
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    id: {
        type: String,
        default: '',
    },
    modelValue: {
        type: Boolean,
        default: false,
    },
    className: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);

const checkbox = ref(null);

defineExpose({ checkbox });
</script>
