<script setup>
defineProps({
    columns: { type: Array, required: true },
    data: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

// Detects ISO date strings and formats them as human-readable dates.
const ISO_RE = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/;

function formatValue(value) {
    if (typeof value === 'string' && ISO_RE.test(value)) {
        return new Date(value).toLocaleString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit',
        });
    }
    return value ?? '—';
}
</script>

<template>
    <div class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_20px_60px_-30px_rgba(15,23,42,0.25)] dark:border-slate-800 dark:bg-slate-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50/90 dark:bg-slate-900">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.accessorKey || col.id"
                            class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400"
                        >
                            {{ col.header }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <tr
                        v-for="(item, index) in data.data"
                        :key="item.id || index"
                        class="transition hover:bg-slate-50/80 dark:hover:bg-slate-900"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.accessorKey || col.id"
                            class="px-6 py-4 align-middle text-sm text-slate-700 dark:text-slate-200"
                            v-html="col.cell ? col.cell({ row: { original: item } }) : formatValue(item[col.accessorKey])"
                        />
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            v-if="data.links && data.links.length > 3"
            class="border-t border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40"
        >
            <div class="flex flex-wrap items-center gap-2">
                <button
                    v-for="link in data.links"
                    :key="`${link.label}-${link.url ?? 'disabled'}`"
                    type="button"
                    :disabled="!link.url"
                    class="min-w-[40px] rounded-xl px-3 py-2 text-sm font-medium transition"
                    :class="link.active
                        ? 'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900'
                        : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-900'"
                    v-html="link.label"
                    @click="link.url && $inertia.visit(link.url)"
                />
            </div>
        </div>
    </div>
</template>
