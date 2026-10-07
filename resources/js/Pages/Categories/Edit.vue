<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryForm from './Partials/CategoryForm.vue';

const props = defineProps({
    category: { type: Object, required: true },
});

const form = useForm({
    name: props.category.name ?? '',
    slug: '',
    description: props.category.description ?? '',
    sort_order: props.category.sort_order ?? 0,
    is_active: props.category.is_active ? '1' : '0',
    is_featured: props.category.is_featured ? '1' : '0',
    image: [],
    banner_image: [],
    existing_image: props.category.image_url ? [{ url: props.category.image_url, name: props.category.name }] : [],
    existing_banner_image: props.category.banner_image_url
        ? [{ url: props.category.banner_image_url, name: `${props.category.name} banner` }]
        : [],
    remove_image: '0',
    remove_banner_image: '0',
    _method: 'put',
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
        .post(route('categories.update', props.category.id), {
            forceFormData: true,
        });
};
</script>

<template>
    <AuthenticatedLayout header="Edit Category">
        <Head title="Edit Category" />

        <div class="max-w-4xl">
            <CategoryForm
                :form="form"
                submitLabel="Update Category"
                :onSubmit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>
