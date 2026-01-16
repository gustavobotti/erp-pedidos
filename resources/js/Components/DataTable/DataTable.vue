<script setup>
defineProps({
    headers: {
        type: Array,
        required: true,
        // Example: [{ label: 'Nome', class: 'text-gray-900' }, ...]
    },
});
</script>

<template>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead class="bg-gray-200">
                <tr>
                    <th
                        v-for="(header, index) in headers"
                        :key="index"
                        :class="header.class || 'text-gray-900'"
                    >
                        {{ header.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <slot name="body" />
            </tbody>
        </table>
    </div>
</template>

