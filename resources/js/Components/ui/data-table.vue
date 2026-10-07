<script setup>
import { ref, computed, useSlots } from 'vue';
import {
    useVueTable,
    getCoreRowModel,
    getSortedRowModel,
    FlexRender,
} from '@tanstack/vue-table';
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-vue-next';
import PaginationNav from '@/Components/PaginationNav.vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    emptyTitle: {
        type: String,
        default: 'No records found',
    },
    emptyDescription: {
        type: String,
        default: 'There is no data to display right now.',
    },
    summaryLabel: {
        type: String,
        default: 'records',
    },
    pagination: {
        type: Object,
        default: null,
    },
    onPageChange: {
        type: Function,
        default: null,
    },
    topContent: {
        type: [String, Object],
        default: null,
    },
    filtersPanel: {
        type: [String, Object],
        default: null,
    },
});

const sorting = ref([]);

const table = useVueTable({
    get data() { return props.data; },
    get columns() { return props.columns; },
    state: {
        get sorting() { return sorting.value; },
    },
    onSortingChange: (updater) => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
});

const rows = computed(() => table.getRowModel().rows);
const totalCount = computed(() => props.pagination?.total ?? rows.value.length);
const start = computed(() => props.pagination?.from ?? (rows.value.length ? 1 : 0));
const end = computed(() => props.pagination?.to ?? rows.value.length);
const currentPage = computed(() => props.pagination?.current_page ?? 1);
const lastPage = computed(() => props.pagination?.last_page ?? 1);
</script>

<template>
    <div class="relative overflow-visible rounded-[28px] border border-slate-200/80 bg-white shadow-[0_24px_70px_-36px_rgba(15,23,42,0.35)] dark:border-slate-800 dark:bg-[#10131a]">
        <div v-if="topContent" class="border-b border-slate-200/90 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-[#11151d]">
            {{ topContent }}
        </div>
        <div v-else class="flex items-center justify-end border-b border-slate-200/90 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-[#11151d]">
            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ totalCount }} {{ summaryLabel }}
            </div>
        </div>

        <div v-if="filtersPanel" class="border-b border-slate-200/90 bg-white px-6 py-5 dark:border-slate-800 dark:bg-[#10131a]">
            {{ filtersPanel }}
        </div>

        <div class="overflow-x-auto overflow-y-visible">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50/90 dark:bg-[#151923]">
                    <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <th
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400"
                        >
                            <template v-if="header.isPlaceholder">
                                <span />
                            </template>
                            <template v-else-if="header.column.getCanSort()">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 transition hover:text-slate-700 dark:hover:text-slate-200"
                                    @click="header.column.getToggleSortingHandler()?.($event)"
                                >
                                    <FlexRender
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                    <component
                                        :is="header.column.getIsSorted() === 'asc' ? ChevronUp : header.column.getIsSorted() === 'desc' ? ChevronDown : ChevronsUpDown"
                                        class="h-4 w-4"
                                    />
                                </button>
                            </template>
                            <template v-else>
                                <FlexRender
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </template>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <template v-if="rows.length">
                        <tr
                            v-for="row in rows"
                            :key="row.id"
                            class="transition hover:bg-slate-50/80 dark:hover:bg-[#151923]"
                        >
                            <td
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="px-6 py-5 align-middle text-slate-700 dark:text-slate-200"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td :colspan="columns.length" class="px-6 py-16 text-center">
                            <div class="mx-auto max-w-sm">
                                <p class="text-base font-medium text-slate-900 dark:text-slate-100">
                                    {{ emptyTitle }}
                                </p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    {{ emptyDescription }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 border-t border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-[#141923] sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-slate-500 dark:text-slate-400">
                Showing {{ start }} to {{ end }} of {{ totalCount }} {{ summaryLabel }}
            </div>

            <div class="max-w-full overflow-x-auto">
                <PaginationNav
                    :current-page="currentPage"
                    :last-page="lastPage"
                    :on-page-change="onPageChange"
                    variant="admin"
                />
            </div>
        </div>
    </div>
</template>
