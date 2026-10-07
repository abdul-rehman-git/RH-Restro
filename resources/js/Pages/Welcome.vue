<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-900">
        <Head title="Welcome" />

        <div class="text-center">
            <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-4">
                Welcome to Business React Kit
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-400 mb-8">
                This application has been migrated to Vue.js
            </p>
            <div class="flex gap-4 justify-center">
                <Link
                    href="/login"
                    class="px-6 py-3 bg-slate-900 text-white rounded-xl hover:bg-slate-700 transition dark:bg-slate-200 dark:text-slate-900"
                >
                    Sign In
                </Link>
                <Link
                    href="/"
                    class="px-6 py-3 border border-slate-300 rounded-xl hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300"
                >
                    Home
                </Link>
            </div>
        </div>
    </div>
</template>
