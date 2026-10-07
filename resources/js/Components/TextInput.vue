<template>
    <input
        :id="id"
        :type="type"
        :value="modelValue"
        :class="[
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-800',
            className,
        ]"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    id: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    className: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

const input = ref(null);

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>
