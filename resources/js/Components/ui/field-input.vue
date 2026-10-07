<template>
    <div :class="['mb-4', wrapperClassName]">
        <label
            v-if="label"
            :for="name"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
        >
            {{ label }}
        </label>
        <div class="relative">
            <div
                v-if="$slots.icon"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"
            >
                <slot name="icon" />
            </div>
            <input
                :id="name"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :class="[
                    'w-full rounded-xl border px-4 py-3 text-sm shadow-sm transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-1',
                    $slots.icon ? 'pl-10' : '',
                    error
                        ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200 dark:border-red-600 dark:focus:ring-red-700'
                        : 'border-slate-300 text-slate-900 focus:border-slate-500 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-slate-500 dark:focus:ring-slate-800',
                    inputClassName,
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>
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
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    wrapperClassName: {
        type: String,
        default: '',
    },
    inputClassName: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>
