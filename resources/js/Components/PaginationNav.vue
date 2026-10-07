<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: {
        type: Number,
        default: 1,
    },
    lastPage: {
        type: Number,
        default: 1,
    },
    onPageChange: {
        type: Function,
        default: null,
    },
    variant: {
        type: String,
        default: 'public',
    },
});

const pageItems = computed(() => {
    const current = Math.max(1, props.currentPage || 1);
    const last = Math.max(1, props.lastPage || 1);

    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    if (current <= 4) {
        return [1, 2, 3, 4, 5, 'ellipsis-right', last];
    }

    if (current >= last - 3) {
        return [1, 'ellipsis-left', last - 4, last - 3, last - 2, last - 1, last];
    }

    return [1, 'ellipsis-left', current - 1, current, current + 1, 'ellipsis-right', last];
});

const isPublic = computed(() => props.variant === 'public');

const prevButtonClass = computed(() => isPublic.value
    ? 'inline-flex items-center whitespace-nowrap rounded-xl border border-border bg-card px-3 py-2 text-xs font-medium text-muted-foreground transition-colors hover:border-amber-500 hover:text-foreground disabled:cursor-not-allowed disabled:opacity-40 sm:px-4 sm:py-2.5 sm:text-sm'
    : 'inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-[#10131a] dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-[#171b24]');

const pageButtonBaseClass = computed(() => isPublic.value
    ? 'inline-flex min-w-[38px] items-center justify-center rounded-xl border px-3 py-2 text-xs font-medium transition-colors sm:min-w-[44px] sm:px-4 sm:py-2.5 sm:text-sm'
    : 'inline-flex min-w-[40px] items-center justify-center rounded-xl border px-3 py-2 text-sm font-medium transition');

const inactivePageClass = computed(() => isPublic.value
    ? 'border-border bg-card text-muted-foreground hover:border-amber-500 hover:text-foreground'
    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-900');

const activePageClass = computed(() => isPublic.value
    ? 'border-amber-500 bg-amber-500 text-slate-950 font-bold shadow-sm'
    : 'border-slate-900 bg-slate-900 text-white shadow-sm dark:border-white dark:bg-white dark:text-slate-900');

const ellipsisClass = computed(() => isPublic.value
    ? 'inline-flex min-w-[38px] items-center justify-center px-1.5 text-xs text-muted-foreground sm:min-w-[44px] sm:px-2 sm:text-sm'
    : 'inline-flex min-w-[40px] items-center justify-center px-2 text-sm text-slate-500 dark:text-slate-400');

const goToPage = (page) => {
    if (typeof page !== 'number') {
        return;
    }

    if (page < 1 || page > props.lastPage || page === props.currentPage) {
        return;
    }

    props.onPageChange?.(page);
};
</script>

<template>
    <div v-if="lastPage > 1" class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2">
        <button
            type="button"
            :disabled="currentPage <= 1"
            :class="prevButtonClass"
            @click="goToPage(currentPage - 1)"
        >
            <span class="sm:hidden">Prev</span>
            <span class="hidden sm:inline">Previous</span>
        </button>

        <template v-for="item in pageItems" :key="item">
            <span v-if="typeof item !== 'number'" :class="ellipsisClass">...</span>

            <button
                v-else
                type="button"
                :aria-current="item === currentPage ? 'page' : undefined"
                :class="[pageButtonBaseClass, item === currentPage ? activePageClass : inactivePageClass]"
                @click="goToPage(item)"
            >
                {{ item }}
            </button>
        </template>

        <button
            type="button"
            :disabled="currentPage >= lastPage"
            :class="prevButtonClass"
            @click="goToPage(currentPage + 1)"
        >
            <span class="sm:hidden">Next</span>
            <span class="hidden sm:inline">Next</span>
        </button>
    </div>
</template>
