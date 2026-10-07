<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';

defineProps({
    payments: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusBadgeClass = (status) => {
    return ({
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        paid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        refunded: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');
};

const columns = [
    {
        accessorKey: 'order_number',
        header: 'Payment Ref',
        cell: ({ row }) => `<div class="font-medium text-slate-900 dark:text-slate-100">${row.original.order_number || '—'}</div>`,
    },
    {
        accessorKey: 'customer',
        header: 'Customer',
        cell: ({ row }) => row.original.customer?.name || '<span class="text-slate-400">—</span>',
    },
    {
        accessorKey: 'amount',
        header: 'Amount',
        cell: ({ row }) => `PKR ${Number(row.original.amount || 0).toLocaleString()}`,
    },
    {
        accessorKey: 'method',
        header: 'Method',
        cell: ({ row }) => `<span class="uppercase">${row.original.method || '—'}</span>`,
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
        cell: ({ row }) => {
            if (!row.original.order_id) {
                return '<span class="text-slate-400">—</span>';
            }

            return `<a href="/orders/${row.original.order_id}" class="font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Open Order</a>`;
        },
    },
];

const filterFields = [
    {
        name: 'status',
        label: 'Status',
        options: [
            { value: 'pending', label: 'Pending' },
            { value: 'paid', label: 'Paid' },
            { value: 'refunded', label: 'Refunded' },
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
    <AuthenticatedLayout header="Payments">
        <Head title="Payments" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Payments"
                :count="payments.total || 0"
                :filters="filters"
                routeName="payments.index"
                searchPlaceholder="Search..."
                :filterFields="filterFields"
            />

            <AdminDataTable :columns="columns" :data="payments" />
        </div>
    </AuthenticatedLayout>
</template>
