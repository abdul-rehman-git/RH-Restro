<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-100 pt-6 sm:justify-center sm:pt-0 dark:bg-slate-900">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-slate-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-slate-800"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>
