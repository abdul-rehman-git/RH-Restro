<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryForm from './Partials/CategoryForm.vue';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    sort_order: 0,
    is_active: '1',
    is_featured: '0',
    image: [],
    banner_image: [],
    existing_image: [],
    existing_banner_image: [],
    remove_image: '0',
    remove_banner_image: '0',
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            image: data.image[0] ?? null,
            banner_image: data.banner_image[0] ?? null,
            slug: '',
            remove_image: data.image.length > 0 ? '0' : data.remove_image,
            remove_banner_image: data.banner_image.length > 0 ? '0' : data.remove_banner_image,
        }))
        .post(route('categories.store'), {
            forceFormData: true,
        });
};
</script>

<template>
    <AuthenticatedLayout header="Create Category">
        <Head title="Create Category" />

        <div class="max-w-4xl">
            <CategoryForm
                :form="form"
                submitLabel="Create Category"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
