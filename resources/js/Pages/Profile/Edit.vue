<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

defineProps({
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: '' },
});
</script>

<template>
    <AuthenticatedLayout header="Profile">
        <Head title="Profile" />

        <div class="space-y-6">
            <UpdateProfileInformationForm
                :mustVerifyEmail="mustVerifyEmail"
                :status="status"
            />

            <UpdatePasswordForm />

            <DeleteUserForm />
        </div>
    </AuthenticatedLayout>
</template>
