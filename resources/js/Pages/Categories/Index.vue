<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    categories: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    {
        accessorKey: 'image_url',
        header: 'Image',
        cell: ({ row }) => row.original.image_url
            ? `<img src="${row.original.image_url}" alt="${row.original.name}" class="h-10 w-10 rounded object-cover" />`
            : 'No image',
    },
    { accessorKey: 'name', header: 'Name' },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => row.original.description || '-',
    },
    { accessorKey: 'products_count', header: 'Products' },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => row.original.is_active
            ? `<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Active</span>`
            : `<span class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600">Inactive</span>`,
    },
    {
        accessorKey: 'actions',
        header: 'Actions',
        cell: ({ row }) =>
            `<a href="/categories/${row.original.id}/edit" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">Edit</a>`,
    },
];
</script>

<template>
    <AuthenticatedLayout header="Categories">
        <Head title="Categories" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Categories"
                :count="categories.total || 0"
                :filters="filters"
                routeName="categories.index"
                addHref="/categories/create"
                addLabel="Add Category"
                searchPlaceholder="Search categories..."
            />

            <AdminDataTable :columns="columns" :data="categories" />
        </div>
    </AuthenticatedLayout>
</template>
