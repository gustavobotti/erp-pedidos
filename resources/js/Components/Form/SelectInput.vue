<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Todos',
    },
    valueKey: {
        type: String,
        default: 'value',
    },
    labelKey: {
        type: String,
        default: 'label',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const handleChange = (event) => {
    const value = event.target.value;
    emit('update:modelValue', value);
    emit('change', value);
};
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        <select
            :value="modelValue"
            @change="handleChange"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
        >
            <option value="">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option[valueKey]"
                :value="option[valueKey]"
            >
                {{ option[labelKey] }}
            </option>
        </select>
    </div>
</template>

