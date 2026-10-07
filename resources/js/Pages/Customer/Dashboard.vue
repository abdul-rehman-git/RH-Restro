<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import SeoHead from "@/Components/SeoHead.vue";
const page = usePage();

import {
    LayoutDashboard,
    ShoppingBag,
    Heart,
    FileText,
    User,
    Compass,
    PackageSearch,
    LogOut,
    ChevronRight,
    Eye,
    X,
    Loader2,
    CheckCircle,
    Clock,
    Trash2,
    Camera,
} from "lucide-vue-next";
import { useCustomer } from "@/composables/useCustomer";
import { formatPrice } from "@/public/utils/formatPrice";
import { toast } from "vue-sonner";

const props = defineProps({
    stats: { type: Object, required: true },
    recentOrders: { type: Array, default: () => [] },
    recentInvoices: { type: Array, default: () => [] },
});

const { customer, logout } = useCustomer();

const activeTab = ref("dashboard");

const tabs = [
    { key: "dashboard", label: "Dashboard", icon: LayoutDashboard },
    { key: "orders", label: "Orders", icon: ShoppingBag },
    { key: "wishlist", label: "Wishlist", icon: Heart },
    { key: "invoices", label: "Invoices", icon: FileText },
    { key: "profile", label: "Profile", icon: User },
    { key: "explore", label: "Explore", icon: Compass },
    { key: "track", label: "Track Order", icon: PackageSearch },
    { key: "logout", label: "Logout", icon: LogOut },
];

const tabKeys = tabs.map((tab) => tab.key);

const ordersLoading = ref(false);
const wishlistLoading = ref(false);
const invoicesLoading = ref(false);
const orders = ref(null);
const wishlistProducts = ref(null);
const invoices = ref(null);
const ordersPagination = ref(null);
const invoicesPagination = ref(null);
const confirmLogout = ref(false);
const confirmingWishlistRemoval = ref(false);
const wishlistItemToRemove = ref(null);

const customerFirstName = computed(
    () => customer.value?.name?.split(" ")[0] || "there",
);

const customerInitials = computed(() =>
    (customer.value?.name || "CU")
        .split(" ")
        .map((part) => part[0])
        .join("")
        .slice(0, 2)
        .toUpperCase(),
);

const wishlistCount = computed(() =>
    Array.isArray(wishlistProducts.value)
        ? wishlistProducts.value.length
        : props.stats.wishlist_count || 0,
);

const totalOrders = computed(
    () => ordersPagination.value?.total ?? props.stats.total_orders ?? 0,
);

const overviewCards = computed(() => [
    {
        key: "total_orders",
        label: "Total Orders",
        value: totalOrders.value,
        helper: "All orders placed",
        icon: ShoppingBag,
        iconWrapClass: "bg-blue-50 dark:bg-blue-950/30",
        iconClass: "text-blue-600 dark:text-blue-400",
    },
    {
        key: "completed_orders",
        label: "Completed",
        value: props.stats.completed_orders ?? 0,
        helper: "Delivered successfully",
        icon: CheckCircle,
        iconWrapClass: "bg-green-50 dark:bg-green-950/30",
        iconClass: "text-green-600 dark:text-green-400",
    },
    {
        key: "wishlist_count",
        label: "Wishlist",
        value: wishlistCount.value,
        helper: "Saved for later",
        icon: Heart,
        iconWrapClass: "bg-rose-50 dark:bg-rose-950/30",
        iconClass: "text-rose-600 dark:text-rose-400",
    },
]);

const quickActions = [
    { key: "orders", label: "View Orders", icon: ShoppingBag },
    { key: "wishlist", label: "Open Wishlist", icon: Heart },
    { key: "profile", label: "Edit Profile", icon: User },
];

const isValidTab = (key) => tabKeys.includes(key) && key !== "logout";

const syncTabHash = (key) => {
    if (typeof window === "undefined") {
        return;
    }

    const url = new URL(window.location.href);
    url.hash = key === "dashboard" ? "" : key;
    window.history.replaceState(window.history.state, "", url.toString());
};

const resolveInitialTab = () => {
    if (typeof window === "undefined") {
        return "dashboard";
    }

    const hashTab = window.location.hash.replace("#", "").trim();

    return isValidTab(hashTab) ? hashTab : "dashboard";
};

const fetchOrders = async (page = 1) => {
    ordersLoading.value = true;
    try {
        const { data } = await window.axios.get(
            "/api/customer/dashboard/orders",
            { params: { page } },
        );
        orders.value = data.orders.data;
        ordersPagination.value = {
            current_page: data.orders.current_page,
            last_page: data.orders.last_page,
            total: data.orders.total,
        };
    } catch (e) {
        toast.error("Failed to load orders");
    } finally {
        ordersLoading.value = false;
    }
};

const fetchWishlist = async () => {
    wishlistLoading.value = true;
    try {
        const { data } = await window.axios.get(
            "/api/customer/dashboard/wishlist-products",
        );
        wishlistProducts.value = data.products;
    } catch (e) {
        toast.error("Failed to load wishlist");
    } finally {
        wishlistLoading.value = false;
    }
};

const fetchInvoices = async (page = 1) => {
    invoicesLoading.value = true;
    try {
        const { data } = await window.axios.get(
            "/api/customer/dashboard/invoices",
            { params: { page } },
        );
        invoices.value = data.invoices.data;
        invoicesPagination.value = {
            current_page: data.invoices.current_page,
            last_page: data.invoices.last_page,
            total: data.invoices.total,
        };
    } catch (e) {
        toast.error("Failed to load invoices");
    } finally {
        invoicesLoading.value = false;
    }
};

const requestWishlistRemoval = (product) => {
    wishlistItemToRemove.value = product;
    confirmingWishlistRemoval.value = true;
};

const closeWishlistRemovalConfirmation = () => {
    confirmingWishlistRemoval.value = false;
    wishlistItemToRemove.value = null;
};

const removeFromWishlist = async () => {
    if (!wishlistItemToRemove.value) {
        return;
    }

    const productId = wishlistItemToRemove.value.id;

    try {
        await window.axios.post(`/api/customer/wishlist/${productId}`);
        wishlistProducts.value = wishlistProducts.value.filter(
            (p) => p.id !== productId,
        );
        toast.success("Removed from wishlist");
        closeWishlistRemovalConfirmation();
    } catch (e) {
        toast.error("Failed to remove");
    }
};

const loadTabData = (key) => {
    if (key === "orders" && !orders.value) {
        fetchOrders();
    }

    if (key === "wishlist" && !wishlistProducts.value) {
        fetchWishlist();
    }

    if (key === "invoices" && !invoices.value) {
        fetchInvoices();
    }
};

const switchTab = (key) => {
    if (key === "logout") {
        confirmLogout.value = true;
        return;
    }

    activeTab.value = key;
    syncTabHash(key);
    loadTabData(key);
};

const handleLogout = async () => {
    await logout();
    confirmLogout.value = false;
};

const orderNumberInput = ref("");
const trackingOrder = ref(null);
const trackingLoading = ref(false);
const trackingError = ref("");

const trackOrderLookup = async () => {
    const normalized = orderNumberInput.value.trim().toUpperCase();
    orderNumberInput.value = normalized;
    trackingError.value = "";
    trackingOrder.value = null;
    if (!normalized) {
        trackingError.value = "Please enter an order number.";
        return;
    }
    trackingLoading.value = true;
    try {
        const { data } = await window.axios.post("/api/public/order-tracking", {
            order_number: normalized,
        });
        trackingOrder.value = data.order;
    } catch (error) {
        trackingError.value =
            error?.response?.data?.message || "Unable to track this order.";
    } finally {
        trackingLoading.value = false;
    }
};

const trackExistingOrder = (orderNumber) => {
    orderNumberInput.value = orderNumber;
    switchTab("track");
    trackOrderLookup();
};

// Profile form
const profileForm = ref({
    name: "",
    email: "",
    phone: "",
});
const forgotPasswordEmail = ref("");
const forgotPasswordSending = ref(false);
const forgotPasswordSent = ref(false);
const profilePhotoFile = ref(null);
const profilePhotoPreview = ref(null);
const profileSubmitting = ref(false);
const profileErrors = ref({});
const maxProfilePhotoSizeBytes = 2 * 1024 * 1024;
const maxProfilePhotoSizeLabel = "2MB";

const isProfileDirty = computed(() => {
    const currentName = customer.value?.name || "";
    const currentPhone = customer.value?.phone || "";

    return (
        profileForm.value.name.trim() !== currentName ||
        profileForm.value.phone.trim() !== currentPhone ||
        !!profilePhotoFile.value
    );
});

watch(
    customer,
    (value) => {
        profileForm.value = {
            name: value?.name || "",
            email: value?.email || "",
            phone: value?.phone || "",
        };
        forgotPasswordEmail.value = value?.email || "";

        if (!profilePhotoFile.value) {
            profilePhotoPreview.value = value?.profile_photo_url || null;
        }
    },
    { immediate: true },
);

const onPhotoChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) {
        profileErrors.value.profile_photo = "";
        return;
    }

    profileErrors.value.profile_photo = "";

    if (file.size > maxProfilePhotoSizeBytes) {
        profilePhotoFile.value = null;
        profilePhotoPreview.value = customer.value?.profile_photo_url || null;
        profileErrors.value.profile_photo =
            `Image size cannot be more than ${maxProfilePhotoSizeLabel}.`;
        e.target.value = "";
        return;
    }

    profilePhotoFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
        profilePhotoPreview.value = ev.target?.result;
    };
    reader.readAsDataURL(file);
};

const submitProfile = async () => {
    profileErrors.value = {};

    if (!profileForm.value.name.trim()) {
        profileErrors.value.name = "Name is required";
        return;
    }

    profileSubmitting.value = true;

    try {
        const csrfToken = document.head
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
        const formData = new FormData();
        formData.append("_token", csrfToken || "");
        formData.append("name", profileForm.value.name.trim());
        formData.append("phone", profileForm.value.phone.trim());

        if (profilePhotoFile.value) {
            formData.append("profile_photo", profilePhotoFile.value);
        }

        const { data } = await window.axios.post(
            "/api/customer/dashboard/profile",
            formData,
        );

        profilePhotoFile.value = null;
        profilePhotoPreview.value = data.customer.profile_photo_url;
        profileForm.value = {
            name: data.customer.name || "",
            email: data.customer.email || "",
            phone: data.customer.phone || "",
        };
        toast.success(data.message);

        router.reload({
            only: ["customer"],
            preserveState: true,
            preserveScroll: true,
        });
    } catch (e) {
        if (e?.response?.data?.errors) {
            profileErrors.value = Object.fromEntries(
                Object.entries(e.response.data.errors).map(([k, v]) => [
                    k,
                    Array.isArray(v) ? v[0] : v,
                ]),
            );
        } else {
            toast.error(
                e?.response?.data?.message || "Failed to update profile",
            );
        }
    } finally {
        profileSubmitting.value = false;
    }
};

// Password form
const passwordForm = ref({
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
});
const passwordSubmitting = ref(false);
const passwordErrors = ref({});

const submitPassword = async () => {
    passwordErrors.value = {};
    if (!passwordForm.value.current_password) {
        passwordErrors.value.current_password = "Current password is required";
        return;
    }
    if (passwordForm.value.new_password.length < 8) {
        passwordErrors.value.new_password =
            "New password must be at least 8 characters";
        return;
    }
    if (
        passwordForm.value.new_password !==
        passwordForm.value.new_password_confirmation
    ) {
        passwordErrors.value.new_password_confirmation =
            "Passwords do not match";
        return;
    }
    passwordSubmitting.value = true;
    try {
        const { data } = await window.axios.put(
            "/api/customer/dashboard/password",
            {
                current_password: passwordForm.value.current_password,
                new_password: passwordForm.value.new_password,
                new_password_confirmation:
                    passwordForm.value.new_password_confirmation,
            },
        );
        toast.success(data.message);
        passwordForm.value = {
            current_password: "",
            new_password: "",
            new_password_confirmation: "",
        };
    } catch (e) {
        if (e?.response?.data?.errors) {
            passwordErrors.value = Object.fromEntries(
                Object.entries(e.response.data.errors).map(([k, v]) => [
                    k,
                    Array.isArray(v) ? v[0] : v,
                ]),
            );
        } else {
            toast.error(
                e?.response?.data?.message || "Failed to change password",
            );
        }
    } finally {
        passwordSubmitting.value = false;
    }
};

const sendForgotPassword = async () => {
    forgotPasswordSent.value = false;
    if (!forgotPasswordEmail.value.trim()) {
        toast.error("Please enter your email");
        return;
    }
    forgotPasswordSending.value = true;
    try {
        const { data } = await window.axios.post(
            "/api/customer/forgot-password",
            {
                email: forgotPasswordEmail.value.trim(),
            },
        );
        forgotPasswordSent.value = true;
        toast.success(data.message);
    } catch (e) {
        toast.error(e?.response?.data?.message || "Failed to send reset link");
    } finally {
        forgotPasswordSending.value = false;
    }
};

// Invoice detail modal
const selectedInvoice = ref(null);
const invoiceModalOpen = ref(false);

const viewInvoice = (invoice) => {
    selectedInvoice.value = invoice;
    invoiceModalOpen.value = true;
};

const getStatusColor = (status) => {
    const colors = {
        pending:
            "bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/30 dark:text-yellow-300 dark:border-yellow-900",
        confirmed:
            "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900",
        shipped:
            "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/30 dark:text-purple-300 dark:border-purple-900",
        delivered:
            "bg-green-50 text-green-700 border-green-200 dark:bg-green-950/30 dark:text-green-300 dark:border-green-900",
        cancelled:
            "bg-red-50 text-red-700 border-red-200 dark:bg-red-950/30 dark:text-red-300 dark:border-red-900",
        paid: "bg-green-50 text-green-700 border-green-200 dark:bg-green-950/30 dark:text-green-300 dark:border-green-900",
        refunded:
            "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900",
    };
    return (
        colors[status] ||
        "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-950/30 dark:text-slate-300 dark:border-slate-900"
    );
};

onMounted(() => {
    const initialTab = resolveInitialTab();
    activeTab.value = initialTab;
    syncTabHash(initialTab);
    loadTabData(initialTab);
});
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>

        <div class="min-h-screen bg-background">
            <!-- Header -->
            <!-- <div class="bg-card border-b border-border">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-foreground">My Account</h1>
                    <p class="text-muted-foreground mt-1">Welcome back, {{ customer?.name?.split(' ')[0] }}</p>
                </div>
            </div> -->

            <!-- Tab Bar -->
            <div
                class="sticky top-0 z-40 border-b border-border bg-card/50 backdrop-blur-sm"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Mobile: horizontal scrollable pill bar -->
                    <nav class="no-scrollbar -mx-4 flex gap-1.5 overflow-x-auto px-4 py-2.5 sm:hidden">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="switchTab(tab.key)"
                            class="flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3.5 py-2 text-[12px] font-semibold transition-all"
                            :class="
                                activeTab === tab.key
                                    ? 'bg-amber-500 text-slate-950 font-bold shadow-sm'
                                    : 'bg-muted/60 text-muted-foreground active:bg-muted'
                            "
                        >
                            <component :is="tab.icon" class="h-3.5 w-3.5 shrink-0" />
                            {{ tab.label }}
                        </button>
                    </nav>
                    <!-- Desktop: horizontal tabs -->
                    <nav class="hidden min-w-max space-x-1 overflow-x-auto py-3 sm:flex">
                        <button
                            v-for="tab in tabs"
                            :key="`${tab.key}-desktop`"
                            @click="switchTab(tab.key)"
                            class="flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-medium transition-luxury"
                            :class="
                                activeTab === tab.key
                                    ? 'bg-amber-500 text-slate-950 font-bold shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                            "
                        >
                            <component :is="tab.icon" class="w-4 h-4" />
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                <!-- Dashboard Tab -->
                <div v-if="activeTab === 'dashboard'">
                    <div
                        class="mb-8 overflow-hidden rounded-3xl border border-border bg-card shadow-sm"
                    >
                        <div
                            class="grid gap-6 bg-[radial-gradient(circle_at_top_right,_rgba(245,158,11,0.16),_transparent_35%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(17,24,39,0.92))] px-6 py-8 sm:px-8 lg:grid-cols-[minmax(0,1fr)_320px]"
                        >
                            <div class="space-y-6">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white/5 text-lg font-semibold text-white shadow-lg"
                                    >
                                        <img
                                            v-if="customer?.profile_photo_url"
                                            :src="customer.profile_photo_url"
                                            :alt="customer?.name || 'Customer avatar'"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>{{ customerInitials }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-500"
                                        >
                                            My Account
                                        </p>
                                        <h1
                                            class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                                        >
                                            Welcome back, {{ customerFirstName }}
                                        </h1>
                                        <p
                                            class="mt-2 max-w-2xl text-sm leading-6 text-white/70"
                                        >
                                            Keep track of your orders, saved
                                            products, invoices, and profile
                                            details from one place.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="grid gap-3 text-sm text-white/75 sm:grid-cols-3"
                                >
                                    <div
                                        class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3"
                                    >
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-white/45">
                                            Email
                                        </p>
                                        <p class="mt-1 truncate font-medium text-white">
                                            {{ customer?.email || "Not available" }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3"
                                    >
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-white/45">
                                            Phone
                                        </p>
                                        <p class="mt-1 font-medium text-white">
                                            {{ customer?.phone || "Add your phone number" }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3"
                                    >
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-white/45">
                                            Last Order
                                        </p>
                                        <p class="mt-1 font-medium text-white">
                                            {{
                                                stats.last_order?.order_number ||
                                                "No orders yet"
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button
                                        v-for="action in quickActions"
                                        :key="action.key"
                                        @click="switchTab(action.key)"
                                        class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition-luxury hover:bg-amber-600 shadow-sm"
                                    >
                                        <component
                                            :is="action.icon"
                                            class="h-4 w-4"
                                        />
                                        {{ action.label }}
                                    </button>
                                </div>
                            </div>

                            <div
                                class="rounded-3xl border border-white/10 bg-white/5 p-5 text-white shadow-lg backdrop-blur"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/15"
                                    >
                                        <Clock class="h-5 w-5 text-amber-500" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50"
                                        >
                                            Pending Orders
                                        </p>
                                        <p class="mt-1 text-3xl font-semibold">
                                            {{ stats.pending_orders }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-6 rounded-2xl border border-white/10 bg-black/10 p-4"
                                >
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">
                                        Last order snapshot
                                    </p>
                                    <div v-if="stats.last_order" class="mt-3 space-y-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold">
                                                    {{ stats.last_order.order_number }}
                                                </p>
                                                <p class="mt-1 text-sm text-white/60">
                                                    {{ stats.last_order.created_at }}
                                                </p>
                                            </div>
                                            <span
                                                class="inline-flex rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300"
                                            >
                                                {{ stats.last_order.status_label }}
                                            </span>
                                        </div>
                                        <button
                                            @click="trackExistingOrder(stats.last_order.order_number)"
                                            class="inline-flex items-center gap-2 text-sm font-medium text-[#F4D777] hover:text-white"
                                        >
                                            <PackageSearch class="h-4 w-4" />
                                            Track this order
                                        </button>
                                    </div>
                                    <p v-else class="mt-3 text-sm text-white/60">
                                        Your latest order will appear here once
                                        you place one.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="card in overviewCards"
                            :key="card.key"
                            class="rounded-2xl border border-border bg-card p-5 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-foreground">
                                        {{ card.label }}
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold text-foreground">
                                        {{ card.value }}
                                    </p>
                                    <p class="mt-2 text-xs text-muted-foreground">
                                        {{ card.helper }}
                                    </p>
                                </div>
                                <div
                                    :class="[
                                        'flex h-11 w-11 items-center justify-center rounded-2xl',
                                        card.iconWrapClass,
                                    ]"
                                >
                                    <component
                                        :is="card.icon"
                                        :class="['h-5 w-5', card.iconClass]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div
                            class="rounded-xl border border-border bg-card p-5 shadow-sm"
                        >
                            <h3
                                class="text-sm font-semibold uppercase tracking-[0.2em] text-muted-foreground mb-4"
                            >
                                Recent Orders
                            </h3>
                            <div v-if="recentOrders.length" class="space-y-3">
                                <div
                                    v-for="order in recentOrders"
                                    :key="order.id"
                                    class="flex items-center justify-between py-2 border-b border-border last:border-0"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-foreground"
                                        >
                                            {{ order.order_number }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ order.created_at }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border"
                                            :class="
                                                getStatusColor(order.status)
                                            "
                                        >
                                            {{ order.status_label }}
                                        </span>
                                        <button
                                            @click="
                                                trackExistingOrder(
                                                    order.order_number,
                                                )
                                            "
                                            class="text-xs text-amber-500 hover:underline font-medium"
                                        >
                                            Track
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p
                                v-else
                                class="text-sm text-muted-foreground text-center py-4"
                            >
                                No orders yet
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-border bg-card p-5 shadow-sm"
                        >
                            <h3
                                class="text-sm font-semibold uppercase tracking-[0.2em] text-muted-foreground mb-4"
                            >
                                Recent Invoices
                            </h3>
                            <div v-if="recentInvoices.length" class="space-y-3">
                                <div
                                    v-for="inv in recentInvoices"
                                    :key="inv.id"
                                    class="flex items-center justify-between py-2 border-b border-border last:border-0"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-foreground"
                                        >
                                            {{ inv.order_number || "—" }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ inv.created_at }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border"
                                            :class="getStatusColor(inv.status)"
                                        >
                                            {{ inv.status_label }}
                                        </span>
                                        <span
                                            class="text-sm font-semibold text-foreground"
                                            >{{ formatPrice(inv.amount) }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <p
                                v-else
                                class="text-sm text-muted-foreground text-center py-4"
                            >
                                No invoices yet
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Orders Tab -->
                <div v-if="activeTab === 'orders'">
                    <h2 class="text-xl font-bold text-foreground mb-6">
                        My Orders
                    </h2>
                    <div
                        v-if="ordersLoading && !orders"
                        class="flex justify-center py-12"
                    >
                        <Loader2 class="w-8 h-8 animate-spin text-amber-500" />
                    </div>
                    <div v-else-if="orders && orders.length" class="space-y-4">
                        <div
                            v-for="order in orders"
                            :key="order.id"
                            class="rounded-xl border border-border bg-card p-5 shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex items-center gap-2 flex-wrap"
                                    >
                                        <p
                                            class="font-semibold text-foreground text-sm sm:text-base"
                                        >
                                            {{ order.order_number }}
                                        </p>
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border"
                                            :class="
                                                getStatusColor(order.status)
                                            "
                                        >
                                            {{ order.status_label }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs text-muted-foreground mt-1"
                                    >
                                        {{ order.created_at }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:justify-end">
                                    <span
                                        class="text-base sm:text-lg font-bold text-foreground"
                                        >{{
                                            formatPrice(order.total_amount)
                                        }}</span
                                    >
                                    <button
                                        @click="
                                            trackExistingOrder(
                                                order.order_number,
                                            )
                                        "
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-amber-500 text-amber-500 hover:bg-amber-500/10 transition-luxury shrink-0"
                                    >
                                        <PackageSearch class="w-3.5 h-3.5" />
                                        Track
                                    </button>
                                </div>
                            </div>
                            <div
                                v-if="order.items && order.items.length"
                                class="mt-4 pt-4 border-t border-border"
                            >
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="flex justify-between text-sm py-1"
                                >
                                    <span class="text-foreground"
                                        >{{ item.product_title }}
                                        <span class="text-muted-foreground"
                                            >×{{ item.quantity }}</span
                                        ></span
                                    >
                                    <span class="text-foreground font-medium">{{
                                        formatPrice(item.subtotal)
                                    }}</span>
                                </div>
                            </div>
                            <div
                                v-if="order.payment"
                                class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <span
                                    >Payment: {{ order.payment.method }} —</span
                                >
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium border"
                                    :class="
                                        getStatusColor(order.payment.status)
                                    "
                                >
                                    {{ order.payment.status_label }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="ordersPagination"
                            class="flex items-center justify-between pt-4"
                        >
                            <p class="text-sm text-muted-foreground">
                                Page {{ ordersPagination.current_page }} of
                                {{ ordersPagination.last_page }}
                            </p>
                            <div class="flex gap-2">
                                <button
                                    v-if="ordersPagination.current_page > 1"
                                    @click="
                                        fetchOrders(
                                            ordersPagination.current_page - 1,
                                        )
                                    "
                                    class="px-3 py-1.5 rounded-lg text-sm border border-border hover:bg-muted transition-luxury text-foreground"
                                >
                                    Previous
                                </button>
                                <button
                                    v-if="
                                        ordersPagination.current_page <
                                        ordersPagination.last_page
                                    "
                                    @click="
                                        fetchOrders(
                                            ordersPagination.current_page + 1,
                                        )
                                    "
                                    class="px-3 py-1.5 rounded-lg text-sm border border-border hover:bg-muted transition-luxury text-foreground"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <ShoppingBag
                            class="w-12 h-12 mx-auto text-muted-foreground mb-3"
                        />
                        <p class="text-muted-foreground">No orders found</p>
                    </div>
                </div>

                <!-- Wishlist Tab -->
                <div v-if="activeTab === 'wishlist'">
                    <h2 class="text-xl font-bold text-foreground mb-6">
                        My Wishlist
                    </h2>
                    <div
                        v-if="wishlistLoading && !wishlistProducts"
                        class="flex justify-center py-12"
                    >
                        <Loader2 class="w-8 h-8 animate-spin text-amber-500" />
                    </div>
                    <div
                        v-else-if="wishlistProducts && wishlistProducts.length"
                        class="grid grid-cols-2 gap-3 md:gap-4 lg:grid-cols-3 xl:grid-cols-4"
                    >
                        <div
                            v-for="product in wishlistProducts"
                            :key="product.id"
                            class="group flex h-full flex-col overflow-hidden rounded-2xl md:rounded-xl border border-border bg-card shadow-luxury md:shadow-sm hover:shadow-luxury-hover transition-luxury"
                        >
                            <Link
                                :href="`/product/${product.slug}`"
                                class="relative block aspect-square overflow-hidden bg-muted"
                            >
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.title"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-luxury group-hover:scale-105"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-muted-foreground text-sm p-4"
                                >
                                    {{ product.title }}
                                </div>

                                <!-- Mobile: circular remove button over the image -->
                                <button
                                    @click.prevent="requestWishlistRemoval(product)"
                                    class="pc-wishlist-btn absolute right-2.5 top-2.5 flex h-10 w-10 items-center justify-center rounded-full border border-border text-red-500 shadow-md transition-transform active:scale-95 md:hidden"
                                    title="Remove from wishlist"
                                    aria-label="Remove from wishlist"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </Link>
                            <div class="flex flex-1 flex-col p-3 md:p-4">
                                <p
                                    v-if="product.category"
                                    class="mb-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground md:text-amber-500"
                                >
                                    {{ product.category }}
                                </p>
                                <Link :href="`/product/${product.slug}`">
                                    <h3
                                        class="line-clamp-2 text-sm leading-5 md:leading-normal font-semibold text-foreground group-hover:text-amber-500 transition-colors"
                                    >
                                        {{ product.title }}
                                    </h3>
                                </Link>
                                <div
                                    class="mt-2 md:mt-3 flex items-center justify-between"
                                >
                                    <span
                                        class="text-base md:text-lg font-bold text-foreground max-md:text-emerald-600 max-md:dark:text-emerald-400"
                                        >{{ formatPrice(product.price) }}</span
                                    >
                                    <button
                                        @click="requestWishlistRemoval(product)"
                                        class="hidden p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-luxury md:block"
                                        title="Remove from wishlist"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                                <Link
                                    :href="`/product/${product.slug}`"
                                    class="mt-auto pt-2.5 md:pt-3 block w-full"
                                >
                                    <span
                                        class="flex h-10 md:h-auto w-full items-center justify-center rounded-full md:rounded-lg md:py-2 text-sm font-semibold md:font-medium bg-amber-500 text-slate-950 hover:bg-amber-600 transition-luxury active:scale-[0.98] md:active:scale-100"
                                    >
                                        View Dish
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <Heart
                            class="w-12 h-12 mx-auto text-muted-foreground mb-3"
                        />
                        <p class="text-muted-foreground">
                            Your wishlist is empty
                        </p>
                        <Link
                            href="/shop"
                            class="mt-3 inline-flex items-center gap-1 text-sm text-amber-500 hover:underline font-medium"
                        >
                            Explore Menu <ChevronRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Invoices Tab -->
                <div v-if="activeTab === 'invoices'">
                    <h2 class="text-xl font-bold text-foreground mb-6">
                        Invoices
                    </h2>
                    <div
                        v-if="invoicesLoading && !invoices"
                        class="flex justify-center py-12"
                    >
                        <Loader2 class="w-8 h-8 animate-spin text-amber-500" />
                    </div>
                    <div
                        v-else-if="invoices && invoices.length"
                        class="space-y-3"
                    >
                        <div
                            v-for="inv in invoices"
                            :key="inv.id"
                            class="rounded-xl border border-border bg-card p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-4">
                                <div>
                                    <p
                                        class="font-semibold text-foreground text-sm"
                                    >
                                        {{ inv.order_number || "—" }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ inv.created_at }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border"
                                    :class="getStatusColor(inv.status)"
                                >
                                    {{ inv.status_label }}
                                </span>
                                <span
                                    class="text-base font-bold text-foreground"
                                    >{{ formatPrice(inv.amount) }}</span
                                >
                                <button
                                    @click="viewInvoice(inv)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium border border-border hover:bg-muted transition-luxury text-foreground"
                                >
                                    <Eye class="w-3.5 h-3.5" />
                                    View
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="invoicesPagination"
                            class="flex items-center justify-between pt-4"
                        >
                            <p class="text-sm text-muted-foreground">
                                Page {{ invoicesPagination.current_page }} of
                                {{ invoicesPagination.last_page }}
                            </p>
                            <div class="flex gap-2">
                                <button
                                    v-if="invoicesPagination.current_page > 1"
                                    @click="
                                        fetchInvoices(
                                            invoicesPagination.current_page - 1,
                                        )
                                    "
                                    class="px-3 py-1.5 rounded-lg text-sm border border-border hover:bg-muted transition-luxury text-foreground"
                                >
                                    Previous
                                </button>
                                <button
                                    v-if="
                                        invoicesPagination.current_page <
                                        invoicesPagination.last_page
                                    "
                                    @click="
                                        fetchInvoices(
                                            invoicesPagination.current_page + 1,
                                        )
                                    "
                                    class="px-3 py-1.5 rounded-lg text-sm border border-border hover:bg-muted transition-luxury text-foreground"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <FileText
                            class="w-12 h-12 mx-auto text-muted-foreground mb-3"
                        />
                        <p class="text-muted-foreground">No invoices found</p>
                    </div>
                </div>

                <!-- Profile Tab -->
                <div v-if="activeTab === 'profile'">
                    <div class="max-w-3xl space-y-8">
                        <div
                            class="rounded-xl border border-border bg-card p-6 shadow-sm"
                        >
                            <div
                                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-foreground"
                                    >
                                        Profile Information
                                    </h3>
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Keep your account details up to date so
                                        order follow-up stays smooth.
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border bg-background px-4 py-3 text-sm"
                                >
                                    <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                        Account email
                                    </p>
                                    <p class="mt-1 font-medium text-foreground">
                                        {{ customer?.email }}
                                    </p>
                                </div>
                            </div>
                            <form
                                @submit.prevent="submitProfile"
                                class="space-y-6"
                            >
                                <div class="space-y-3">
                                    <div class="flex items-center gap-6">
                                        <div class="relative">
                                            <div
                                                class="w-20 h-20 rounded-full overflow-hidden bg-muted border-2 border-border"
                                            >
                                                <img
                                                    v-if="profilePhotoPreview"
                                                    :src="profilePhotoPreview"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full flex items-center justify-center text-muted-foreground"
                                                >
                                                    <User class="w-8 h-8" />
                                                </div>
                                            </div>
                                            <label
                                                class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center cursor-pointer shadow-sm hover:bg-amber-600 transition-luxury"
                                            >
                                                <Camera class="w-3.5 h-3.5" />
                                                <input
                                                    type="file"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    class="hidden"
                                                    @change="onPhotoChange"
                                                />
                                            </label>
                                        </div>
                                        <div>
                                            <p
                                                class="font-semibold text-foreground"
                                            >
                                                {{ customer?.name }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ customer?.email }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {{
                                                    customer?.phone ||
                                                    "Add a phone number for easier contact"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <p
                                        v-if="profileErrors.profile_photo"
                                        class="text-xs text-red-500"
                                    >
                                        {{ profileErrors.profile_photo }}
                                    </p>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-medium text-foreground mb-1.5"
                                            >Full Name</label
                                        >
                                        <input
                                            type="text"
                                            v-model="profileForm.name"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                        />
                                        <p
                                            v-if="profileErrors.name"
                                            class="mt-1 text-xs text-red-500"
                                        >
                                            {{ profileErrors.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-foreground mb-1.5"
                                            >Phone Number</label
                                        >
                                        <input
                                            type="text"
                                            v-model="profileForm.phone"
                                            placeholder="+92 300 1234567"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                        />
                                        <p
                                            v-if="profileErrors.phone"
                                            class="mt-1 text-xs text-red-500"
                                        >
                                            {{ profileErrors.phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-foreground mb-1.5"
                                            >Email</label
                                        >
                                        <input
                                            type="email"
                                            :value="profileForm.email"
                                            disabled
                                            class="w-full cursor-not-allowed rounded-lg border border-border bg-slate-900/70 px-4 py-2.5 text-sm text-slate-300 opacity-100"
                                        />
                                        <p
                                            class="text-xs text-muted-foreground mt-1"
                                        >
                                            Email cannot be changed from this
                                            screen.
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col gap-3 border-t border-border pt-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Profile photo should be JPG, PNG, or
                                        WEBP and up to 2MB.
                                    </p>
                                    <button
                                        type="submit"
                                        :disabled="
                                            profileSubmitting || !isProfileDirty
                                        "
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-2.5 font-bold text-slate-950 transition-luxury hover:bg-amber-600 disabled:opacity-70 shadow-sm"
                                    >
                                        <Loader2
                                            v-if="profileSubmitting"
                                            class="w-4 h-4 animate-spin"
                                        />
                                        {{
                                            profileSubmitting
                                                ? "Saving..."
                                                : "Save Changes"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div
                            class="rounded-xl border border-border bg-card p-6 shadow-sm"
                        >
                            <h3
                                class="text-lg font-semibold text-foreground mb-6"
                            >
                                Change Password
                            </h3>
                            <form
                                @submit.prevent="submitPassword"
                                class="space-y-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium text-foreground mb-1.5"
                                        >Current Password</label
                                    >
                                    <input
                                        type="password"
                                        v-model="passwordForm.current_password"
                                        class="public-field w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                    />
                                    <p
                                        v-if="passwordErrors.current_password"
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{ passwordErrors.current_password }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-foreground mb-1.5"
                                        >New Password</label
                                    >
                                    <input
                                        type="password"
                                        v-model="passwordForm.new_password"
                                        class="public-field w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                    />
                                    <p
                                        v-if="passwordErrors.new_password"
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{ passwordErrors.new_password }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-foreground mb-1.5"
                                        >Confirm New Password</label
                                    >
                                    <input
                                        type="password"
                                        v-model="
                                            passwordForm.new_password_confirmation
                                        "
                                        class="public-field w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                    />
                                    <p
                                        v-if="
                                            passwordErrors.new_password_confirmation
                                        "
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{
                                            passwordErrors.new_password_confirmation
                                        }}
                                    </p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="passwordSubmitting"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-600 transition-luxury disabled:opacity-70 shadow-sm"
                                >
                                    <Loader2
                                        v-if="passwordSubmitting"
                                        class="w-4 h-4 animate-spin"
                                    />
                                    {{
                                        passwordSubmitting
                                            ? "Updating..."
                                            : "Update Password"
                                    }}
                                </button>
                            </form>
                        </div>

                        <div
                            class="rounded-xl border border-border bg-card p-6 shadow-sm"
                        >
                            <h3
                                class="text-lg font-semibold text-foreground mb-2"
                            >
                                Forgot Password?
                            </h3>
                            <p class="text-sm text-muted-foreground mb-4">
                                Enter your email address and we'll send you a
                                password reset link.
                            </p>
                            <div v-if="!forgotPasswordSent">
                                <div class="flex gap-3 max-w-md">
                                    <input
                                        type="email"
                                        v-model="forgotPasswordEmail"
                                        placeholder="Your email address"
                                        class="public-field flex-1 rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                    />
                                    <button
                                        @click="sendForgotPassword"
                                        :disabled="forgotPasswordSending"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-sm hover:bg-amber-600 transition-luxury disabled:opacity-70 whitespace-nowrap shadow-sm"
                                    >
                                        <Loader2
                                            v-if="forgotPasswordSending"
                                            class="w-4 h-4 animate-spin"
                                        />
                                        Send Reset Link
                                    </button>
                                </div>
                            </div>
                            <div
                                v-else
                                class="text-sm text-green-600 dark:text-green-400"
                            >
                                Password reset link sent! Please check your
                                email.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Explore Tab -->
                <div v-if="activeTab === 'explore'">
                    <div class="text-center py-12">
                        <Compass
                            class="w-16 h-16 mx-auto text-amber-500 mb-4"
                        />
                        <h2 class="text-2xl font-bold text-foreground mb-2">
                            Explore Chef's Menu
                        </h2>
                        <p class="text-muted-foreground mb-6 max-w-md mx-auto">
                            Browse our delectable chef menu of sizzling burgers, flame-grilled steaks, and artisan pizzas.
                        </p>
                        <Link
                            href="/shop"
                            class="inline-flex items-center gap-2 px-8 py-3 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-600 transition-luxury shadow-sm"
                        >
                            <ShoppingBag class="w-5 h-5" />
                            Explore Menu
                        </Link>
                    </div>
                </div>

                <!-- Track Order Tab -->
                <div v-if="activeTab === 'track'">
                    <h2 class="text-xl font-bold text-foreground mb-6">
                        Track Your Order
                    </h2>
                    <div class="max-w-3xl">
                        <div
                            class="rounded-xl border border-border bg-card p-6 shadow-sm mb-6"
                        >
                            <form
                                @submit.prevent="trackOrderLookup"
                                class="flex flex-col sm:flex-row gap-3"
                            >
                                <input
                                    type="text"
                                    v-model="orderNumberInput"
                                    placeholder="Enter order number (e.g. ORD-20260516-AB12)"
                                    class="public-field flex-1 rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-950"
                                />
                                <button
                                    type="submit"
                                    :disabled="trackingLoading"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-amber-500 text-slate-950 font-bold hover:bg-amber-600 transition-luxury disabled:opacity-70 whitespace-nowrap shadow-sm"
                                >
                                    <Loader2
                                        v-if="trackingLoading"
                                        class="w-4 h-4 animate-spin"
                                    />
                                    {{
                                        trackingLoading
                                            ? "Tracking..."
                                            : "Track Order"
                                    }}
                                </button>
                            </form>
                            <p
                                v-if="trackingError"
                                class="mt-3 text-sm text-red-500"
                            >
                                {{ trackingError }}
                            </p>
                        </div>

                        <div v-if="trackingOrder" class="space-y-6">
                            <div
                                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <div
                                    class="rounded-xl border border-border bg-card p-4 shadow-sm"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                    >
                                        Order Number
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{ trackingOrder.order_number }}
                                    </p>
                                </div>
                                <div
                                    :class="[
                                        'rounded-xl border p-4 shadow-sm',
                                        getStatusColor(trackingOrder.status),
                                    ]"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                    >
                                        Status
                                    </p>
                                    <p class="mt-2 text-sm font-semibold">
                                        {{ trackingOrder.status_label }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-border bg-card p-4 shadow-sm"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                    >
                                        Placed On
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{ trackingOrder.placed_at }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl border border-border bg-card p-4 shadow-sm"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                    >
                                        Total
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{
                                            formatPrice(
                                                trackingOrder.total_amount,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="trackingOrder.timeline"
                                class="rounded-xl border border-border bg-card p-6 shadow-sm"
                            >
                                <h3
                                    class="text-sm font-semibold text-foreground mb-4"
                                >
                                    Status Timeline
                                </h3>
                                <div
                                    v-for="(
                                        step, idx
                                    ) in trackingOrder.timeline"
                                    :key="idx"
                                    class="flex items-start gap-3 relative"
                                >
                                    <div
                                        class="relative flex flex-col items-center"
                                    >
                                        <span
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-full border text-xs font-bold relative z-10"
                                            :class="
                                                step.completed
                                                    ? 'border-amber-500 bg-amber-500/15 text-amber-500'
                                                    : 'border-border bg-muted text-muted-foreground'
                                            "
                                        >
                                            {{ idx + 1 }}
                                        </span>
                                        <div
                                            v-if="
                                                idx <
                                                trackingOrder.timeline.length -
                                                    1
                                            "
                                            class="w-0.5 h-10"
                                            :class="
                                                trackingOrder.timeline[idx + 1]
                                                    .completed
                                                    ? 'bg-amber-500'
                                                    : 'bg-border'
                                            "
                                        ></div>
                                    </div>
                                    <p
                                        class="text-sm pt-1"
                                        :class="
                                            step.current
                                                ? 'font-semibold text-foreground'
                                                : 'text-muted-foreground'
                                        "
                                    >
                                        {{ step.label }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 lg:grid-cols-2">
                                <div
                                    v-if="
                                        trackingOrder.items &&
                                        trackingOrder.items.length
                                    "
                                    class="rounded-xl border border-border bg-card p-6 shadow-sm"
                                >
                                    <h3
                                        class="text-sm font-semibold text-foreground mb-4"
                                    >
                                        Order Items
                                    </h3>
                                    <div class="space-y-3">
                                        <div
                                            v-for="item in trackingOrder.items"
                                            :key="item.id"
                                            class="rounded-lg border border-border bg-background p-3"
                                        >
                                            <div
                                                class="flex justify-between gap-3"
                                            >
                                                <p
                                                    class="text-sm font-medium text-foreground"
                                                >
                                                    {{ item.title }}
                                                </p>
                                                <p
                                                    class="text-sm font-semibold text-foreground"
                                                >
                                                    {{
                                                        formatPrice(
                                                            item.subtotal,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <p
                                                class="text-xs text-muted-foreground mt-1"
                                            >
                                                {{ item.quantity }} ×
                                                {{ formatPrice(item.price) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="trackingOrder.payment"
                                    class="rounded-xl border border-border bg-card p-6 shadow-sm"
                                >
                                    <h3
                                        class="text-sm font-semibold text-foreground mb-4"
                                    >
                                        Payment Information
                                    </h3>
                                    <div class="space-y-3">
                                        <div
                                            class="rounded-lg border border-border bg-background p-3 text-sm flex justify-between"
                                        >
                                            <span class="text-muted-foreground"
                                                >Method</span
                                            >
                                            <span
                                                class="font-medium text-foreground capitalize"
                                                >{{
                                                    trackingOrder.payment.method
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            :class="[
                                                'rounded-lg border p-3 text-sm flex justify-between',
                                                getStatusColor(
                                                    trackingOrder.payment
                                                        .status,
                                                ),
                                            ]"
                                        >
                                            <span class="text-muted-foreground"
                                                >Status</span
                                            >
                                            <span class="font-medium">{{
                                                trackingOrder.payment
                                                    .status_label
                                            }}</span>
                                        </div>
                                        <div
                                            class="rounded-lg border border-border bg-background p-3 text-sm flex justify-between"
                                        >
                                            <span class="text-muted-foreground"
                                                >Amount</span
                                            >
                                            <span
                                                class="font-medium text-foreground"
                                                >{{
                                                    formatPrice(
                                                        trackingOrder.payment
                                                            .amount,
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Detail Modal -->
        <Teleport to="body">
            <div
                v-if="invoiceModalOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click.self="invoiceModalOpen = false"
            >
                <div
                    class="w-full max-w-lg rounded-xl border border-border bg-card shadow-luxury p-6 relative custom-fade-in-up"
                >
                    <button
                        @click="invoiceModalOpen = false"
                        class="absolute top-4 right-4 p-1.5 rounded-lg text-muted-foreground hover:bg-muted transition-luxury"
                    >
                        <X class="w-5 h-5" />
                    </button>
                    <h3 class="text-lg font-bold text-foreground mb-6">
                        Invoice Details
                    </h3>
                    <div v-if="selectedInvoice" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Order
                                </p>
                                <p
                                    class="text-sm font-semibold text-foreground mt-0.5"
                                >
                                    {{ selectedInvoice.order_number || "—" }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Amount
                                </p>
                                <p
                                    class="text-sm font-semibold text-foreground mt-0.5"
                                >
                                    {{ formatPrice(selectedInvoice.amount) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Payment Method
                                </p>
                                <p
                                    class="text-sm font-semibold text-foreground mt-0.5 capitalize"
                                >
                                    {{ selectedInvoice.method }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Status
                                </p>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border mt-0.5"
                                    :class="
                                        getStatusColor(selectedInvoice.status)
                                    "
                                >
                                    {{ selectedInvoice.status_label }}
                                </span>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Date
                                </p>
                                <p
                                    class="text-sm font-semibold text-foreground mt-0.5"
                                >
                                    {{ selectedInvoice.created_at }}
                                </p>
                            </div>
                            <div v-if="selectedInvoice.order_status">
                                <p
                                    class="text-xs text-muted-foreground uppercase tracking-wider"
                                >
                                    Order Status
                                </p>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium border mt-0.5"
                                    :class="
                                        getStatusColor(
                                            selectedInvoice.order_status?.toLowerCase(),
                                        )
                                    "
                                >
                                    {{ selectedInvoice.order_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Logout Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="confirmLogout"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click.self="confirmLogout = false"
            >
                <div
                    class="w-full max-w-sm rounded-xl border border-border bg-card shadow-luxury p-6 relative custom-fade-in-up"
                >
                    <h3 class="text-lg font-bold text-foreground mb-2">
                        Sign Out
                    </h3>
                    <p class="text-sm text-muted-foreground mb-6">
                        Are you sure you want to sign out of your account?
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="confirmLogout = false"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-border text-foreground font-medium hover:bg-muted transition-luxury text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleLogout"
                            class="flex-1 px-4 py-2.5 rounded-lg bg-red-500 text-white font-medium hover:bg-red-600 transition-luxury text-sm"
                        >
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="confirmingWishlistRemoval"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click.self="closeWishlistRemovalConfirmation"
            >
                <div
                    class="w-full max-w-sm rounded-xl border border-border bg-card shadow-luxury p-6 relative custom-fade-in-up"
                >
                    <h3 class="text-lg font-bold text-foreground mb-2">
                        Remove Wishlist Item
                    </h3>
                    <p class="text-sm text-muted-foreground mb-6">
                        Remove
                        <span class="font-medium text-foreground">
                            {{ wishlistItemToRemove?.title || "this item" }}
                        </span>
                        from your wishlist?
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="closeWishlistRemovalConfirmation"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-border text-foreground font-medium hover:bg-muted transition-luxury text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            @click="removeFromWishlist"
                            class="flex-1 px-4 py-2.5 rounded-lg bg-red-500 text-white font-medium hover:bg-red-600 transition-luxury text-sm"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </PublicLayout>
</template>
