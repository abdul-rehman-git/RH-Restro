<template>
    <teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="closeable && $emit('close')">
                    <div class="absolute inset-0 bg-slate-950/50 dark:bg-slate-950/80"></div>
                </div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                    :class="maxWidthClass"
                >
                    <slot />

                    <div v-if="closeable" class="absolute right-4 top-4">
                        <button
                            type="button"
                            class="rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                            @click="$emit('close')"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['close']);

const maxWidthClass = {
    'sm': 'sm:max-w-sm',
    'md': 'sm:max-w-md',
    'lg': 'sm:max-w-lg',
    'xl': 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth];

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        e.preventDefault();
        emit('close');
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = null;
    }
});
</script>
