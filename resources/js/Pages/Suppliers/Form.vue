<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import { Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import axios from 'axios';
import { formatCNPJ, formatCEP } from '@/Composables/useMasks';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    vendedores: {
        type: Array,
        default: () => [],
    },
    submitLabel: {
        type: String,
        default: 'Salvar',
    },
    cancelRoute: {
        type: String,
        default: 'suppliers.index',
    },
});

const emit = defineEmits(['submit']);

const fetchAddress = async () => {
    if (props.form.cep.length >= 8) {
        try {
            const response = await axios.post(route('fetch-address'), {
                cep: props.form.cep,
            });

            if (response.data.success) {
                props.form.address = response.data.address.full_address;
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }
};

watch(() => props.form.cep, (newValue) => {
    if (newValue.replace(/\D/g, '').length === 8) {
        fetchAddress();
    }
});

const handleCNPJInput = (event) => {
    props.form.cnpj = formatCNPJ(event.target.value);
};

const handleCEPInput = (event) => {
    props.form.cep = formatCEP(event.target.value);
};

const customVendedorLabel = (vendedor) => {
    return `${vendedor.name} (${vendedor.email})`;
};

const handleSubmit = () => {
    emit('submit');
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nome -->
        <div>
            <InputLabel for="name" value="Nome" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <!-- CNPJ -->
        <div>
            <InputLabel for="cnpj" value="CNPJ" />
            <TextInput
                id="cnpj"
                type="text"
                class="mt-1 block w-full"
                v-model="form.cnpj"
                required
                placeholder="00.000.000/0000-00"
                @input="handleCNPJInput"
                maxlength="18"
            />
            <InputError class="mt-2" :message="form.errors.cnpj" />
        </div>

        <!-- CEP -->
        <div>
            <InputLabel for="cep" value="CEP" />
            <TextInput
                id="cep"
                type="text"
                class="mt-1 block w-full"
                v-model="form.cep"
                required
                placeholder="00000-000"
                @input="handleCEPInput"
                @blur="fetchAddress"
                maxlength="9"
            />
            <InputError class="mt-2" :message="form.errors.cep" />
        </div>

        <!-- Endereço -->
        <div>
            <InputLabel for="address" value="Endereço" />
            <textarea
                id="address"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100 text-gray-900"
                v-model="form.address"
                required
                rows="3"
                disabled
                placeholder="Será preenchido automaticamente ao informar o CEP"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.address" />
        </div>

        <!-- Status -->
        <div class="flex items-center gap-3">
            <input
                id="status"
                v-model="form.status"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            />
            <InputLabel for="status" value="Fornecedor Ativo" class="!mb-0" />
        </div>
        <InputError class="mt-2" :message="form.errors.status" />

        <!-- Vendedores -->
        <div v-if="vendedores && vendedores.length > 0" class="border-t pt-6">
            <InputLabel value="Vendedores com Acesso" class="mb-3" />
            <MultiSelect
                v-model="form.user_ids"
                :options="vendedores"
                track-by="id"
                label="name"
                :custom-label="customVendedorLabel"
                placeholder="Selecione os vendedores..."
            />
            <InputError class="mt-2" :message="form.errors.user_ids" />
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>

            <Link
                :href="route(cancelRoute)"
                class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
            >
                Cancelar
            </Link>
        </div>
    </form>
</template>

