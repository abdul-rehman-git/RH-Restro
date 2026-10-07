<script setup>
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FieldInput from '@/Components/ui/field-input.vue';

const props = defineProps({
    cloudinary: {
        type: Object,
        default: () => ({
            cloud_name: '',
            api_key: '',
            folder: 'rh-commerce',
            api_secret_set: false,
            is_configured: false,
        }),
    },
});

const form = useForm({
    cloud_name: props.cloudinary.cloud_name || '',
    api_key: props.cloudinary.api_key || '',
    api_secret: '',
    folder: props.cloudinary.folder || 'rh-commerce',
});

const submit = () => {
    form.post(route('profile.cloudinary.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.api_secret = '';
        },
    });
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <header class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Cloudinary
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Image uploads use Cloudinary. Values saved here override `.env` defaults.
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                :class="cloudinary.is_configured
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                    : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300'"
            >
                {{ cloudinary.is_configured ? 'Configured' : 'Not configured' }}
            </span>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <FieldInput
                name="cloud_name"
                label="Cloud Name"
                v-model="form.cloud_name"
                :error="form.errors.cloud_name"
                placeholder="your-cloud-name"
            />

            <FieldInput
                name="api_key"
                label="API Key"
                v-model="form.api_key"
                :error="form.errors.api_key"
                placeholder="Cloudinary API key"
            />

            <FieldInput
                name="api_secret"
                label="API Secret"
                type="password"
                v-model="form.api_secret"
                :error="form.errors.api_secret"
                :placeholder="cloudinary.api_secret_set ? 'Leave blank to keep current secret' : 'Cloudinary API secret'"
            />

            <FieldInput
                name="folder"
                label="Upload Folder"
                v-model="form.folder"
                :error="form.errors.folder"
                placeholder="rh-commerce"
            />

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">Save Cloudinary</PrimaryButton>
                <p
                    v-if="form.recentlySuccessful"
                    class="text-sm text-slate-600 dark:text-slate-400"
                >
                    Saved.
                </p>
            </div>
        </form>
    </div>
</template>
