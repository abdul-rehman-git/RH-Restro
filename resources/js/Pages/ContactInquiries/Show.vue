<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    contactInquiry: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
});

const statusBadgeClass = (status) => {
    return ({
        new: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        in_progress: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        replied: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        closed: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');
};

const statusLabel = (status, options) => {
    return options.find((option) => option.value === status)?.label || status;
};
</script>

<template>
    <AuthenticatedLayout header="Inquiry Details">
        <Head title="Inquiry Details" />

        <div class="max-w-4xl">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Inquiry Details
                        </h2>
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusBadgeClass(contactInquiry.status)"
                        >
                            {{ statusLabel(contactInquiry.status, statusOptions) }}
                        </span>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Name</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Email</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Phone</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.phone || 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Subject</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.subject }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Source</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.source_page || 'contact' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Date</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ contactInquiry.created_at }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Message</p>
                    <p class="whitespace-pre-line text-slate-900 dark:text-slate-100">{{ contactInquiry.message }}</p>
                </div>

                <div v-if="contactInquiry.admin_notes" class="mb-6">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Admin Notes</p>
                    <p class="whitespace-pre-line text-slate-900 dark:text-slate-100">{{ contactInquiry.admin_notes }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <Link href="/contact-inquiries">
                        <button class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                            Back to List
                        </button>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
