<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { ChevronLeft, ChevronRight, X } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    images: { type: Array, default: () => [] },
    initialIndex: { type: Number, default: 0 },
    title: { type: String, default: 'Review images' },
});

const emit = defineEmits(['update:open']);

const currentIndex = ref(0);
const touchStartX = ref(0);
const touchEndX = ref(0);

const currentImage = computed(() => props.images[currentIndex.value] || null);
const hasMultipleImages = computed(() => (props.images?.length || 0) > 1);

const close = () => emit('update:open', false);

const goTo = (index) => {
    if (!props.images.length) {
        return;
    }

    const total = props.images.length;
    currentIndex.value = ((index % total) + total) % total;
};

const showPrevious = () => goTo(currentIndex.value - 1);
const showNext = () => goTo(currentIndex.value + 1);

const handleKeydown = (event) => {
    if (!props.open) {
        return;
    }

    if (event.key === 'Escape') {
        close();
    }

    if (event.key === 'ArrowLeft') {
        showPrevious();
    }

    if (event.key === 'ArrowRight') {
        showNext();
    }
};

const handleTouchStart = (event) => {
    touchStartX.value = event.changedTouches[0]?.clientX || 0;
};

const handleTouchEnd = (event) => {
    touchEndX.value = event.changedTouches[0]?.clientX || 0;

    const delta = touchEndX.value - touchStartX.value;

    if (Math.abs(delta) < 40) {
        return;
    }

    if (delta > 0) {
        showPrevious();

        return;
    }

    showNext();
};

watch(
    () => [props.open, props.initialIndex, props.images.length],
    ([open]) => {
        if (!open) {
            return;
        }

        goTo(props.initialIndex || 0);
    },
    { immediate: true },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            window.addEventListener('keydown', handleKeydown);

            return;
        }

        window.removeEventListener('keydown', handleKeydown);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="custom-fade-in motion-modal-enter"
            leave-active-class="custom-fade-out motion-modal-leave"
        >
            <div
                v-if="open && currentImage"
                class="fixed inset-0 z-[120] flex items-center justify-center bg-black/90 px-4 py-6 backdrop-blur-sm"
                @click.self="close"
            >
                <div class="custom-zoom-in motion-modal-enter relative w-full max-w-6xl">
                    <button
                        type="button"
                        class="public-interactive absolute right-3 top-3 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-black/55 text-white hover:border-amber-500/60 hover:text-amber-500"
                        @click="close"
                    >
                        <X class="h-5 w-5" />
                    </button>

                    <div class="overflow-hidden rounded-[28px] border border-white/10 bg-[#111111] shadow-2xl">
                        <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">Review Gallery</p>
                                <h3 class="mt-1 text-sm font-semibold text-white">{{ title }}</h3>
                            </div>
                            <p class="text-sm text-white/70">{{ currentIndex + 1 }} / {{ images.length }}</p>
                        </div>

                        <div class="relative" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
                            <div class="flex min-h-[55vh] items-center justify-center bg-black px-4 py-6">
                                <img
                                    :src="currentImage"
                                    :alt="`${title} image ${currentIndex + 1}`"
                                    class="max-h-[72vh] w-auto max-w-full rounded-2xl object-contain"
                                />
                            </div>

                            <button
                                v-if="hasMultipleImages"
                                type="button"
                                class="absolute left-4 top-1/2 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/55 text-white transition-colors hover:border-amber-500/60 hover:text-amber-500"
                                @click="showPrevious"
                            >
                                <ChevronLeft class="h-5 w-5" />
                            </button>

                            <button
                                v-if="hasMultipleImages"
                                type="button"
                                class="absolute right-4 top-1/2 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/15 bg-black/55 text-white transition-colors hover:border-amber-500/60 hover:text-amber-500"
                                @click="showNext"
                            >
                                <ChevronRight class="h-5 w-5" />
                            </button>
                        </div>

                        <div v-if="hasMultipleImages" class="border-t border-white/10 px-5 py-4">
                            <div class="flex gap-3 overflow-x-auto pb-1">
                                <button
                                    v-for="(image, index) in images"
                                    :key="`${image}-${index}`"
                                    type="button"
                                    class="public-interactive h-16 w-16 flex-shrink-0 overflow-hidden rounded-xl border transition-all"
                                    :class="currentIndex === index ? 'border-amber-500 ring-2 ring-amber-500/30' : 'border-white/10 opacity-70 hover:opacity-100'"
                                    @click="goTo(index)"
                                >
                                    <img :src="image" :alt="`${title} thumbnail ${index + 1}`" class="h-full w-full object-cover" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
