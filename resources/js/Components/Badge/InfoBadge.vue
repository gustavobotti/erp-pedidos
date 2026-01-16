<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'gray', // gray, info, success, warning, error, indigo
    },
});

const badgeClass = computed(() => {
    const variants = {
        gray: 'bg-gray-100 text-gray-800',
        info: 'bg-blue-100 text-blue-800',
        success: 'bg-green-100 text-green-800',
        warning: 'bg-yellow-100 text-yellow-800',
        error: 'bg-red-100 text-red-800',
        indigo: 'bg-indigo-100 text-indigo-800',
    };

    return variants[props.variant] || variants.gray;
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium"
        :class="badgeClass"
    >
        <slot />
    </span>
</template>

