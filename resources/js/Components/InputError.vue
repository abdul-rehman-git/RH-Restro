<template>
    <div v-if="message" class="text-sm text-red-600 dark:text-red-400">
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>
