<script setup>
defineProps({
    hoverable: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <tr :class="hoverable ? 'hover:bg-gray-100 border-b border-gray-200' : 'border-b border-gray-200'">
        <slot />
    </tr>
</template>

