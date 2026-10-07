<script setup>
import { Link } from '@inertiajs/vue3';
import FieldInput from '@/Components/ui/field-input.vue';
import FieldTextarea from '@/Components/ui/field-textarea.vue';
import FieldSelect from '@/Components/ui/field-select.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ImageUploader from '@/Components/ui/image-uploader.vue';

const props = defineProps({
    form: { type: Object, required: true },
    submitLabel: { type: String, default: 'Save' },
    onSubmit: { type: Function, required: true },
});

const booleanOptions = [
    { value: '1', label: 'Yes' },
    { value: '0', label: 'No' },
];

const statusOptions = [
    { value: '1', label: 'Active' },
    { value: '0', label: 'Inactive' },
];

const removeExistingImage = () => {
    props.form.existing_image = [];
    props.form.remove_image = '1';
};

const removeExistingBannerImage = () => {
    props.form.existing_banner_image = [];
    props.form.remove_banner_image = '1';
};
</script>

<template>
    <form @submit.prevent="onSubmit">
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Basic Information
                    </h3>
                    <div class="space-y-4">
                        <FieldInput name="name" label="Category Name" v-model="form.name" :error="form.errors.name || form.errors.slug"
                            placeholder="Enter category name" />


                        <FieldTextarea name="description" label="Description" v-model="form.description"
                            :error="form.errors.description" :rows="4" placeholder="Enter category description" />
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Category Image
                    </h3>
                    <ImageUploader :existingImages="form.existing_image || []" v-model="form.image"
                        :error="form.errors.image" :maxFiles="1" @remove-existing="removeExistingImage" />
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Category Banner Image
                    </h3>
                    <ImageUploader
                        :existingImages="form.existing_banner_image || []"
                        v-model="form.banner_image"
                        :error="form.errors.banner_image"
                        :maxFiles="1"
                        @remove-existing="removeExistingBannerImage"
                    />
                </div>
            </div>

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Status
                    </h3>
                    <FieldInput name="sort_order" label="Sort Order" type="number" v-model="form.sort_order"
                        :error="form.errors.sort_order" placeholder="0" />

                    <FieldSelect name="is_active" label="Status" v-model="form.is_active" :error="form.errors.is_active"
                        :options="statusOptions" />

                    <FieldSelect name="is_featured" label="Featured" v-model="form.is_featured"
                        :error="form.errors.is_featured" :options="booleanOptions" />
                </div>

                <div class="flex items-center gap-3">
                    <PrimaryButton type="submit" :disabled="form.processing">
                        {{ submitLabel }}
                    </PrimaryButton>
                    <Link :href="route('categories.index')">
                        <SecondaryButton type="button">Cancel</SecondaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </form>
</template>
