<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminDataTable from '@/Components/AdminDataTable.vue';
import AdminTableToolbar from '@/Components/AdminTableToolbar.vue';

defineProps({
    contactInquiries: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusBadgeClass = (status) => {
    return ({
        new: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        in_progress: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        replied: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        closed: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');
};

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
        accessorKey: 'subject',
        header: 'Subject',
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
        cell: ({ row }) => `<a href="/contact-inquiries/${row.original.id}" class="font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">View</a>`,
    },
];

</script>

<template>
    <AuthenticatedLayout header="Contact Inquiries">
        <Head title="Contact Inquiries" />

        <div class="space-y-6">
            <AdminTableToolbar
                title="Contact Inquiries"
                :count="contactInquiries.total || 0"
                :filters="filters"
                routeName="contact-inquiries.index"
                searchPlaceholder="Search by name, email, or subject..."
            />

            <AdminDataTable :columns="columns" :data="contactInquiries" />
        </div>
    </AuthenticatedLayout>
</template>
