<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    description: '',
    price: '',
    compare_price: '',
    category_id: '',
    stock_quantity: 0,
    sort_order: 0,
    is_active: '1',
    is_featured: '0',
    images: [],
    existing_images: [],
    variants: [],
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            variants: (data.variants || []).map((v) => {
                const item = {
                    id: v.id || null,
                    name: v.name,
                    sku: v.sku || null,
                    price: v.price,
                    compare_price: v.compare_price || null,
                    stock_quantity: v.stock_quantity || 0,
                    image: v.image || null,
                };
                if (v.image_file instanceof File) {
                    item.image_file = v.image_file;
                }
                return item;
            }),
        }))
        .post(route('products.store'));
};
</script>

<template>
    <AuthenticatedLayout header="Create Product">
        <Head title="Create Product" />

        <div class="max-w-4xl">
            <ProductForm
                :form="form"
                :categories="categories"
                submitLabel="Create Product"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
