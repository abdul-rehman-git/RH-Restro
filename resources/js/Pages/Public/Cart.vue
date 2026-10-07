<script setup>
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoHead from '@/Components/SeoHead.vue';
import Button from '@/public/components/Button.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useCustomer } from '@/composables/useCustomer';
import { formatPrice } from '@/public/utils/formatPrice';
import { Trash2, Minus, Plus, ShoppingBag, ShieldCheck } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const { customer, cartItems, updateCartQuantity, removeFromCart, openAuthModal, fetchCart } = useCustomer();
const page = usePage();
const site = computed(() => page.props.site || {});

const cartTotal = computed(() => {
    return cartItems.value.reduce((total, item) => {
        const itemPrice = Number(item.price ?? item.variant?.price ?? item.product?.price ?? 0);
        return total + (itemPrice * item.quantity);
    }, 0);
});

const proceedToWhatsAppCheckout = async () => {
    if (!customer.value) {
        openAuthModal();
        return;
    }
    if (!site.value.whatsapp) {
        alert('WhatsApp contact number is not configured.');
        return;
    }

    const outOfStockItem = cartItems.value.find(
        (item) => item.in_stock === false || (item.stock_quantity !== undefined && Number(item.stock_quantity) <= 0)
    );

    if (outOfStockItem) {
        toast.error(`"${outOfStockItem.product?.title || 'An item'}" is currently out of stock. Please remove it to proceed.`);
        return;
    }

    const exceedStockItem = cartItems.value.find(
        (item) => item.stock_quantity !== undefined && Number(item.quantity) > Number(item.stock_quantity)
    );

    if (exceedStockItem) {
        toast.error(`Quantity for "${exceedStockItem.product?.title || 'An item'}" exceeds available stock (${exceedStockItem.stock_quantity}).`);
        return;
    }

    const itemsSnapshot = cartItems.value.map((item) => ({
        quantity: Number(item.quantity),
        product: {
            title: item.product?.title || 'Product',
            variant_name: item.variant?.name || null,
            slug: item.product?.slug || null,
            price: Number(item.price ?? item.variant?.price ?? item.product?.price ?? 0),
        },
    }));

    try {
        const { data } = await window.axios.post('/api/customer/order/whatsapp');
        const orderNumber = data.order_number;

        let message = `Hello! I would like to place an order for the following items:\n\n`;
        message += `*Order Number: ${orderNumber}*\n\n`;

        itemsSnapshot.forEach((item, index) => {
            const price = formatPrice(item.product.price || 0);
            const itemTotal = formatPrice((item.product.price || 0) * item.quantity);
            const titleWithVariant = item.product.variant_name
                ? `${item.product.title} (${item.product.variant_name})`
                : item.product.title;

            message += `${index + 1}. *${titleWithVariant}*\n`;
            message += `   Quantity: ${item.quantity} x ${price} = ${itemTotal}\n`;
            if (item.product.slug) {
                message += `   Link: ${window.location.origin}/product/${item.product.slug}\n`;
            }
            message += `\n`;
        });

        message += `*Estimated Total: ${formatPrice(cartTotal.value)}*\n\n`;
        message += 'Please let me know the next steps for payment and shipping. Thank you!';

        const encodedMessage = encodeURIComponent(message);
        const whatsappNumber = site.value.whatsapp.replace(/\D/g, '');
        const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;

        window.open(whatsappUrl, '_blank');

        await fetchCart();
        router.reload({ only: ['cartCount'] });
        toast.success(`Order ${orderNumber} created successfully.`);
    } catch (error) {
        const message = error?.response?.data?.message || 'Unable to place WhatsApp order.';
        toast.error(message);
    }
};
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <h1 class="text-2xl sm:text-4xl font-bold text-foreground tracking-tight">
                    Shopping Cart
                </h1>
                <span v-if="cartItems.length > 0" class="text-xs sm:text-sm font-semibold text-amber-500 bg-amber-500/10 px-3 py-1 rounded-full border border-amber-500/20">
                    {{ cartItems.length }} {{ cartItems.length === 1 ? 'Item' : 'Items' }}
                </span>
            </div>

            <!-- Not Signed In State -->
            <div v-if="!customer" v-reveal="{ preset: 'zoom', duration: 700 }"
                class="rounded-2xl border border-border bg-card p-8 sm:p-12 text-center shadow-luxury">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-foreground mb-2">Sign in to view your cart</h2>
                <p class="text-sm text-muted-foreground mb-6 max-w-md mx-auto">
                    Access your saved items, track orders, and enjoy a seamless checkout experience.
                </p>
                <Button size="lg" @click="openAuthModal" class="w-full sm:w-auto">Sign In or Create Account</Button>
            </div>

            <!-- Empty Cart State -->
            <div v-else-if="cartItems.length === 0" v-reveal="{ preset: 'zoom', duration: 700 }"
                class="rounded-2xl border border-border bg-card p-8 sm:p-12 text-center shadow-luxury">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-muted flex items-center justify-center mx-auto mb-5">
                    <ShoppingBag class="w-8 h-8 sm:w-10 sm:h-10 text-muted-foreground" />
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-foreground mb-2">Your cart is empty</h2>
                <p class="text-sm text-muted-foreground mb-6">Looks like you haven't added any items to your cart yet.</p>
                <Link href="/shop">
                    <Button size="lg" class="w-full sm:w-auto">Continue Shopping</Button>
                </Link>
            </div>

            <!-- Cart Items Grid Layout -->
            <div v-else class="grid lg:grid-cols-[1fr_380px] gap-6 sm:gap-8 items-start">
                <!-- Cart Item List -->
                <div class="space-y-3 sm:space-y-4">
                    <div v-for="item in cartItems" :key="item.id"
                        class="relative flex gap-3.5 sm:gap-6 p-3.5 sm:p-5 bg-card border border-border/80 rounded-2xl shadow-sm hover:border-amber-500/40 transition-all">
                        
                        <!-- Thumbnail Image -->
                        <Link :href="`/product/${item.product?.slug}`" class="shrink-0">
                            <img :src="item.image || item.variant?.image || item.product?.image || '/placeholder.jpg'" :alt="item.product?.title"
                                class="w-20 h-20 sm:w-28 sm:h-28 object-cover rounded-xl border border-border/80 shadow-xs" />
                        </Link>

                        <!-- Content & Actions -->
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-2">
                                    <Link :href="`/product/${item.product?.slug}`" class="min-w-0 flex-1">
                                        <h3 class="text-sm sm:text-lg font-bold text-foreground hover:text-amber-500 transition-colors truncate">
                                            {{ item.product?.title }}
                                        </h3>
                                    </Link>
                                    <!-- Price -->
                                    <p class="text-sm sm:text-lg font-extrabold text-amber-500 shrink-0 ml-2">
                                        {{ formatPrice(item.price ?? item.variant?.price ?? item.product?.price) }}
                                    </p>
                                </div>

                                 <!-- Variant & Category Sub-tags -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span v-if="item.variant?.name" class="inline-flex items-center text-[10px] sm:text-xs font-bold text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                        Variant: {{ item.variant.name }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ item.product?.category?.name || 'Item' }}</span>

                                    <!-- Stock Warnings -->
                                    <span v-if="item.in_stock === false || (item.stock_quantity !== undefined && Number(item.stock_quantity) <= 0)" class="inline-flex items-center text-[10px] sm:text-xs font-bold text-red-500 bg-red-500/10 px-2 py-0.5 rounded-md border border-red-500/20">
                                        Out of Stock
                                    </span>
                                    <span v-else-if="item.stock_quantity !== undefined && Number(item.quantity) > Number(item.stock_quantity)" class="inline-flex items-center text-[10px] sm:text-xs font-bold text-amber-500 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
                                        Only {{ item.stock_quantity }} available
                                    </span>
                                </div>
                            </div>

                            <!-- Bottom Row: Stepper & Remove Button -->
                            <div class="flex items-center justify-between mt-3 sm:mt-4">
                                <div class="flex items-center border border-border/80 rounded-xl bg-background/80 shadow-xs">
                                    <button @click="updateCartQuantity(item.id, Math.max(1, item.quantity - 1))"
                                        class="p-1.5 sm:p-2 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-30"
                                        :disabled="Number(item.quantity) <= 1"
                                        aria-label="Decrease quantity">
                                        <Minus class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                    </button>
                                    <span class="w-8 sm:w-10 text-center font-bold text-xs sm:text-sm text-foreground">{{ item.quantity }}</span>
                                    <button @click="updateCartQuantity(item.id, Number(item.quantity) + 1)"
                                        class="p-1.5 sm:p-2 text-muted-foreground hover:text-foreground transition-colors disabled:opacity-30"
                                        :disabled="item.stock_quantity !== undefined && Number(item.quantity) >= Number(item.stock_quantity)"
                                        aria-label="Increase quantity">
                                        <Plus class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                    </button>
                                </div>

                                <button @click="removeFromCart(item.id)"
                                    class="flex items-center text-xs font-semibold text-red-500 hover:text-red-600 bg-red-500/10 hover:bg-red-500/20 px-2.5 py-1.5 rounded-xl transition-colors">
                                    <Trash2 class="w-3.5 h-3.5 sm:mr-1" />
                                    <span class="hidden sm:inline">Remove</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Box -->
                <div class="lg:sticky lg:top-24 bg-card border border-border rounded-2xl p-5 sm:p-6 shadow-luxury space-y-5">
                    <h3 class="text-base sm:text-lg font-bold text-foreground border-b border-border pb-3">Order Summary</h3>

                    <div class="space-y-3 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-semibold text-foreground">{{ formatPrice(cartTotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Shipping</span>
                            <span class="text-muted-foreground font-medium">Calculated at checkout</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Taxes</span>
                            <span class="text-muted-foreground font-medium">Calculated at checkout</span>
                        </div>
                        <div class="pt-3 border-t border-border flex justify-between items-center">
                            <span class="text-sm sm:text-base font-bold text-foreground">Estimated Total</span>
                            <span class="text-lg sm:text-2xl font-extrabold text-amber-500">{{ formatPrice(cartTotal) }}</span>
                        </div>
                    </div>

                    <button @click="proceedToWhatsAppCheckout"
                        class="w-full flex items-center justify-center gap-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] px-5 py-3.5 font-bold text-white shadow-md transition-all">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                        </svg>
                        Checkout via WhatsApp
                    </button>

                    <div class="flex items-center justify-center gap-1.5 text-xs text-muted-foreground pt-1">
                        <ShieldCheck class="w-4 h-4 text-emerald-500 shrink-0" />
                        Direct & secure order connection
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
