<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    customers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const columns = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => `<div class="font-medium text-slate-900 dark:text-slate-100">${row.original.name}</div>`,
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => `<div class="break-all">${row.original.email}</div>`,
    },
    {
        accessorKey: 'phone',
        header: 'Phone',
        cell: ({ row }) => row.original.phone || '<span class="text-slate-400">—</span>',
    },
    {
        accessorKey: 'created_at',
        header: 'Joined',
    },
    {
        accessorKey: 'actions',
        header: 'Action',
        cell: ({ row }) => `<a href="/customers/${row.original.id}" class="text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">View Details</a>`,
    },
];

const filterFields = [
    {
        name: 'date',
        label: 'Joined',
        options: [
            { value: 'today', label: 'Today' },
            { value: 'this_week', label: 'This Week' },
            { value: 'this_month', label: 'This Month' },
            { value: 'this_year', label: 'This Year' },
        ],
    },
];
</script>

<template>
    <AuthenticatedLayout header="Customers">

        <Head title="Customers" />

        <div class="space-y-6">
            <AdminTableToolbar title="Customers" :count="customers.total || 0" :filters="filters"
                routeName="customers.index" searchPlaceholder="Search by name, email, or phone..."
                :filterFields="filterFields" />

            <AdminDataTable :columns="columns" :data="customers" />
        </div>
    </AuthenticatedLayout>
</template>
