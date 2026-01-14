<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { unmask } from '@/Composables/useMasks';

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
                    <Form
                        :form="form"
                        :vendedores="vendedores"
                        submit-label="Criar Fornecedor"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

