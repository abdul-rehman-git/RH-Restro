<script setup>
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';

const page = usePage();
import SectionHeader from '@/public/components/SectionHeader.vue';
import Button from '@/public/components/Button.vue';
import { formatPrice } from '@/public/utils/formatPrice';

const orderNumber = ref('');
const loading = ref(false);
const errorMessage = ref('');
const order = ref(null);

const normalizeOrderNumber = (value) => {
    return (value || '').trim().toUpperCase();
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-50 border-yellow-200 dark:bg-yellow-950/40 dark:border-yellow-900';
        case 'confirmed':
            return 'bg-blue-50 border-blue-200 dark:bg-blue-950/40 dark:border-blue-900';
        case 'shipped':
            return 'bg-purple-50 border-purple-200 dark:bg-purple-950/40 dark:border-purple-900';
        case 'delivered':
            return 'bg-green-50 border-green-200 dark:bg-green-950/40 dark:border-green-900';
        case 'cancelled':
            return 'bg-red-50 border-red-200 dark:bg-red-950/40 dark:border-red-900';
        default:
            return 'bg-slate-50 border-slate-200 dark:bg-slate-950/40 dark:border-slate-900';
    }
};

const getStatusTextColor = (status) => {
    switch (status) {
        case 'pending':
            return 'text-yellow-700 dark:text-yellow-300';
        case 'confirmed':
            return 'text-blue-700 dark:text-blue-300';
        case 'shipped':
            return 'text-purple-700 dark:text-purple-300';
        case 'delivered':
            return 'text-green-700 dark:text-green-300';
        case 'cancelled':
            return 'text-red-700 dark:text-red-300';
        default:
            return 'text-slate-700 dark:text-slate-300';
    }
};

const getPaymentStatusColor = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-50 border-yellow-200 dark:bg-yellow-950/40 dark:border-yellow-900';
        case 'completed':
            return 'bg-green-50 border-green-200 dark:bg-green-950/40 dark:border-green-900';
        case 'failed':
            return 'bg-red-50 border-red-200 dark:bg-red-950/40 dark:border-red-900';
        case 'refunded':
            return 'bg-blue-50 border-blue-200 dark:bg-blue-950/40 dark:border-blue-900';
        case 'cancelled':
            return 'bg-red-50 border-red-200 dark:bg-red-950/40 dark:border-red-900';
        default:
            return 'bg-slate-50 border-slate-200 dark:bg-slate-950/40 dark:border-slate-900';
    }
};

const getPaymentStatusTextColor = (status) => {
    switch (status) {
        case 'pending':
            return 'text-yellow-700 dark:text-yellow-300';
        case 'completed':
            return 'text-green-700 dark:text-green-300';
        case 'failed':
            return 'text-red-700 dark:text-red-300';
        case 'refunded':
            return 'text-blue-700 dark:text-blue-300';
        case 'cancelled':
            return 'text-red-700 dark:text-red-300';
        default:
            return 'text-slate-700 dark:text-slate-300';
    }
};

const trackOrder = async () => {
    const normalized = normalizeOrderNumber(orderNumber.value);
    orderNumber.value = normalized;
    errorMessage.value = '';
    order.value = null;

    if (!normalized) {
        errorMessage.value = 'Please enter your order number first.';
        return;
    }

    loading.value = true;

    try {
        const { data } = await window.axios.post('/api/public/order-tracking', {
            order_number: normalized,
        });

        order.value = data.order;
    } catch (error) {
        errorMessage.value = error?.response?.data?.message || 'Unable to track this order right now.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const queryOrderNumber = normalizeOrderNumber(params.get('order_number'));

    if (queryOrderNumber) {
        orderNumber.value = queryOrderNumber;
        trackOrder();
    }
});
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background py-8 sm:py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    title="Track Your Order"
                    subtitle="Enter your order number to check the latest status, order items, payment details, and updates."
                    centered
                />

                <div class="mx-auto max-w-3xl rounded-2xl border border-border bg-card p-6 shadow-luxury sm:p-8">
                    <form class="space-y-4" @submit.prevent="trackOrder">
                        <label for="order-number" class="block text-sm font-medium text-foreground">
                            Order Number
                        </label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input
                                id="order-number"
                                v-model="orderNumber"
                                type="text"
                                placeholder="Example: ORD-20260516-AB12"
                                class="public-field w-full rounded-lg border border-border bg-white px-4 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                            />
                            <Button
                                type="submit"
                                class-name="w-full sm:w-auto sm:min-w-[170px]"
                                :disabled="loading"
                            >
                                {{ loading ? 'Checking...' : 'Track Order' }}
                            </Button>
                        </div>
                    </form>

                    <p v-if="errorMessage" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                        {{ errorMessage }}
                    </p>
                </div>

                <div v-if="order" class="mx-auto mt-8 max-w-5xl space-y-6">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <div class="rounded-xl border border-border bg-card p-3 sm:p-4 shadow-sm">
                            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.15em] sm:tracking-[0.2em] text-muted-foreground">Order Number</p>
                            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm font-semibold text-foreground break-all">{{ order.order_number }}</p>
                        </div>
                        <div :class="['rounded-xl border p-3 sm:p-4 shadow-sm', getStatusColor(order.status)]">
                            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.15em] sm:tracking-[0.2em] text-muted-foreground">Order Status</p>
                            <p :class="['mt-1.5 sm:mt-2 text-xs sm:text-sm font-semibold', getStatusTextColor(order.status)]">{{ order.status_label }}</p>
                        </div>
                        <div class="rounded-xl border border-border bg-card p-3 sm:p-4 shadow-sm">
                            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.15em] sm:tracking-[0.2em] text-muted-foreground">Placed On</p>
                            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm font-semibold text-foreground">{{ order.placed_at }}</p>
                        </div>
                        <div class="rounded-xl border border-border bg-card p-3 sm:p-4 shadow-sm">
                            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.15em] sm:tracking-[0.2em] text-muted-foreground">Order Total</p>
                            <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm font-semibold text-foreground">{{ formatPrice(order.total_amount) }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-luxury">
                        <h3 class="mb-5 text-lg font-semibold text-foreground">Status Timeline</h3>
                        <div>
                            <div
                                v-for="(step, index) in order.timeline"
                                :key="`${step.key}-${index}`"
                                class="flex items-start gap-3 relative"
                            >
                                <div class="relative flex flex-col items-center">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border text-xs font-bold relative z-10"
                                        :class="step.completed
                                            ? 'border-amber-500 bg-amber-500/15 text-amber-500'
                                            : 'border-border bg-muted text-muted-foreground'"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                    <div
                                        v-if="index < order.timeline.length - 1"
                                        class="w-0.5 h-12"
                                        :class="order.timeline[index + 1].completed
                                            ? 'bg-amber-500'
                                            : 'bg-border'"
                                    ></div>
                                </div>
                                <p
                                    class="text-sm pt-1"
                                    :class="step.current ? 'font-semibold text-foreground' : 'text-muted-foreground'"
                                >
                                    {{ step.label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-border bg-card p-6 shadow-luxury">
                            <h3 class="mb-5 text-lg font-semibold text-foreground">Order Items</h3>

                            <div v-if="order.items.length" class="space-y-3">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="rounded-xl border border-border bg-background p-4"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-medium text-foreground">{{ item.title }}</p>
                                        <p class="text-sm font-semibold text-foreground">{{ formatPrice(item.subtotal) }}</p>
                                    </div>
                                    <p class="mt-2 text-xs text-muted-foreground">
                                        {{ item.quantity }} × {{ formatPrice(item.price) }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">No items found for this order.</p>
                        </div>

                        <div class="space-y-6">
                            <div class="rounded-2xl border border-border bg-card p-6 shadow-luxury">
                                <h3 class="mb-4 text-lg font-semibold text-foreground">Payment Information</h3>
                                <div v-if="order.payment" class="space-y-3">
                                    <div class="rounded-xl border border-border bg-background p-3 text-sm">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted-foreground">Method</span>
                                            <span class="font-medium text-foreground capitalize">{{ order.payment.method }}</span>
                                        </div>
                                    </div>
                                    <div :class="['rounded-xl border p-3 text-sm', getPaymentStatusColor(order.payment.status)]">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted-foreground">Payment Status</span>
                                            <span :class="['font-medium', getPaymentStatusTextColor(order.payment.status)]">{{ order.payment.status_label }}</span>
                                        </div>
                                    </div>
                                    <div class="rounded-xl border border-border bg-background p-3 text-sm">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted-foreground">Amount</span>
                                            <span class="font-medium text-foreground">{{ formatPrice(order.payment.amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-muted-foreground">Payment information is not available yet.</p>
                            </div>

                            <div v-if="order.notes" class="rounded-2xl border border-border bg-card p-6 shadow-luxury">
                                <h3 class="mb-3 text-lg font-semibold text-foreground">Order Notes</h3>
                                <p class="text-sm leading-6 text-muted-foreground whitespace-pre-line">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
