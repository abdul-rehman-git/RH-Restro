<template>
    <div class="mb-4">
        <label
            v-if="label"
            :for="name"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
        >
            {{ label }}
        </label>
        <select
            :id="name"
            :value="modelValue"
            :class="[
                'w-full rounded-xl border px-4 py-3 text-sm shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                error
                    ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200 dark:border-red-600 dark:focus:ring-red-700'
                    : 'border-slate-300 text-slate-900 focus:border-slate-500 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-800',
            ]"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled class="dark:bg-slate-900 dark:text-slate-400">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                class="dark:bg-slate-900 dark:text-slate-100"
            >
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    name: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>
