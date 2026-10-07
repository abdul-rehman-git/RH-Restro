<script setup>
import { ref, computed } from 'vue';
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';

const FilePond = vueFilePond(
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview
);

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    existingImages: {
        type: Array,
        default: () => [],
    },
    maxFiles: {
        type: Number,
        default: 5,
    },
    maxFileSize: {
        type: String,
        default: '5MB',
    },
    accept: {
        type: String,
        default: 'image/*',
    },
    label: {
        type: String,
        default: 'Upload Images',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'remove-existing']);

const pond = ref(null);

const serverConfig = computed(() => ({
    process: null,
    fetch: null,
    revert: null,
}));

const updateFiles = () => {
    if (!pond.value) return;
    const files = pond.value.getFiles();
    emit(
        'update:modelValue',
        files
            .map((fileItem) => fileItem.file)
            .filter(Boolean)
    );
};

const handleRemoveExisting = (index) => {
    emit('remove-existing', index);
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            {{ label }}
        </label>
        <FilePond
            ref="pond"
            :max-files="maxFiles"
            :max-file-size="maxFileSize"
            :accepted-file-types="accept"
            :server="serverConfig"
            :allow-multiple="maxFiles > 1"
            :name="'images'"
            class="mb-2"
            @updatefiles="updateFiles"
        />
        <div v-if="existingImages.length" class="mt-2 space-y-2">
            <div
                v-for="(img, index) in existingImages"
                :key="index"
                class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex items-center gap-3">
                    <img :src="img.url" class="h-10 w-10 rounded object-cover" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ img.name || 'Image' }}</span>
                </div>
                <button
                    type="button"
                    class="text-red-500 hover:text-red-700"
                    @click="handleRemoveExisting(index)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
