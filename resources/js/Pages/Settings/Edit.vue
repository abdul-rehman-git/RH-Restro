<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateBusinessSettingsForm from '@/Pages/Profile/Partials/UpdateBusinessSettingsForm.vue';
import UpdateCloudinarySettingsForm from '@/Pages/Settings/Partials/UpdateCloudinarySettingsForm.vue';

defineProps({
    cloudinary: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <AuthenticatedLayout header="Settings">
        <Head title="Settings" />

        <div class="max-w-4xl space-y-6">
            <UpdateBusinessSettingsForm />
            <UpdateCloudinarySettingsForm :cloudinary="cloudinary" />
        </div>
    </AuthenticatedLayout>
</template>
