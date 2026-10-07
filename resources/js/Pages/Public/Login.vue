<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import SeoHead from '@/Components/SeoHead.vue';

const page = usePage();
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/public/components/Button.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { computed } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const errors = computed(() => usePage().props.errors || {});
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="max-w-md mx-auto px-4 py-16">
            <div v-reveal="{ preset: 'zoom', duration: 700 }" class="rounded-xl border border-border bg-card p-8">
                <h1 class="text-2xl font-bold text-foreground mb-6 text-center">
                    Sign In
                </h1>

                <div v-if="errors.google" class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                    {{ errors.google }}
                </div>

                <a :href="route('auth.google.redirect', { action: 'signin' })"
                    class="w-full flex items-center justify-center gap-3 px-6 py-3 mb-4 border border-border rounded-lg bg-card hover:bg-muted text-foreground font-medium transition-luxury">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign in with Google
                </a>

                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-border" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-card px-2 text-muted-foreground">Or continue with</span>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <InputLabel forId="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="public-field mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel forId="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="public-field mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <Checkbox v-model="form.remember" />
                            <span class="ms-2 text-sm text-muted-foreground">
                                Remember me
                            </span>
                        </label>
                    </div>

                    <Button class="w-full" size="lg">Sign In</Button>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
