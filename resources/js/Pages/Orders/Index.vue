<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusBadgeClass = (status) => {
    return ({
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        confirmed: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        shipped: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        cancelled: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');
};

const columns = [
    {
        accessorKey: 'order_number',
        header: 'Order #',
        cell: ({ row }) => `<div class="font-medium text-slate-900 dark:text-slate-100">${row.original.order_number}</div>`,
    },
    {
        accessorKey: 'customer',
        header: 'Customer',
        cell: ({ row }) => row.original.customer?.name || '<span class="text-slate-400">—</span>',
    },
    {
        accessorKey: 'total_amount',
        header: 'Total',
        cell: ({ row }) => `PKR ${Number(row.original.total_amount || 0).toLocaleString()}`,
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadgeClass(row.original.status)}">${row.original.status_label}</span>`,
    },
    {
        accessorKey: 'created_at',
        header: 'Date',
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }) => `<a href="/orders/${row.original.id}" class="font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">View</a>`,
    },
];

const filterFields = [
    {
        name: 'status',
        label: 'Status',
        options: [
            { value: 'pending', label: 'Pending' },
            { value: 'confirmed', label: 'Confirmed' },
            { value: 'shipped', label: 'Shipped' },
            { value: 'delivered', label: 'Delivered' },
            { value: 'cancelled', label: 'Cancelled' },
        ],
    },
    {
        name: 'date',
        label: 'Date',
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
    <AuthenticatedLayout header="Orders">
        <Head title="Orders" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Orders"
                :count="orders.total || 0"
                :filters="filters"
                routeName="orders.index"
                searchPlaceholder="Search by order number or customer..."
                :filterFields="filterFields"
            />

            <AdminDataTable :columns="columns" :data="orders" />
        </div>
    </AuthenticatedLayout>
</template>
