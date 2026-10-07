<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: '',
    },
});

const open = ref(false);
const root = ref(null);

const widthClass = computed(() => ({
    '48': 'w-48',
}[props.width] ?? 'w-48'));

const toggle = () => {
    open.value = !open.value;
};

const close = () => {
    open.value = false;
};

const handleClickOutside = (event) => {
    if (!root.value?.contains(event.target)) {
        close();
    }
};

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleEscape);
});
</script>

<template>
    <div ref="root" class="relative">
        <div class="cursor-pointer" @click.stop="toggle">
            <slot name="trigger" />
        </div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute right-0 z-50 mt-2 origin-top-right rounded-xl"
                :class="[widthClass, contentClasses]"
                @click.stop
            >
                <slot name="content" :close="close" />
            </div>
        </transition>
    </div>
</template>
