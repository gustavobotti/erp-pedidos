<script setup>
import { computed } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'name',
    },
    trackBy: {
        type: String,
        default: 'id',
    },
    placeholder: {
        type: String,
        default: 'Selecione...',
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    closeOnSelect: {
        type: Boolean,
        default: false,
    },
    multiple: {
        type: Boolean,
        default: true,
    },
    taggable: {
        type: Boolean,
        default: false,
    },
    customLabel: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedItems = computed({
    get() {
        // Convert IDs to full objects
        return props.options.filter(option =>
            props.modelValue.includes(option[props.trackBy])
        );
    },
    set(value) {
        // Convert objects to IDs
        const ids = value.map(item => item[props.trackBy]);
        emit('update:modelValue', ids);
    },
});
</script>

<template>
    <Multiselect
        v-model="selectedItems"
        :options="options"
        :label="label"
        :track-by="trackBy"
        :placeholder="placeholder"
        :searchable="searchable"
        :close-on-select="closeOnSelect"
        :multiple="multiple"
        :taggable="taggable"
        :custom-label="customLabel"
        select-label="Pressione enter para selecionar"
        deselect-label="Pressione enter para remover"
        selected-label="Selecionado"
    >
        <template #noResult>
            Nenhum resultado encontrado
        </template>
        <template #noOptions>
            Lista vazia
        </template>
    </Multiselect>
</template>

<style>
/* Customize multiselect to match your design */
.multiselect {
    min-height: 42px;
}

.multiselect__tags {
    min-height: 42px;
    padding: 8px 40px 0 8px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
}

.multiselect__tags:focus-within {
    border-color: #374151;
    outline: none;
    box-shadow: 0 0 0 1px #374151;
}

.multiselect__tag {
    background: #374151;
    margin-bottom: 8px;
    padding: 5px 26px 5px 10px;
}

.multiselect__tag-icon:after {
    color: rgba(255, 255, 255, 0.8);
}

.multiselect__tag-icon:hover {
    background: #1f2937;
}

.multiselect__option--highlight {
    background: #4b5563;
    color: white;
}

.multiselect__option--selected {
    background: #e5e7eb;
    color: #1f2937;
    font-weight: 600;
}

.multiselect__option--selected.multiselect__option--highlight {
    background: #374151;
    color: white;
}

.multiselect__placeholder {
    color: #9ca3af;
    padding-top: 2px;
    margin-bottom: 8px;
}

.multiselect__input {
    padding: 0;
    margin-bottom: 8px;
}

.multiselect__single {
    margin-bottom: 8px;
}
</style>

