<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in">
            <meta name="robots" content="noindex, nofollow" />
        </Head>

        <form @submit.prevent="submit">
            <div>
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
            </div>

            <div class="mt-4">
                <InputLabel forId="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox v-model="form.remember" />
                    <span class="ms-2 text-sm text-slate-600 dark:text-slate-400">
                        Remember me
                    </span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="route().has('password.request')"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:text-slate-400 dark:hover:text-slate-100 dark:focus:ring-offset-slate-800"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton class="ms-4" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
