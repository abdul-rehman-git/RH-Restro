<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    reviews: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    { accessorKey: 'reviewer_name', header: 'Reviewer' },
    { accessorKey: 'rating', header: 'Rating' },
    { accessorKey: 'comment', header: 'Comment' },
    {
        accessorKey: 'product',
        header: 'Product',
        cell: ({ row }) => row.original.product?.title ?? 'General Review',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => row.original.is_approved
            ? `<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Approved</span>`
            : `<span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending</span>`,
    },
    {
        accessorKey: 'actions',
        header: 'Actions',
        cell: ({ row }) =>
            `<a href="/admin-reviews/${row.original.id}" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">View</a>`,
    },
];
</script>

<template>
    <AuthenticatedLayout header="Reviews">
        <Head title="Reviews" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Reviews"
                :count="reviews.total || 0"
                :filters="filters"
                routeName="admin-reviews.index"
            />

            <AdminDataTable :columns="columns" :data="reviews" />
        </div>
    </AuthenticatedLayout>
</template>
