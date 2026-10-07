<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FieldInput from '@/Components/ui/field-input.vue';
import FieldTextarea from '@/Components/ui/field-textarea.vue';
import ImageUploader from '@/Components/ui/image-uploader.vue';

const props = defineProps({
    publicSite: { type: Object, default: () => ({}) },
    homePage: { type: Object, default: () => ({}) },
    aboutPage: { type: Object, default: () => ({}) },
    seoSettings: { type: Object, default: () => ({}) },
});

const siteForm = useForm({
    name: props.publicSite.name || '',
    tagline: props.publicSite.tagline || '',
    email: props.publicSite.email || '',
    phone: props.publicSite.phone || '',
    whatsapp: props.publicSite.whatsapp || '',
    address: props.publicSite.address || '',
    map_embed_url: props.publicSite.map_embed_url || '',
    footer_text: props.publicSite.footer_text || '',
    copyright_text: props.publicSite.copyright_text || '',
    facebook_url: props.publicSite.facebook_url || '',
    instagram_url: props.publicSite.instagram_url || '',
    twitter_url: props.publicSite.twitter_url || '',
    business_hours_text: props.publicSite.business_hours_text || '',
    logo: null,
    footer_logo: null,
    remove_logo: false,
    remove_footer_logo: false,
});

const homeForm = useForm({
    hero_title: props.homePage.hero_title || '',
    hero_subtitle: props.homePage.hero_subtitle || '',
    stats_text: props.homePage.stats_text || '',
    hero_image: null,
    remove_hero_image: false,
});

const aboutForm = useForm({
    hero_title: props.aboutPage.hero_title || '',
    hero_subtitle: props.aboutPage.hero_subtitle || '',
    story_content: props.aboutPage.story_content || '',
    artist_name: props.aboutPage.artist_name || '',
    artist_quote: props.aboutPage.artist_quote || '',
    stats_text: props.aboutPage.stats_text || '',
    process_steps_text: props.aboutPage.process_steps_text || '',
    values_text: props.aboutPage.values_text || '',
    artist_image: null,
    remove_artist_image: false,
});

const logoFiles = ref([]);
const footerLogoFiles = ref([]);
const heroImageFiles = ref([]);
const artistImageFiles = ref([]);

const existingLogo = computed(() => props.publicSite.logo_url ? [{ url: props.publicSite.logo_url, name: 'Header logo' }] : []);
const existingFooterLogo = computed(() => props.publicSite.footer_logo_url ? [{ url: props.publicSite.footer_logo_url, name: 'Footer logo' }] : []);
const existingHeroImage = computed(() => props.homePage.hero_image_url ? [{ url: props.homePage.hero_image_url, name: 'Hero image' }] : []);
const existingArtistImage = computed(() => props.aboutPage.artist_image_url ? [{ url: props.aboutPage.artist_image_url, name: 'Artist image' }] : []);

watch(logoFiles, (files) => {
    siteForm.logo = files[0] ?? null;
    if (siteForm.logo) {
        siteForm.remove_logo = false;
    }
});

watch(footerLogoFiles, (files) => {
    siteForm.footer_logo = files[0] ?? null;
    if (siteForm.footer_logo) {
        siteForm.remove_footer_logo = false;
    }
});

watch(heroImageFiles, (files) => {
    homeForm.hero_image = files[0] ?? null;
    if (homeForm.hero_image) {
        homeForm.remove_hero_image = false;
    }
});

watch(artistImageFiles, (files) => {
    aboutForm.artist_image = files[0] ?? null;
    if (aboutForm.artist_image) {
        aboutForm.remove_artist_image = false;
    }
});

const removeExistingLogo = () => {
    siteForm.remove_logo = true;
    siteForm.logo = null;
    logoFiles.value = [];
};

const removeExistingFooterLogo = () => {
    siteForm.remove_footer_logo = true;
    siteForm.footer_logo = null;
    footerLogoFiles.value = [];
};

const removeExistingHeroImage = () => {
    homeForm.remove_hero_image = true;
    homeForm.hero_image = null;
    heroImageFiles.value = [];
};

const removeExistingArtistImage = () => {
    aboutForm.remove_artist_image = true;
    aboutForm.artist_image = null;
    artistImageFiles.value = [];
};

const saveSiteSettings = () => {
    siteForm.post(route('public-content.site-settings.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const saveHomePage = () => {
    homeForm.post(route('public-content.home.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const saveAboutPage = () => {
    aboutForm.post(route('public-content.about.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const seoForm = useForm({
    default_description: props.seoSettings.default_description || '',
    google_analytics_id: props.seoSettings.google_analytics_id || '',
    google_site_verification: props.seoSettings.google_site_verification || '',
    default_og_image: null,
    remove_default_og_image: false,
});

const seoImageFiles = ref([]);

const existingSeoImage = computed(() =>
    props.seoSettings.default_og_image_url
        ? [{ url: props.seoSettings.default_og_image_url, name: 'Default OG image' }]
        : [],
);

watch(seoImageFiles, (files) => {
    seoForm.default_og_image = files[0] ?? null;
    if (seoForm.default_og_image) {
        seoForm.remove_default_og_image = false;
    }
});

const removeExistingSeoImage = () => {
    seoForm.remove_default_og_image = true;
    seoForm.default_og_image = null;
    seoImageFiles.value = [];
};

const saveSeoSettings = () => {
    seoForm.post(route('public-content.seo.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout header="Public Content">
        <Head title="Public Content" />

        <div class="mx-auto max-w-6xl space-y-8">
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/60">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Simplified Public Content</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                    This panel now focuses on real content only. Static labels, buttons, routes, and section headings stay in the frontend so the public site is easier to maintain.
                </p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Public Site Settings</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Brand, contact details, footer copy, social links, and business hours for the public site.
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="saveSiteSettings">
                    <div class="grid gap-4 md:grid-cols-2">
                        <FieldInput name="name" label="Site Name" v-model="siteForm.name" :error="siteForm.errors.name" />
                        <FieldInput name="tagline" label="Tagline" v-model="siteForm.tagline" :error="siteForm.errors.tagline" />
                        <FieldInput name="email" label="Email" type="email" v-model="siteForm.email" :error="siteForm.errors.email" />
                        <FieldInput name="phone" label="Phone" v-model="siteForm.phone" :error="siteForm.errors.phone" />
                        <FieldInput name="whatsapp" label="WhatsApp" v-model="siteForm.whatsapp" :error="siteForm.errors.whatsapp" />
                        <FieldInput name="map_embed_url" label="Map Embed URL" v-model="siteForm.map_embed_url" :error="siteForm.errors.map_embed_url" />
                    </div>

                    <FieldTextarea name="address" label="Address" v-model="siteForm.address" :error="siteForm.errors.address" :rows="3" />
                    <FieldTextarea name="footer_text" label="Footer Text" v-model="siteForm.footer_text" :error="siteForm.errors.footer_text" :rows="3" />
                    <FieldInput name="copyright_text" label="Copyright Text" v-model="siteForm.copyright_text" :error="siteForm.errors.copyright_text" />

                    <div class="grid gap-4 md:grid-cols-3">
                        <FieldInput name="facebook_url" label="Facebook URL" v-model="siteForm.facebook_url" :error="siteForm.errors.facebook_url" />
                        <FieldInput name="instagram_url" label="Instagram URL" v-model="siteForm.instagram_url" :error="siteForm.errors.instagram_url" />
                        <FieldInput name="twitter_url" label="Twitter URL" v-model="siteForm.twitter_url" :error="siteForm.errors.twitter_url" />
                    </div>

                    <div>
                        <FieldTextarea
                            name="business_hours_text"
                            label="Business Hours"
                            v-model="siteForm.business_hours_text"
                            :error="siteForm.errors.business_hours_text"
                            :rows="4"
                            placeholder="Monday - Friday|9:00 AM - 6:00 PM"
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Use one line per entry in the format `Day|Hours`.</p>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Header Logo</h3>
                            <div class="mt-4">
                                <ImageUploader
                                    v-model="logoFiles"
                                    :existing-images="siteForm.remove_logo ? [] : existingLogo"
                                    :error="siteForm.errors.logo"
                                    :max-files="1"
                                    label="Upload Header Logo"
                                    @remove-existing="removeExistingLogo"
                                />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Footer Logo</h3>
                            <div class="mt-4">
                                <ImageUploader
                                    v-model="footerLogoFiles"
                                    :existing-images="siteForm.remove_footer_logo ? [] : existingFooterLogo"
                                    :error="siteForm.errors.footer_logo"
                                    :max-files="1"
                                    label="Upload Footer Logo"
                                    @remove-existing="removeExistingFooterLogo"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="siteForm.processing">Save Site Settings</PrimaryButton>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Homepage Content</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Keep this limited to hero copy, homepage stats, and the main visual.
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="saveHomePage">
                    <FieldInput name="hero_title" label="Hero Title" v-model="homeForm.hero_title" :error="homeForm.errors.hero_title" />

                    <FieldTextarea name="hero_subtitle" label="Hero Subtitle" v-model="homeForm.hero_subtitle" :error="homeForm.errors.hero_subtitle" :rows="3" />

                    <div>
                        <FieldTextarea
                            name="stats_text"
                            label="Stats"
                            v-model="homeForm.stats_text"
                            :error="homeForm.errors.stats_text"
                            :rows="4"
                            placeholder="Artworks|500+"
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Use one line per entry in the format `Label|Value`.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Hero Image</h3>
                        <div class="mt-4">
                            <ImageUploader
                                v-model="heroImageFiles"
                                :existing-images="homeForm.remove_hero_image ? [] : existingHeroImage"
                                :error="homeForm.errors.hero_image"
                                :max-files="1"
                                label="Upload Hero Image"
                                @remove-existing="removeExistingHeroImage"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="homeForm.processing">Save Homepage</PrimaryButton>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">About Page Content</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Keep only the story, highlights, values, and supporting imagery here.
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="saveAboutPage">
                    <FieldInput name="about_hero_title" label="Hero Title" v-model="aboutForm.hero_title" :error="aboutForm.errors.hero_title" />

                    <FieldTextarea name="about_hero_subtitle" label="Hero Subtitle" v-model="aboutForm.hero_subtitle" :error="aboutForm.errors.hero_subtitle" :rows="3" />

                    <FieldInput name="artist_name" label="Artist Name" v-model="aboutForm.artist_name" :error="aboutForm.errors.artist_name" />

                    <FieldTextarea name="story_content" label="Story Content" v-model="aboutForm.story_content" :error="aboutForm.errors.story_content" :rows="7" />
                    <FieldTextarea name="artist_quote" label="Artist Quote" v-model="aboutForm.artist_quote" :error="aboutForm.errors.artist_quote" :rows="3" />

                    <div>
                        <FieldTextarea
                            name="about_stats_text"
                            label="Stats"
                            v-model="aboutForm.stats_text"
                            :error="aboutForm.errors.stats_text"
                            :rows="4"
                            placeholder="Artworks Created|500+"
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Use one line per entry in the format `Label|Value`.</p>
                    </div>

                    <div>
                        <FieldTextarea
                            name="process_steps_text"
                            label="Process Steps"
                            v-model="aboutForm.process_steps_text"
                            :error="aboutForm.errors.process_steps_text"
                            :rows="5"
                            placeholder="Inspiration|We begin by understanding your vision."
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Use one line per entry in the format `Title|Description`.</p>
                    </div>

                    <div>
                        <FieldTextarea
                            name="values_text"
                            label="Values"
                            v-model="aboutForm.values_text"
                            :error="aboutForm.errors.values_text"
                            :rows="5"
                            placeholder="Quality First|We never compromise on quality."
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Use one line per entry in the format `Title|Description`.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Artist Image</h3>
                        <div class="mt-4">
                            <ImageUploader
                                v-model="artistImageFiles"
                                :existing-images="aboutForm.remove_artist_image ? [] : existingArtistImage"
                                :error="aboutForm.errors.artist_image"
                                :max-files="1"
                                label="Upload Artist Image"
                                @remove-existing="removeExistingArtistImage"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="aboutForm.processing">Save About Page</PrimaryButton>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">SEO Settings</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Configure default SEO metadata for all pages. Each page also has specific SEO set automatically.
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="saveSeoSettings">
                    <FieldTextarea
                        name="default_description"
                        label="Default Meta Description"
                        v-model="seoForm.default_description"
                        :error="seoForm.errors.default_description"
                        :rows="3"
                        placeholder="Enter a default description for search engines..."
                    />

                    <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Default OG Image</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Used when a page does not have its own image. Recommended 1200x630px.</p>
                        <div class="mt-4">
                            <ImageUploader
                                v-model="seoImageFiles"
                                :existing-images="seoForm.remove_default_og_image ? [] : existingSeoImage"
                                :error="seoForm.errors.default_og_image"
                                :max-files="1"
                                label="Upload OG Image"
                                @remove-existing="removeExistingSeoImage"
                            />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <FieldInput
                            name="google_analytics_id"
                            label="Google Analytics ID"
                            v-model="seoForm.google_analytics_id"
                            :error="seoForm.errors.google_analytics_id"
                            placeholder="G-XXXXXXXXXX"
                        />
                        <FieldInput
                            name="google_site_verification"
                            label="Google Site Verification"
                            v-model="seoForm.google_site_verification"
                            :error="seoForm.errors.google_site_verification"
                            placeholder="Enter verification code"
                        />
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="seoForm.processing">Save SEO Settings</PrimaryButton>
                    </div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
