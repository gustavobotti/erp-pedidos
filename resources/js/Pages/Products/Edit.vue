<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    suppliers: Array,
});

const form = useForm({
    supplier_id: props.product.supplier_id,
    reference: props.product.reference,
    name: props.product.name,
    color: props.product.color,
    price: props.product.price,
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head title="Editar Produto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Produto
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form
                        :form="form"
                        :suppliers="suppliers"
                        submit-label="Atualizar Produto"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

