<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const verificationLinkSent = ref(false);

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
    verificationLinkSent.value = true;
};
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification">
            <meta name="robots" content="noindex, nofollow" />
        </Head>

        <div class="mb-4 text-sm text-slate-600 dark:text-slate-400">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the
            link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <div class="mt-4 flex items-center justify-between">
            <PrimaryButton
                @click="submit"
                :disabled="form.processing"
            >
                Resend Verification Email
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:text-slate-400 dark:hover:text-slate-100 dark:focus:ring-offset-slate-800"
            >
                Log Out
            </Link>
        </div>
    </GuestLayout>
</template>
