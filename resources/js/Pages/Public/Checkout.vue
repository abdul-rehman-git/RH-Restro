<script setup>
import { usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from '@/public/components/Button.vue';
import SeoHead from '@/Components/SeoHead.vue';

const page = usePage();
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-8">
                Checkout
            </h1>

            <div class="grid lg:grid-cols-3 gap-6">
                <div v-stagger="{ preset: 'fadeUp', stagger: 100, duration: 620 }" class="lg:col-span-2 space-y-6">
                    <div class="rounded-xl border border-border bg-card p-6">
                        <h2 class="text-xl font-semibold text-foreground mb-4">Shipping Information</h2>
                        <p class="text-muted-foreground">Shipping form will be here</p>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-6">
                        <h2 class="text-xl font-semibold text-foreground mb-4">Payment Information</h2>
                        <p class="text-muted-foreground">Payment form will be here</p>
                    </div>
                </div>

                <div v-reveal="{ preset: 'fadeLeft', duration: 650 }" class="h-fit rounded-xl border border-border bg-card p-6">
                    <h2 class="text-xl font-semibold text-foreground mb-4">Order Summary</h2>
                    <p class="text-muted-foreground mb-4">Order summary will be here</p>
                    <Button size="lg" class="w-full">Place Order</Button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
