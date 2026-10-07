<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReviewForm from './Partials/ReviewForm.vue';

const props = defineProps({
    review: { type: Object, required: true },
    products: { type: Array, default: () => [] },
});

const form = useForm({
    product_id: props.review.product_id ?? '',
    reviewer_name: props.review.reviewer_name ?? '',
    comment: props.review.comment ?? '',
    rating: props.review.rating ?? '5',
    images: [],
    existing_images: props.review.images || [],
    reviewed_at: props.review.reviewed_at ?? '',
    helpful_count: props.review.helpful_count ?? 0,
    is_featured: props.review.is_featured ? '1' : '0',
    is_approved: props.review.is_approved ? '1' : '0',
    is_verified_purchase: props.review.is_verified_purchase ? '1' : '0',
    _method: 'put',
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            existing_images: data.existing_images.map((image) => image.path ?? image),
        }))
        .post(route('admin-reviews.update', props.review.id));
};
</script>

<template>
    <AuthenticatedLayout header="Edit Review">
        <Head title="Edit Review" />

        <div class="max-w-4xl">
            <ReviewForm
                :form="form"
                :products="products"
                submitLabel="Update Review"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
