<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    suppliers: Array,
    initialProducts: Array,
});

const form = useForm({
    supplier_id: props.order.supplier_id,
    date: props.order.date,
    observation: props.order.observation || '',
    status: props.order.status,
    products: props.order.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        unit_price: parseFloat(item.unit_price),
    })),
});

const submit = () => {
    form.put(route('orders.update', props.order.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Pedido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Pedido #{{ order.id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form
                        :form="form"
                        :suppliers="suppliers"
                        :initial-products="initialProducts"
                        :is-edit="true"
                        submit-label="Atualizar Pedido"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

