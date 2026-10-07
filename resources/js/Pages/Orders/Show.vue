<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { toast } from 'vue-sonner';

const props = defineProps({
    order: { type: Object, required: true },
});

const form = useForm({
    status: props.order.status || 'pending',
});

const paymentForm = useForm({
    status: props.order.payment?.status || 'pending',
});

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'shipped', label: 'Shipped' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' },
];

const paymentStatusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'refunded', label: 'Refunded' },
];

const badgeClass = (status) => {
    return ({
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        confirmed: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        shipped: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        cancelled: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');
};

const submit = () => {
    form.patch(route('orders.update', props.order.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Order status updated.'),
        onError: () => toast.error('Unable to update order status.'),
    });
};

const submitPaymentStatus = () => {
    if (!props.order.payment?.id) {
        toast.error('No payment record found for this order.');
        return;
    }

    paymentForm.patch(route('payments.update', props.order.payment.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Payment status updated.'),
        onError: () => toast.error('Unable to update payment status.'),
    });
};
</script>

<template>
    <AuthenticatedLayout header="Order Details">
        <Head :title="`Order ${order.order_number}`" />

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Order Number</p>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ order.order_number }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ order.created_at }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(order.status)">
                        {{ order.status_label }}
                    </span>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Customer</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                        <p><span class="font-medium">Name:</span> {{ order.customer?.name || '—' }}</p>
                        <p><span class="font-medium">Email:</span> {{ order.customer?.email || '—' }}</p>
                        <p><span class="font-medium">Phone:</span> {{ order.customer?.phone || '—' }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Payment</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-200" v-if="order.payment">
                        <p><span class="font-medium">Method:</span> {{ order.payment.method }}</p>
                        <p><span class="font-medium">Status:</span> {{ order.payment.status_label }}</p>
                        <p><span class="font-medium">Amount:</span> PKR {{ Number(order.payment.amount || 0).toLocaleString() }}</p>
                        <p><span class="font-medium">Created:</span> {{ order.payment.created_at }}</p>
                        <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center" @submit.prevent="submitPaymentStatus">
                            <select
                                v-model="paymentForm.status"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            >
                                <option v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <PrimaryButton :disabled="paymentForm.processing" class="rounded-xl px-4 py-2.5 text-sm normal-case tracking-normal">
                                Update Payment
                            </PrimaryButton>
                        </form>
                        <p v-if="paymentForm.errors.status" class="text-sm text-rose-600">{{ paymentForm.errors.status }}</p>
                    </div>
                    <p v-else class="mt-4 text-sm text-slate-500 dark:text-slate-400">No payment record found.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Order Items</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Product</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Price</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Qty</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="px-3 py-3 text-sm text-slate-800 dark:text-slate-100">{{ item.product_title }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700 dark:text-slate-200">PKR {{ Number(item.product_price || 0).toLocaleString() }}</td>
                                <td class="px-3 py-3 text-sm text-slate-700 dark:text-slate-200">{{ item.quantity }}</td>
                                <td class="px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">PKR {{ Number(item.subtotal || 0).toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        Total: PKR {{ Number(order.total_amount || 0).toLocaleString() }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Update Status</h3>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center" @submit.prevent="submit">
                    <select
                        v-model="form.status"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <PrimaryButton :disabled="form.processing" class="rounded-xl px-4 py-2.5 text-sm normal-case tracking-normal">
                        Save Status
                    </PrimaryButton>
                </form>
                <p v-if="form.errors.status" class="mt-2 text-sm text-rose-600">{{ form.errors.status }}</p>
            </div>

            <div>
                <Link href="/orders" class="text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                    Back to Orders
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
