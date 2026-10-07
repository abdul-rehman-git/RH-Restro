<script setup>
import { Link } from '@inertiajs/vue3';
import FieldInput from '@/Components/ui/field-input.vue';
import FieldTextarea from '@/Components/ui/field-textarea.vue';
import FieldSelect from '@/Components/ui/field-select.vue';
import ImageUploader from '@/Components/ui/image-uploader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    products: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Save' },
    onSubmit: { type: Function, required: true },
});

const productOptions = props.products.map(p => ({
    value: p.id,
    label: p.title,
}));

const booleanOptions = [
    { value: '1', label: 'Yes' },
    { value: '0', label: 'No' },
];

const removeExistingImage = (index) => {
    props.form.existing_images.splice(index, 1);
};
</script>

<template>
    <form @submit.prevent="onSubmit">
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Review Information
                    </h3>
                    <div class="space-y-4">
                        <FieldSelect
                            name="product_id"
                            label="Product"
                            v-model="form.product_id"
                            :error="form.errors.product_id"
                            :options="productOptions"
                            placeholder="Select product"
                        />

                        <FieldTextarea
                            name="comment"
                            label="Comment"
                            v-model="form.comment"
                            :error="form.errors.comment"
                            :rows="4"
                            placeholder="Enter review comment"
                        />

                        <FieldSelect
                            name="rating"
                            label="Rating"
                            v-model="form.rating"
                            :error="form.errors.rating"
                            :options="[
                                { value: '1', label: '1 Star' },
                                { value: '2', label: '2 Stars' },
                                { value: '3', label: '3 Stars' },
                                { value: '4', label: '4 Stars' },
                                { value: '5', label: '5 Stars' },
                            ]"
                            placeholder="Select rating"
                        />

                        <FieldInput
                            name="reviewer_name"
                            label="Reviewer Name"
                            v-model="form.reviewer_name"
                            :error="form.errors.reviewer_name"
                            placeholder="Customer name"
                        />

                        <div class="grid sm:grid-cols-2 gap-4">
                            <FieldInput
                                name="reviewed_at"
                                label="Reviewed At"
                                type="date"
                                v-model="form.reviewed_at"
                                :error="form.errors.reviewed_at"
                            />

                            <FieldInput
                                name="helpful_count"
                                label="Helpful Count"
                                type="number"
                                v-model="form.helpful_count"
                                :error="form.errors.helpful_count"
                                placeholder="0"
                            />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Review Images
                    </h3>
                    <ImageUploader
                        :existingImages="form.existing_images || []"
                        v-model="form.images"
                        :error="form.errors.images"
                        :maxFiles="5"
                        maxFileSize="3MB"
                        @remove-existing="removeExistingImage"
                    />
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Status
                    </h3>
                    <FieldSelect
                        name="is_approved"
                        label="Approved"
                        v-model="form.is_approved"
                        :error="form.errors.is_approved"
                        :options="booleanOptions"
                    />

                    <FieldSelect
                        name="is_featured"
                        label="Featured"
                        v-model="form.is_featured"
                        :error="form.errors.is_featured"
                        :options="booleanOptions"
                    />

                    <FieldSelect
                        name="is_verified_purchase"
                        label="Verified Purchase"
                        v-model="form.is_verified_purchase"
                        :error="form.errors.is_verified_purchase"
                        :options="booleanOptions"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <PrimaryButton type="submit" :disabled="form.processing">
                        {{ submitLabel }}
                    </PrimaryButton>
                    <Link :href="route('admin-reviews.index')">
                        <SecondaryButton type="button">Cancel</SecondaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </form>
</template>
