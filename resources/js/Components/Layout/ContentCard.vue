<script setup>
defineProps({
    padding: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div :class="padding ? 'p-6' : ''">
            <slot />
        </div>
    </div>
</template>

