<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewForm from './Partials/ReviewForm.vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
});

const form = useForm({
    product_id: '',
    reviewer_name: '',
    comment: '',
    rating: '5',
    images: [],
    existing_images: [],
    reviewed_at: '',
    helpful_count: 0,
    is_featured: '0',
    is_approved: '1',
    is_verified_purchase: '0',
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            existing_images: data.existing_images.map((image) => image.path ?? image),
        }))
        .post(route('admin-reviews.store'));
};
</script>

<template>
    <AuthenticatedLayout header="Create Review">
        <Head title="Create Review" />

        <div class="max-w-4xl">
            <ReviewForm
                :form="form"
                :products="products"
                submitLabel="Create Review"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
