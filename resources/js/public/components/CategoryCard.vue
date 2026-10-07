<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Utensils } from 'lucide-vue-next';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    image: { type: String, default: '' },
    itemCount: { type: Number, default: 0 },
    link: { type: String, required: true },
});
</script>

<template>
    <Link
        :href="link"
        class="public-card group relative overflow-hidden rounded-2xl border border-border/80 bg-card shadow-md transition-all duration-300 hover:-translate-y-1 hover:border-[#F59E0B]/60 hover:shadow-xl"
    >
        <div class="relative aspect-[4/3] overflow-hidden">
            <img
                v-if="image"
                :src="image"
                :alt="title"
                loading="lazy"
                class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-108"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#F59E0B]/20 to-black px-6 text-center text-lg font-bold text-white"
            >
                {{ title }}
            </div>
            <!-- Multi-layer gradient for great readability -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-90 transition-opacity group-hover:opacity-95" />

            <!-- Category Body Over Image -->
            <div class="absolute inset-0 p-5 sm:p-6 flex flex-col justify-end">
                <div class="transition-transform duration-300 group-hover:-translate-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#F59E0B]/20 text-[#FDE68A] border border-[#F59E0B]/40 mb-2 backdrop-blur-md">
                        {{ itemCount }} Dishes
                    </span>
                    <h3 class="font-display text-xl sm:text-2xl font-bold text-white mb-1.5 drop-shadow-sm">
                        {{ title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-200/90 mb-3 line-clamp-2 font-normal">
                        {{ description }}
                    </p>
                    <div class="flex items-center space-x-1.5 text-[#F59E0B] font-bold text-xs sm:text-sm">
                        <span>View Menu</span>
                        <ArrowRight class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1.5" />
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>
