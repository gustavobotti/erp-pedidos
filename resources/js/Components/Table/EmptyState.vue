<script setup>
defineProps({
    message: {
        type: String,
        default: 'Nenhum registro encontrado.',
    },
    colspan: {
        type: [Number, String],
        default: 6,
    },
});
</script>

<template>
    <tr>
        <td :colspan="colspan" class="text-center py-4 text-gray-700">
            {{ message }}
        </td>
    </tr>
</template>

