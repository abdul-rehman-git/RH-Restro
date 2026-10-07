<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password">
            <meta name="robots" content="noindex, nofollow" />
        </Head>

        <div class="mb-4 text-sm text-slate-600 dark:text-slate-400">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
        </div>

        <form @submit.prevent="submit">
            <InputLabel forId="email" value="Email" />
            <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
            />
            <InputError :message="form.errors.email" class="mt-2" />

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:text-slate-400 dark:hover:text-slate-100 dark:focus:ring-offset-slate-800"
                >
                    Back to login
                </Link>

                <PrimaryButton class="ms-4" :disabled="form.processing">
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
