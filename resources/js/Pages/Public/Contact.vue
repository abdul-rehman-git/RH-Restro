<script setup>
import { reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Mail, MapPin, MessageCircle, Phone } from 'lucide-vue-next';
import SeoHead from '@/Components/SeoHead.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from '@/public/components/Button.vue';
import SectionHeader from '@/public/components/SectionHeader.vue';

const page = usePage();
const site = page.props.site || {};

const form = reactive({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    source_page: 'contact',
});

const submitting = ref(false);
const successMessage = ref('');
const formErrors = ref({});

const submit = async () => {
    submitting.value = true;
    successMessage.value = '';
    formErrors.value = {};

    try {
        const { data } = await window.axios.post('/api/public/contact', form);
        successMessage.value = data.message;
        form.name = '';
        form.email = '';
        form.phone = '';
        form.subject = '';
        form.message = '';
    } catch (error) {
        formErrors.value = error.response?.data?.errors || {};
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <SeoHead :seo="page.props.seo" />
    <PublicLayout>
        <div class="min-h-screen bg-background py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <SectionHeader
                    title="Get in Touch"
                    subtitle="Have a question, custom watch inquiry, or order query? We would love to hear from you."
                    centered
                />

                <div class="grid lg:grid-cols-3 gap-8">
                    <div v-reveal="{ preset: 'fadeRight', duration: 700 }" class="lg:col-span-2">
                        <div class="rounded-xl border border-border bg-card p-6 shadow-luxury sm:p-8">
                            <h2 class="text-2xl font-semibold text-foreground mb-6">
                                Send us a Message
                            </h2>

                            <form class="space-y-6" @submit.prevent="submit">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Name *</label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                                            placeholder="Your name"
                                        />
                                        <p v-if="formErrors.name" class="mt-2 text-sm text-red-600">{{ formErrors.name[0] }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Email *</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                                            placeholder="your@email.com"
                                        />
                                        <p v-if="formErrors.email" class="mt-2 text-sm text-red-600">{{ formErrors.email[0] }}</p>
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Phone</label>
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                                            :placeholder="site.phone"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Subject *</label>
                                        <input
                                            v-model="form.subject"
                                            type="text"
                                            class="public-field w-full rounded-lg border border-border bg-white px-4 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                                            placeholder="How can we help?"
                                        />
                                        <p v-if="formErrors.subject" class="mt-2 text-sm text-red-600">{{ formErrors.subject[0] }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Message *</label>
                                    <textarea
                                        v-model="form.message"
                                        rows="6"
                                        class="public-field w-full resize-none rounded-lg border border-border bg-white px-4 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 [color-scheme:light] dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:[color-scheme:dark]"
                                        placeholder="Tell us more about your inquiry..."
                                    />
                                    <p v-if="formErrors.message" class="mt-2 text-sm text-red-600">{{ formErrors.message[0] }}</p>
                                </div>

                                <p v-if="successMessage" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-300">
                                    {{ successMessage }}
                                </p>

                                <Button size="lg" class="w-full sm:w-auto" :class="{ 'public-loading': submitting }" :disabled="submitting">
                                    {{ submitting ? 'Sending...' : 'Send Message' }}
                                </Button>
                            </form>
                        </div>
                    </div>

                    <div v-stagger="{ preset: 'fadeLeft', stagger: 110, duration: 650 }" class="space-y-6">
                        <div class="public-card rounded-xl border border-border bg-card p-6 shadow-luxury">
                            <h3 class="font-semibold text-foreground mb-4">Contact Information</h3>
                            <div class="space-y-4">
                                <a :href="`tel:${site.phone}`" class="flex items-start space-x-4 rounded-lg p-4 transition-colors group hover:bg-muted">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center flex-shrink-0"><Phone class="w-5 h-5 text-amber-500" /></div>
                                    <div><div class="font-medium text-foreground group-hover:text-amber-500 transition-colors">Phone</div><div class="text-sm text-muted-foreground">{{ site.phone }}</div></div>
                                </a>
                                <a :href="`mailto:${site.email}`" class="flex items-start space-x-4 rounded-lg p-4 transition-colors group hover:bg-muted">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center flex-shrink-0"><Mail class="w-5 h-5 text-amber-500" /></div>
                                    <div><div class="font-medium text-foreground group-hover:text-amber-500 transition-colors">Email</div><div class="text-sm text-muted-foreground">{{ site.email }}</div></div>
                                </a>
                                <div class="flex items-start space-x-4 p-4">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center flex-shrink-0"><MapPin class="w-5 h-5 text-amber-500" /></div>
                                    <div><div class="font-medium text-foreground">Address</div><div class="text-sm text-muted-foreground whitespace-pre-line">{{ site.address }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-[#25D366]/20 bg-gradient-to-br from-[#25D366]/10 to-[#25D366]/5 p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="public-icon-float flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366]"><MessageCircle class="w-6 h-6 text-white" /></div>
                                <div><h3 class="font-semibold text-foreground">Chat on WhatsApp</h3><p class="text-sm text-muted-foreground">Fastest way to discuss custom work and order questions.</p></div>
                            </div>
                            <a
                                :href="site.whatsapp_url || '#'"
                                target="_blank"
                                rel="noreferrer"
                                class="public-interactive inline-flex w-full items-center justify-center rounded-lg border border-[#25D366] px-6 py-3 text-sm font-medium text-[#25D366] transition-colors hover:bg-[#25D366]/10"
                            >
                                Start Chat
                            </a>
                        </div>

                        <div class="public-card rounded-xl border border-border bg-card p-6 shadow-luxury">
                            <h3 class="font-semibold text-foreground mb-4">Business Hours</h3>
                            <div class="space-y-2 text-sm">
                                <div
                                    v-for="row in site.business_hours || []"
                                    :key="`${row.day}-${row.hours}`"
                                    class="flex justify-between gap-6"
                                >
                                    <span class="text-muted-foreground">{{ row.day }}</span>
                                    <span class="font-medium text-foreground text-right">{{ row.hours }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-reveal="{ preset: 'zoom', duration: 760 }" class="mt-12">
                    <div class="h-96 overflow-hidden rounded-xl border border-border bg-muted">
                        <iframe
                            v-if="site.map_embed_url"
                            :src="site.map_embed_url"
                            title="Business location"
                            class="h-full w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        />
                        <div v-else class="h-full flex items-center justify-center">
                            <div class="text-center">
                                <MapPin class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                                <p class="text-muted-foreground">Map URL can be managed from the admin public site settings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
