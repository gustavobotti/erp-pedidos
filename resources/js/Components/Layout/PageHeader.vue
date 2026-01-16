<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ title }}
        </h2>
        <div v-if="$slots.actions" class="flex gap-3">
            <slot name="actions" />
        </div>
    </div>
</template>

