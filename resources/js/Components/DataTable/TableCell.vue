<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'normal', // normal, bold, medium
    },
});

const cellClass = computed(() => {
    const variants = {
        normal: 'text-gray-700',
        bold: 'text-gray-900 font-medium',
        medium: 'text-gray-900 font-medium',
    };

    return variants[props.variant] || variants.normal;
});
</script>

<template>
    <td :class="cellClass">
        <slot />
    </td>
</template>

