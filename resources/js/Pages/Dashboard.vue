<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const loading = ref(true);
const stats = ref(null);

const orderStats = computed(() => stats.value?.order_stats || {});
const customerStats = computed(() => stats.value?.customer_stats || {});
const paymentStats = computed(() => stats.value?.payment_stats || {});
const salesSummary = computed(() => stats.value?.sales_summary || {});
const barData = computed(() => stats.value?.weekly_sales || []);
const lineData = computed(() => stats.value?.monthly_orders || []);

const maxBarSales = computed(() => Math.max(...(barData.value?.map(item => item.sales) || [1])));
const maxLineOrders = computed(() => Math.max(...(lineData.value?.map(item => item.orders) || [1])));

const statCategories = computed(() => [
    { label: 'Total Orders', value: orderStats.value.all, color: 'text-slate-900 dark:text-slate-100' },
    { label: 'Customers', value: customerStats.value.all, color: 'text-slate-900 dark:text-slate-100' },
    { label: 'Total Sales', value: '$' + (salesSummary.value.total_sales || 0).toLocaleString(), color: 'text-green-600 dark:text-green-300' },
]);

const fetchStats = async () => {
    try {
        loading.value = true;
        const response = await window.axios.get('/api/dashboard/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Failed to fetch dashboard stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
</script>

<template>
    <AuthenticatedLayout header="Dashboard">
        <Head title="Dashboard" />

        <div class="space-y-6">
            <!-- General Stats -->
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">General Stats</h3>
            <section class="grid gap-4 md:grid-cols-3">
                <div
                    v-for="stat in statCategories"
                    :key="stat.label"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-sm text-slate-500">
                        {{ stat.label }}
                    </p>
                    <p :class="['mt-3 text-3xl font-semibold', stat.color]">
                        {{ stat.value }}
                    </p>
                </div>
            </section>

            <!-- Order Stats -->
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">Order Stats</h3>
            <section class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">All Orders</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ orderStats.all || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 shadow-sm dark:border-yellow-900/30 dark:bg-yellow-950/20">
                    <p class="text-xs text-yellow-700 uppercase tracking-widest dark:text-yellow-300">Pending</p>
                    <p class="mt-2 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ orderStats.pending || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-900/30 dark:bg-blue-950/20">
                    <p class="text-xs text-blue-700 uppercase tracking-widest dark:text-blue-300">Confirmed</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ orderStats.confirmed || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-purple-200 bg-purple-50 p-4 shadow-sm dark:border-purple-900/30 dark:bg-purple-950/20">
                    <p class="text-xs text-purple-700 uppercase tracking-widest dark:text-purple-300">Shipped</p>
                    <p class="mt-2 text-2xl font-semibold text-purple-900 dark:text-purple-100">{{ orderStats.shipped || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 shadow-sm dark:border-green-900/30 dark:bg-green-950/20">
                    <p class="text-xs text-green-700 uppercase tracking-widest dark:text-green-300">Delivered</p>
                    <p class="mt-2 text-2xl font-semibold text-green-900 dark:text-green-100">{{ orderStats.delivered || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-900/30 dark:bg-red-950/20">
                    <p class="text-xs text-red-700 uppercase tracking-widest dark:text-red-300">Cancelled</p>
                    <p class="mt-2 text-2xl font-semibold text-red-900 dark:text-red-100">{{ orderStats.cancelled || 0 }}</p>
                </div>
            </section>

            <!-- Customer Stats -->
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">Customer Stats</h3>
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">All Customers</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ customerStats.all || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">With Orders</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ customerStats.with_orders || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">Completed Orders</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ customerStats.completed_orders || 0 }}</p>
                </div>
            </section>

            <!-- Payment Stats -->
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">Payment Stats</h3>
            <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">All Payments</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ paymentStats.all || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 shadow-sm dark:border-yellow-900/30 dark:bg-yellow-950/20">
                    <p class="text-xs text-yellow-700 uppercase tracking-widest dark:text-yellow-300">Pending</p>
                    <p class="mt-2 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ paymentStats.pending || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 shadow-sm dark:border-green-900/30 dark:bg-green-950/20">
                    <p class="text-xs text-green-700 uppercase tracking-widest dark:text-green-300">Paid</p>
                    <p class="mt-2 text-2xl font-semibold text-green-900 dark:text-green-100">{{ paymentStats.paid || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-900/30 dark:bg-blue-950/20">
                    <p class="text-xs text-blue-700 uppercase tracking-widest dark:text-blue-300">Refunded</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ paymentStats.refunded || 0 }}</p>
                </div>
            </section>

            <!-- Sales Summary -->
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 shadow-sm dark:border-green-900/30 dark:bg-green-950/20">
                    <p class="text-xs text-green-700 uppercase tracking-widest dark:text-green-300">Total Sales (Paid)</p>
                    <p class="mt-2 text-2xl font-semibold text-green-900 dark:text-green-100">
                        ${{ (salesSummary.total_sales || 0).toLocaleString() }}
                    </p>
                </div>
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-900/30 dark:bg-blue-950/20">
                    <p class="text-xs text-blue-700 uppercase tracking-widest dark:text-blue-300">Total Refunded</p>
                    <p class="mt-2 text-2xl font-semibold text-blue-900 dark:text-blue-100">
                        ${{ (salesSummary.total_refunded || 0).toLocaleString() }}
                    </p>
                </div>
                <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 shadow-sm dark:border-yellow-900/30 dark:bg-yellow-950/20">
                    <p class="text-xs text-yellow-700 uppercase tracking-widest dark:text-yellow-300">Pending Amount</p>
                    <p class="mt-2 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">
                        ${{ (salesSummary.total_pending || 0).toLocaleString() }}
                    </p>
                </div>
            </section>

            <!-- Analytics -->
            <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">Analytics</h3>
            <section class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Weekly Sales Chart
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Overview of this week's sales.
                        </p>
                    </div>

                    <div v-if="barData.length" class="space-y-4">
                        <div
                            v-for="item in barData"
                            :key="item.label"
                            class="grid grid-cols-[52px_minmax(0,1fr)_72px] items-center gap-4"
                        >
                            <span class="text-sm font-medium text-slate-500">{{ item.label }}</span>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-slate-900 dark:bg-emerald-400"
                                    :style="{ width: `${Math.max((item.sales / maxBarSales) * 100, 8)}%` }"
                                />
                            </div>
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">${{ item.sales }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center text-slate-500">
                        <p>No sales data available</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Orders Graph
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Monthly order trend at a glance.
                        </p>
                    </div>

                    <div v-if="lineData.length" class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="item in lineData"
                            :key="item.label"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-500">{{ item.label }}</span>
                                <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ item.orders }}</span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-slate-900 dark:bg-emerald-400"
                                    :style="{ width: `${Math.max((item.orders / maxLineOrders) * 100, 10)}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-slate-500">
                        <p>No order data available</p>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
