import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

// Global state
const isAuthModalOpen = ref(false);
const wishlistIds = ref(new Set());
const cartItems = ref([]);
const lastAddedCartItem = ref(null);
const showFloatingCart = ref(false);
let floatingCartTimer = null;

const triggerFloatingCart = (item) => {
    lastAddedCartItem.value = item;
    showFloatingCart.value = true;
    if (floatingCartTimer) clearTimeout(floatingCartTimer);
    floatingCartTimer = setTimeout(() => {
        showFloatingCart.value = false;
    }, 7000);
};

const closeFloatingCart = () => {
    showFloatingCart.value = false;
};

export function useCustomer() {
    const page = usePage();

    const customer = computed(() => page.props.customer || null);
    const cartCount = computed(() => page.props.cartCount || 0);

    const openAuthModal = () => {
        isAuthModalOpen.value = true;
    };

    const closeAuthModal = () => {
        isAuthModalOpen.value = false;
    };

    const fetchWishlist = async () => {
        if (!customer.value) return;
        try {
            const { data } = await window.axios.get('/api/customer/wishlist');
            wishlistIds.value = new Set(data.wishlistIds);
        } catch (error) {
            console.error('Failed to fetch wishlist', error);
        }
    };

    const toggleWishlist = async (productId) => {
        if (!customer.value) {
            openAuthModal();
            return;
        }

        // Optimistic update
        const isCurrentlyLiked = wishlistIds.value.has(productId);
        if (isCurrentlyLiked) {
            wishlistIds.value.delete(productId);
            toast.success('Removed from wishlist');
        } else {
            wishlistIds.value.add(productId);
            toast.success('Added to wishlist');
        }

        try {
            const { data } = await window.axios.post('/api/customer/wishlist/toggle', { product_id: productId });
            wishlistIds.value = new Set(data.wishlistIds);
        } catch (error) {
            // Revert on error
            if (isCurrentlyLiked) {
                wishlistIds.value.add(productId);
            } else {
                wishlistIds.value.delete(productId);
            }
            console.error('Failed to toggle wishlist', error);
        }
    };

    const fetchCart = async () => {
        if (!customer.value) return;
        try {
            const { data } = await window.axios.get('/api/customer/cart');
            cartItems.value = data.cartItems;
        } catch (error) {
            console.error('Failed to fetch cart', error);
        }
    };

    const addToCart = async (productId, quantity = 1, productVariantId = null) => {
        if (!customer.value) {
            openAuthModal();
            return;
        }

        try {
            const { data } = await window.axios.post('/api/customer/cart', {
                product_id: productId,
                product_variant_id: productVariantId,
                quantity,
            });
            router.reload({ only: ['cartCount'] }); // Reload Inertia props to update count
            toast.success('Added to cart');

            if (data?.cartItem) {
                const item = data.cartItem;
                const p = item.product || {};
                const v = item.variant || {};
                triggerFloatingCart({
                    title: p.title || 'Product',
                    variant_name: v.name || null,
                    price: v.price ?? p.price ?? 0,
                    image: v.image || p.image || (p.images && p.images[0]) || '/placeholder.jpg',
                    quantity: item.quantity || quantity,
                });
            }
        } catch (error) {
            console.error('Failed to add to cart', error);
            const errs = error.response?.data?.errors;
            const message = errs?.stock?.[0]
                || errs?.quantity?.[0]
                || errs?.product?.[0]
                || error.response?.data?.message
                || 'Failed to add to cart';
            toast.error(message);
        }
    };

    const updateCartQuantity = async (cartItemId, quantity) => {
        if (!customer.value) return;

        try {
            await window.axios.patch(`/api/customer/cart/${cartItemId}`, { quantity });
            await fetchCart();
            router.reload({ only: ['cartCount'] });
        } catch (error) {
            console.error('Failed to update cart quantity', error);
            const errs = error.response?.data?.errors;
            const message = errs?.stock?.[0]
                || errs?.quantity?.[0]
                || errs?.product?.[0]
                || error.response?.data?.message
                || 'Failed to update quantity';
            toast.error(message);
        }
    };

    const removeFromCart = async (cartItemId) => {
        if (!customer.value) return;

        try {
            await window.axios.delete(`/api/customer/cart/${cartItemId}`);
            await fetchCart();
            router.reload({ only: ['cartCount'] });
            toast.success('Item removed');
        } catch (error) {
            console.error('Failed to remove from cart', error);
            toast.error('Failed to remove item');
        }
    };

    const logout = async () => {
        try {
            await window.axios.post('/logout');
            window.location.reload();
        } catch (error) {
            console.error('Failed to logout', error);
        }
    };

    return {
        customer,
        cartCount,
        cartItems,
        wishlistIds,
        isAuthModalOpen,
        openAuthModal,
        closeAuthModal,
        fetchWishlist,
        toggleWishlist,
        fetchCart,
        addToCart,
        updateCartQuantity,
        removeFromCart,
        logout,
        lastAddedCartItem,
        showFloatingCart,
        closeFloatingCart,
        triggerFloatingCart,
    };
}
