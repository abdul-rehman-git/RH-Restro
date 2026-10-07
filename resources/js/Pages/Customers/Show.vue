<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const statusBadgeClass = (status) => {
    return {
        pending: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-300',
        confirmed: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        shipped: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
        delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        cancelled: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
};

const paymentStatusBadgeClass = (status) => {
    return {
        pending: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-300',
        paid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        refunded: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
    }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
};
</script>

<template>
    <AuthenticatedLayout header="Customer Details">
        <Head :title="`Customer: ${customer.name}`" />

        <div class="space-y-6">
            <!-- Customer Information -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ customer.name }}</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ customer.email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Phone</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ customer.phone || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Customer Since</p>
                        <p class="mt-1 text-slate-900 dark:text-slate-100">{{ customer.created_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Filter Orders</h3>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Order Status</label>
                        <select
                            :value="filters.status"
                            @change="(e) => $inertia.get(route('customers.show', { customer: customer.id }), { status: e.target.value, date: filters.date }, { preserveScroll: true })"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Date Range</label>
                        <select
                            :value="filters.date"
                            @change="(e) => $inertia.get(route('customers.show', { customer: customer.id }), { status: filters.status, date: e.target.value }, { preserveScroll: true })"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Customer Orders</h3>

                <div v-if="orders.data.length" class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Order #</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Payment</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="order in orders.data" :key="order.id">
                                <td class="px-3 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="statusBadgeClass(order.status)">
                                        {{ order.status_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span v-if="order.payment" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="paymentStatusBadgeClass(order.payment.status)">
                                        {{ order.payment.status_label }}
                                    </span>
                                    <span v-else class="text-xs text-slate-500">—</span>
                                </td>
                                <td class="px-3 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    ${{ Number(order.total_amount || 0).toLocaleString() }}
                                </td>
                                <td class="px-3 py-3 text-sm text-slate-700 dark:text-slate-200">
                                    {{ order.created_at }}
                                </td>
                                <td class="px-3 py-3">
                                    <Link :href="route('orders.show', { order: order.id })" class="text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="mt-4 text-center text-sm text-slate-500 dark:text-slate-400">No orders found for this customer.</p>

                <!-- Pagination -->
                <div v-if="orders.links" class="mt-6 flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-800">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in orders.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium',
                                link.active
                                    ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700',
                                !link.url && 'cursor-default opacity-50',
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>

            <div>
                <Link href="/customers" class="text-sm font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">
                    Back to Customers
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
