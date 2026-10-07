<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    review: { type: Object, required: true },
});

const approvalForm = useForm({
    is_approved: props.review.is_approved ? '1' : '0',
});

const approve = () => {
    approvalForm
        .transform(() => ({ is_approved: '1' }))
        .put(route('admin-reviews.update', props.review.id));
};

const reject = () => {
    approvalForm
        .transform(() => ({ is_approved: '0' }))
        .put(route('admin-reviews.update', props.review.id));
};
</script>

<template>
    <AuthenticatedLayout header="Review Details">
        <Head title="Review Details" />

        <div class="max-w-4xl space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ review.reviewer_name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ review.product?.title || 'General Review' }}
                        </p>
                    </div>

                    <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                        :class="review.is_approved
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700'"
                    >
                        {{ review.is_approved ? 'Approved' : 'Pending' }}
                    </span>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Rating</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ review.rating }}/5</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Reviewed At</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ review.reviewed_at || 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Verified Purchase</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ review.is_verified_purchase ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="mb-2 text-sm font-medium text-slate-500 dark:text-slate-400">Comment</p>
                    <p class="whitespace-pre-line text-slate-900 dark:text-slate-100">{{ review.comment }}</p>
                </div>

                <div v-if="review.images?.length" class="mt-6">
                    <p class="mb-3 text-sm font-medium text-slate-500 dark:text-slate-400">Images</p>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <img
                            v-for="image in review.images"
                            :key="image.path"
                            :src="image.url"
                            alt="Review image"
                            class="h-40 w-full rounded-xl border border-slate-200 object-cover dark:border-slate-700"
                        />
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                    :disabled="approvalForm.processing"
                    @click="approve"
                >
                    Approve
                </button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700"
                    :disabled="approvalForm.processing"
                    @click="reject"
                >
                    Reject
                </button>
                <Link
                    href="/admin-reviews"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300"
                >
                    Back to Reviews
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
