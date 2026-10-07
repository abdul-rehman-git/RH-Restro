<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    products: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    {
        accessorKey: 'image',
        header: 'Image',
        cell: ({ row }) =>
            row.original.image_url
                ? `<img src="${row.original.image_url}" class="h-10 w-10 rounded object-cover" />`
                : 'No image',
    },
    { accessorKey: 'title', header: 'Name' },
    {
        accessorKey: 'category',
        header: 'Category',
        cell: ({ row }) => row.original.category?.name ?? '-',
    },
    { accessorKey: 'price', header: 'Price' },
    { accessorKey: 'stock_quantity', header: 'Stock' },
    {
        accessorKey: 'actions',
        header: 'Actions',
        cell: ({ row }) =>
            `<a href="/products/${row.original.id}/edit" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">Edit</a>`,
    },
];
</script>

<template>
    <AuthenticatedLayout header="Products">
        <Head title="Products" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Products"
                :count="products.total || 0"
                :filters="filters"
                routeName="products.index"
                addHref="/products/create"
                addLabel="Add Product"
            />

            <AdminDataTable :columns="columns" :data="products" />
        </div>
    </AuthenticatedLayout>
</template>
