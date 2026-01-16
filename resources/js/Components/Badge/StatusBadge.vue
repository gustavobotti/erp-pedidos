<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: [String, Boolean, Number],
        required: true,
    },
    variant: {
        type: String,
        default: 'auto', // auto, success, error, warning, info
    },
    truthyLabel: {
        type: String,
        default: 'Ativo',
    },
    falsyLabel: {
        type: String,
        default: 'Inativo',
    },
});

const badgeClass = computed(() => {
    if (props.variant !== 'auto') {
        return {
            success: 'bg-green-100 text-green-800',
            error: 'bg-red-100 text-red-800',
            warning: 'bg-yellow-100 text-yellow-800',
            info: 'bg-blue-100 text-blue-800',
            indigo: 'bg-indigo-100 text-indigo-800',
        }[props.variant] || 'bg-gray-100 text-gray-800';
    }

    // Auto mode - detect from status value
    if (typeof props.status === 'boolean') {
        return props.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    const statusMap = {
        'Pendente': 'bg-yellow-100 text-yellow-800',
        'Concluído': 'bg-green-100 text-green-800',
        'Cancelado': 'bg-red-100 text-red-800',
        'Ativo': 'bg-green-100 text-green-800',
        'Inativo': 'bg-red-100 text-red-800',
    };

    return statusMap[props.status] || 'bg-gray-100 text-gray-800';
});

const displayLabel = computed(() => {
    if (typeof props.status === 'boolean') {
        return props.status ? props.truthyLabel : props.falsyLabel;
    }
    return props.status;
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium"
        :class="badgeClass"
    >
        {{ displayLabel }}
    </span>
</template>

