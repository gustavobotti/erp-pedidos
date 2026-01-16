<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pesquisar...',
    },
    label: {
        type: String,
        default: 'Pesquisar',
    },
    debounce: {
        type: Number,
        default: 300,
    },
});

const emit = defineEmits(['update:modelValue']);

const searchValue = ref(props.modelValue);

let searchTimeout = null;

watch(searchValue, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        emit('update:modelValue', value);
    }, props.debounce);
});

// Sync with parent when modelValue changes
watch(() => props.modelValue, (value) => {
    if (searchValue.value !== value) {
        searchValue.value = value;
    }
});
</script>

<template>
    <div class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">
            {{ label }}
        </label>
        <input
            v-model="searchValue"
            type="text"
            :placeholder="placeholder"
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
        />
    </div>
</template>

