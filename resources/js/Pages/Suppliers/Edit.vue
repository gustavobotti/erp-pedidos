<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatCNPJ, formatCEP, unmask } from '@/Composables/useMasks';

const props = defineProps({
    supplier: Object,
    vendedores: Array,
});

const form = useForm({
    name: props.supplier.name,
    cnpj: formatCNPJ(props.supplier.cnpj),
    cep: formatCEP(props.supplier.cep),
    address: props.supplier.address,
    status: props.supplier.status,
    user_ids: props.supplier.users ? props.supplier.users.map(u => u.id) : [],
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

    form.transform(() => data).put(route('suppliers.update', props.supplier.id));
};
</script>

<template>
    <Head title="Editar Fornecedor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Fornecedor
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form
                        :form="form"
                        :vendedores="vendedores"
                        submit-label="Atualizar Fornecedor"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

