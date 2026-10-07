<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Funnel, Plus, Search } from 'lucide-vue-next';
import FieldInput from '@/Components/ui/field-input.vue';
import FieldSelect from '@/Components/ui/field-select.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent } from '@/Components/ui/dropdown-menu.vue';

const props = defineProps({
    title: { type: String, required: true },
    count: { type: Number, default: 0 },
    routeName: { type: String, required: true },
    filters: { type: Object, default: () => ({}) },
    addHref: { type: String, default: null },
    addLabel: { type: String, default: 'Add Item' },
    searchPlaceholder: { type: String, default: 'Search...' },
    filterTitle: { type: String, default: 'Filter Records' },
    filterDescription: { type: String, default: 'Narrow the list using quick filters.' },
    filterFields: { type: Array, default: () => [] },
});

function buildDraftFilters(fields, filters) {
    return Object.fromEntries(
        fields.map(field => [field.name, filters[field.name] ?? ''])
    );
}

function applyFilters(values) {
    const nextQuery = Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== '')
    );

    router.get(route(props.routeName), nextQuery, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const search = ref(props.filters.search ?? '');
const isFilterOpen = ref(false);
const draftFilters = ref(buildDraftFilters(props.filterFields, props.filters));

const hasActiveFilters = computed(() => {
    return props.filterFields.some(field => (props.filters[field.name] ?? '') !== '');
});

let searchTimeout = null;

watch(search, (newVal) => {
    if (newVal === (props.filters.search ?? '')) return;

    if (searchTimeout) clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        applyFilters({
            ...props.filters,
            search: newVal,
            page: '',
        });
    }, 400);
});

watch(() => props.filters, (newFilters) => {
    search.value = newFilters.search ?? '';
});

watch(() => [props.filterFields, props.filters], () => {
    draftFilters.value = buildDraftFilters(props.filterFields, props.filters);
});

const applyDraftFilters = () => {
    applyFilters({
        ...props.filters,
        search: search.value,
        ...draftFilters.value,
        page: '',
    });
    isFilterOpen.value = false;
};

const clearFilters = () => {
    draftFilters.value = buildDraftFilters(props.filterFields, {});
};
</script>

<template>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    {{ title }}
                </h2>
                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ count }} items
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Search and filter your records from one place.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <FieldInput
                v-model="search"
                :placeholder="searchPlaceholder"
                wrapperClassName="min-w-[260px]"
                inputClassName="py-2.5 pr-4"
            >
                <template #icon>
                    <Search class="h-4 w-4" />
                </template>
            </FieldInput>

            <DropdownMenu v-if="filterFields.length > 0" v-model:open="isFilterOpen">
                <DropdownMenuTrigger asChild>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="hasActiveFilters
                            ? 'border-slate-900 bg-slate-900 text-white dark:border-slate-100 dark:bg-slate-100 dark:text-slate-900'
                            : 'border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-[#10131a] dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-[#171b24]'"
                        aria-label="Toggle filters"
                    >
                        <Funnel class="h-4 w-4" />
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="end"
                    :sideOffset="14"
                    class="w-[min(92vw,760px)] rounded-2xl border border-slate-200 bg-white p-0 shadow-[0_32px_70px_-28px_rgba(15,23,42,0.45)] dark:border-slate-700 dark:bg-[#10131a]"
                    @closeAutoFocus="(event) => event.preventDefault()"
                >
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ filterTitle }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ filterDescription }}
                                </p>
                            </div>
                            <SecondaryButton
                                type="button"
                                @click="clearFilters"
                                class="rounded-xl px-4 py-2 text-sm normal-case tracking-normal"
                            >
                                Clear
                            </SecondaryButton>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-2">
                        <div
                            v-for="field in filterFields"
                            :key="field.name"
                            :class="field.fullWidth ? 'md:col-span-2' : ''"
                        >
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                                {{ field.label }}
                            </label>
                            <FieldSelect
                                v-model="draftFilters[field.name]"
                                :options="[{ value: '', label: 'All' }, ...(field.options || [])]"
                            />
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <PrimaryButton
                                type="button"
                                @click="applyDraftFilters"
                                class="rounded-xl px-4 py-2.5 text-sm normal-case tracking-normal"
                            >
                                Apply Filters
                            </PrimaryButton>
                        </div>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <Link v-if="addHref" :href="addHref">
                <PrimaryButton class="gap-2 rounded-xl px-4 py-2.5 text-sm normal-case tracking-normal">
                    <Plus class="h-4 w-4" />
                    {{ addLabel }}
                </PrimaryButton>
            </Link>
        </div>
    </div>
</template>
