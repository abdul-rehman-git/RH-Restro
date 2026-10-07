<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from './Partials/ProductForm.vue';

const props = defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    title: props.product.title ?? '',
    description: props.product.description ?? '',
    price: props.product.price ?? '',
    compare_price: props.product.compare_price ?? '',
    category_id: props.product.category_id ?? '',
    stock_quantity: props.product.stock_quantity ?? 0,
    sort_order: props.product.sort_order ?? 0,
    is_active: props.product.is_active ? '1' : '0',
    is_featured: props.product.is_featured ? '1' : '0',
    images: [],
    existing_images: props.product.images || [],
    variants: (props.product.variants || []).map(v => ({
        id: v.id,
        name: v.name || '',
        sku: v.sku || '',
        price: v.price || '',
        compare_price: v.compare_price || '',
        stock_quantity: v.stock_quantity || 0,
        image: v.image || '',
        image_preview: v.image || '',
        image_file: null,
    })),
    _method: 'put',
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            existing_images: data.existing_images.map((image) => image.path ?? image),
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
        .post(route('products.update', props.product.id));
};
</script>

<template>
    <AuthenticatedLayout header="Edit Product">
        <Head title="Edit Product" />

        <div class="max-w-4xl">
            <ProductForm
                :form="form"
                :categories="categories"
                submitLabel="Update Product"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
