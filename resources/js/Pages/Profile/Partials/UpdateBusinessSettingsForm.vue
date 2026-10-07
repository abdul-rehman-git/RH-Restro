<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FieldInput from '@/Components/ui/field-input.vue';

const page = usePage();
const businessSettings = page.props.businessSettings || {};

const form = useForm({
    business_name: businessSettings.name || '',
    business_logo: null,
    remove_business_logo: false,
});

const submit = () => {
    form.post(route('profile.business.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.business_logo = null;
            form.remove_business_logo = false;
        },
    });
};

const setBusinessLogo = (event) => {
    form.business_logo = event.target.files?.[0] ?? null;
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <header>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Business Settings
            </h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Update the business name and admin logo shown across the dashboard.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <FieldInput
                name="business_name"
                label="Business Name"
                v-model="form.business_name"
                :error="form.errors.business_name"
            />

            <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Business Logo</h3>
                <div class="mt-4 flex items-start gap-4">
                    <img
                        v-if="businessSettings.logoUrl"
                        :src="businessSettings.logoUrl"
                        alt="Current business logo"
                        class="h-20 w-20 rounded-2xl object-cover ring-1 ring-black/5"
                    />
                    <div v-else class="flex h-20 w-20 items-center justify-center rounded-2xl border border-dashed border-slate-300 text-xs text-slate-400 dark:border-slate-700">
                        No logo
                    </div>
                    <div class="flex-1">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Upload New Logo</label>
                        <input
                            type="file"
                            accept="image/*"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            @change="setBusinessLogo"
                        />
                        <p v-if="form.errors.business_logo" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.business_logo }}</p>
                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input v-model="form.remove_business_logo" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 dark:border-slate-700 dark:bg-slate-950" />
                            Remove current logo
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
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
