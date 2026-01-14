<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Pendente':
            return 'bg-yellow-100 text-yellow-800';
        case 'Concluído':
            return 'bg-green-100 text-green-800';
        case 'Cancelado':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head :title="`Pedido #${order.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pedido #{{ order.id }}
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('orders.edit', order.id)"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50"
                    >
                        Voltar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Informações do Pedido -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Fornecedor</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ order.supplier?.name }}</p>
                                <p v-if="order.supplier?.cnpj" class="text-sm text-gray-600">
                                    CNPJ: {{ order.supplier.cnpj }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Data do Pedido</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ formatDate(order.date) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Status</h3>
                                <span
                                    class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium"
                                    :class="getStatusBadgeClass(order.status)"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Criado por</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ order.user?.name }}</p>
                            </div>
                        </div>

                        <div v-if="order.observation" class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Observação</h3>
                            <p class="text-gray-900 bg-gray-50 p-4 rounded-md">{{ order.observation }}</p>
                        </div>
                    </div>
                </div>

                <!-- Produtos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Produtos do Pedido</h3>

                        <div class="space-y-4">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ item.product?.name }}
                                        </h4>
                                        <div class="mt-1 space-y-1">
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Referência:</span> {{ item.product?.reference }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Cor:</span> {{ item.product?.color }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Quantidade:</span> {{ item.quantity }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Valor Unitário:</span> {{ formatCurrency(item.unit_price) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Subtotal</p>
                                        <p class="text-xl font-bold text-gray-900">
                                            {{ formatCurrency(item.subtotal) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Total de Itens</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ order.items?.length || 0 }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Valor Total do Pedido</p>
                                <p class="text-3xl font-bold text-indigo-600">
                                    {{ formatCurrency(order.total_amount) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

