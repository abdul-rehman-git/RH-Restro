<script setup>
import { computed, inject, watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';

const actualTheme = inject('actualTheme');
const page = usePage();

const lastToast = ref(null);
const currentFlash = computed(() => page.props.flash || {});

watch(
    () => currentFlash.value,
    (newFlash) => {
        if (!newFlash?.message) return;

        const toastKey = `${newFlash.type ?? 'success'}:${newFlash.message}`;

        if (lastToast.value === toastKey) return;

        lastToast.value = toastKey;

        const showToast = toast[newFlash.type] ?? toast.success;
        showToast(newFlash.message);
    },
    { immediate: true }
);
</script>

<template>
    <Toaster
        richColors
        closeButton
        position="top-right"
        :theme="actualTheme || 'light'"
        :toastOptions="{
            className: 'font-medium',
        }"
    />
</template>
