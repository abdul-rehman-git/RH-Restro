<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import FieldInput from '@/Components/ui/field-input.vue';
import FieldTextarea from '@/Components/ui/field-textarea.vue';
import FieldSelect from '@/Components/ui/field-select.vue';
import ImageUploader from '@/Components/ui/image-uploader.vue';
import { Layers, Plus, Trash2, Upload, X, Tag, DollarSign, Package, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Save' },
    onSubmit: { type: Function, required: true },
});

const categoryOptions = props.categories.map(cat => ({
    value: cat.id,
    label: cat.name,
}));

const booleanOptions = [
    { value: '1', label: 'Yes' },
    { value: '0', label: 'No' },
];

const statusOptions = [
    { value: '1', label: 'Active' },
    { value: '0', label: 'Inactive' },
];

const removeExistingImage = (index) => {
    props.form.existing_images.splice(index, 1);
};

const addVariant = () => {
    if (!Array.isArray(props.form.variants)) {
        props.form.variants = [];
    }
    props.form.variants.push({
        id: null,
        name: '',
        price: props.form.price || '',
        compare_price: props.form.compare_price || '',
        stock_quantity: props.form.stock_quantity || 0,
        sku: '',
        image: '',
        image_preview: '',
        image_file: null,
    });
};

const removeVariant = (index) => {
    if (Array.isArray(props.form.variants)) {
        props.form.variants.splice(index, 1);
    }
};

const handleVariantImageChange = (index, event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    const variant = props.form.variants[index];
    if (variant) {
        variant.image_file = file;
        variant.image_preview = URL.createObjectURL(file);
    }
};

const clearVariantImage = (index) => {
    const variant = props.form.variants[index];
    if (variant) {
        variant.image_file = null;
        variant.image_preview = '';
        variant.image = '';
    }
};
</script>

<template>
    <form @submit.prevent="onSubmit">
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <Tag class="w-5 h-5 text-amber-500" />
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Basic Information
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <FieldInput
                            name="title"
                            label="Product Title"
                            v-model="form.title"
                            :error="form.errors.title"
                            placeholder="Enter product title"
                        />

                        <FieldTextarea
                            name="description"
                            label="Description"
                            v-model="form.description"
                            :error="form.errors.description"
                            :rows="4"
                            placeholder="Enter product description"
                        />

                        <div class="grid sm:grid-cols-2 gap-4">
                            <FieldInput
                                name="price"
                                label="Base Price ($)"
                                type="number"
                                step="0.01"
                                v-model="form.price"
                                :error="form.errors.price"
                                placeholder="0.00"
                            />

                            <FieldInput
                                name="compare_price"
                                label="Compare Price ($)"
                                type="number"
                                step="0.01"
                                v-model="form.compare_price"
                                :error="form.errors.compare_price"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <FieldSelect
                                name="category_id"
                                label="Category"
                                v-model="form.category_id"
                                :error="form.errors.category_id"
                                :options="categoryOptions"
                                placeholder="Select category"
                            />

                            <FieldInput
                                name="stock_quantity"
                                label="Stock Quantity"
                                type="number"
                                v-model="form.stock_quantity"
                                :error="form.errors.stock_quantity"
                                placeholder="0"
                            />
                        </div>
                    </div>
                </div>

                <!-- Product Variants Section -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <Layers class="w-5 h-5 text-amber-500" />
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        Product Variants
                                    </h3>
                                    <span v-if="form.variants && form.variants.length > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-500">
                                        {{ form.variants.length }} {{ form.variants.length === 1 ? 'Variant' : 'Variants' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Configure unique variant names, custom prices, and variant-specific gallery images.
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="addVariant"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 bg-slate-100 hover:bg-amber-500 hover:text-black dark:bg-slate-800 dark:hover:bg-amber-500 dark:hover:text-black transition-all shadow-sm border border-slate-200 dark:border-slate-700"
                        >
                            <Plus class="w-4 h-4" /> Add Variant
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!form.variants || form.variants.length === 0" class="rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8 text-center bg-slate-50/50 dark:bg-slate-800/20">
                        <Layers class="w-10 h-10 mx-auto text-slate-400 dark:text-slate-600 mb-2" />
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">No product variants configured</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                            If this product comes in different editions, colors, or materials, add variants so customers can choose on the detail page.
                        </p>
                        <button
                            type="button"
                            @click="addVariant"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-amber-500 text-slate-950 hover:bg-amber-400 transition-all shadow-md"
                        >
                            <Plus class="w-4 h-4" /> Create First Variant
                        </button>
                    </div>

                    <!-- Variants List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="(variant, index) in form.variants"
                            :key="variant.id || 'variant-' + index"
                            class="group relative rounded-xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/60 transition-all hover:border-amber-500/50 space-y-4"
                        >
                            <!-- Variant Card Header -->
                            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-slate-950 text-xs font-bold shadow-xs">
                                        {{ index + 1 }}
                                    </span>
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                        {{ variant.name || 'New Variant' }}
                                    </span>
                                    <span v-if="variant.price" class="text-xs px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 font-semibold border border-emerald-500/20">
                                        ${{ Number(variant.price).toFixed(2) }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeVariant(index)"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-red-500 hover:text-red-700 bg-red-500/10 hover:bg-red-500/20 px-2.5 py-1 rounded-lg transition-colors"
                                    title="Remove Variant"
                                >
                                    <Trash2 class="w-3.5 h-3.5" /> Remove
                                </button>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Variant Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="variant.name"
                                        placeholder="e.g. Regular / Large / Spicy Feast"
                                        class="w-full text-sm rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 transition-all"
                                        required
                                    />
                                    <span v-if="form.errors[`variants.${index}.name`]" class="text-xs text-red-500 mt-1 block">
                                        {{ form.errors[`variants.${index}.name`] }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">SKU Code (Optional)</label>
                                    <input
                                        type="text"
                                        v-model="variant.sku"
                                        placeholder="e.g. DISH-REG-01"
                                        class="w-full text-sm rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 transition-all"
                                    />
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Variant Price ($) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            step="0.01"
                                            v-model="variant.price"
                                            placeholder="0.00"
                                            class="w-full text-sm rounded-xl border border-slate-300 bg-white pl-3.5 pr-3 py-2 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 transition-all"
                                            required
                                        />
                                    </div>
                                    <span v-if="form.errors[`variants.${index}.price`]" class="text-xs text-red-500 mt-1 block">
                                        {{ form.errors[`variants.${index}.price`] }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Compare Price ($)</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="variant.compare_price"
                                        placeholder="0.00"
                                        class="w-full text-sm rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 transition-all"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Stock Quantity</label>
                                    <input
                                        type="number"
                                        v-model="variant.stock_quantity"
                                        placeholder="0"
                                        class="w-full text-sm rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 transition-all"
                                    />
                                </div>
                            </div>

                            <!-- Variant Image Picker -->
                            <div class="pt-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Variant Specific Image</label>
                                <div class="flex items-center gap-3">
                                    <div v-if="variant.image_preview || variant.image" class="relative group/img w-16 h-16 rounded-xl overflow-hidden border-2 border-amber-500 shadow-sm flex-shrink-0">
                                        <img :src="variant.image_preview || variant.image" alt="Variant Preview" class="w-full h-full object-cover" />
                                        <button
                                            type="button"
                                            @click="clearVariantImage(index)"
                                            class="absolute top-1 right-1 bg-black/80 hover:bg-red-600 text-white rounded-full p-1 transition-all"
                                            title="Remove Image"
                                        >
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:border-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 transition-all shadow-xs">
                                        <Upload class="w-4 h-4 mr-2 text-amber-500" />
                                        {{ (variant.image_preview || variant.image) ? 'Change Variant Image' : 'Upload Variant Image' }}
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleVariantImageChange(index, $event)"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Gallery Images -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        Product Gallery Images
                    </h3>
                    <ImageUploader
                        :existingImages="form.existing_images || []"
                        v-model="form.images"
                        :error="form.errors.images"
                        @remove-existing="removeExistingImage"
                    />
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 pb-3 border-b border-slate-100 dark:border-slate-800">
                        Publishing & Status
                    </h3>

                    <FieldInput
                        name="sort_order"
                        label="Sort Order"
                        type="number"
                        v-model="form.sort_order"
                        :error="form.errors.sort_order"
                        placeholder="0"
                    />

                    <FieldSelect
                        name="is_active"
                        label="Status"
                        v-model="form.is_active"
                        :error="form.errors.is_active"
                        :options="statusOptions"
                    />

                    <FieldSelect
                        name="is_featured"
                        label="Featured Product"
                        v-model="form.is_featured"
                        :error="form.errors.is_featured"
                        :options="booleanOptions"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <PrimaryButton type="submit" class="w-full justify-center py-3" :disabled="form.processing">
                        {{ submitLabel }}
                    </PrimaryButton>
                    <Link :href="route('products.index')">
                        <SecondaryButton type="button" class="py-3">Cancel</SecondaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </form>
</template>
