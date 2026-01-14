<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    suppliers: Array,
});

const form = useForm({
    supplier_id: '',
    reference: '',
    name: '',
    color: '',
    price: '',
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Novo Produto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Criar Novo Produto
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form
                        :form="form"
                        :suppliers="suppliers"
                        submit-label="Criar Produto"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

