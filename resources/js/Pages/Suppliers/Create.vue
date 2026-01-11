<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import axios from 'axios';
import { formatCNPJ, formatCEP, unmask } from '@/Composables/useMasks';

const props = defineProps({
    vendedores: Array,
});

const form = useForm({
    name: '',
    cnpj: '',
    cep: '',
    address: '',
    status: true,
    user_ids: [],
});

const fetchAddress = async () => {
    if (form.cep.length >= 8) {
        try {
            const response = await axios.post(route('fetch-address'), {
                cep: form.cep,
            });

            if (response.data.success) {
                form.address = response.data.address.full_address;
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }
};

watch(() => form.cep, (newValue) => {
    if (newValue.replace(/\D/g, '').length === 8) {
        fetchAddress();
    }
});

const handleCNPJInput = (event) => {
    form.cnpj = formatCNPJ(event.target.value);
};

const handleCEPInput = (event) => {
    form.cep = formatCEP(event.target.value);
};

const toggleVendedor = (vendedorId) => {
    const index = form.user_ids.indexOf(vendedorId);
    if (index > -1) {
        form.user_ids.splice(index, 1);
    } else {
        form.user_ids.push(vendedorId);
    }
};

const submit = () => {
    const data = {
        name: form.name,
        cnpj: unmask(form.cnpj),
        cep: unmask(form.cep),
        address: form.address,
        status: form.status,
        user_ids: form.user_ids,
    };

    form.transform(() => data).post(route('suppliers.store'));
};
</script>

<template>
    <Head title="Novo Fornecedor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Criar Novo Fornecedor
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
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
                            <div class="space-y-2 max-h-60 overflow-y-auto bg-gray-50 p-4 rounded-md">
                                <div
                                    v-for="vendedor in vendedores"
                                    :key="vendedor.id"
                                    class="flex items-start"
                                >
                                    <div class="flex items-center h-5">
                                        <input
                                            :id="`vendedor-${vendedor.id}`"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            :checked="form.user_ids.includes(vendedor.id)"
                                            @change="toggleVendedor(vendedor.id)"
                                        />
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label :for="`vendedor-${vendedor.id}`" class="font-medium text-gray-700 cursor-pointer">
                                            {{ vendedor.name }}
                                        </label>
                                        <p class="text-gray-500">{{ vendedor.email }}</p>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.user_ids" />
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Criar Fornecedor
                            </PrimaryButton>

                            <Link
                                :href="route('suppliers.index')"
                                class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

