<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { AlertDialog, AlertDialogTrigger, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogDescription, AlertDialogFooter, AlertDialogAction, AlertDialogCancel } from '@/Components/ui/alert-dialog.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm dark:border-red-800 dark:bg-slate-900">
        <header>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Delete Account
            </h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Once your account is deleted, all of its resources and data will be permanently deleted.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion" class="mt-4">
            Delete Account
        </DangerButton>

        <AlertDialog :open="confirmingUserDeletion" @update:open="confirmingUserDeletion = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Are you sure you want to delete your account?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Once your account is deleted, all of its resources and data will be permanently deleted.
                        Please enter your password to confirm you would like to permanently delete your account.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <form @submit.prevent="deleteUser" class="mt-4">
                    <InputLabel forId="password" value="Password" class="sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </form>

                <AlertDialogFooter>
                    <AlertDialogCancel @click="closeModal">
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-red-600 hover:bg-red-500"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
