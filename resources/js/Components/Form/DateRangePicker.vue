<script setup>
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: 'Período',
    },
    placeholder: {
        type: String,
        default: 'Selecione o período',
    },
    mode: {
        type: String,
        default: 'range', // single, range, multiple
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const dateConfig = {
    mode: props.mode,
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    locale: {
        rangeSeparator: ' a ',
    },
};

const handleChange = (selectedDates, dateStr) => {
    emit('update:modelValue', dateStr);
    emit('change', { selectedDates, dateStr });
};
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        <flat-pickr
            :model-value="modelValue"
            :config="dateConfig"
            :placeholder="placeholder"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
            @on-change="handleChange"
        />
    </div>
</template>

