<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

const props = defineProps({
    orders: Object,
    suppliers: Array,
    filters: Object,
});

const selectedSupplier = ref(props.filters?.supplier_id || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedDateFrom = ref(props.filters?.date_from || '');
const selectedDateTo = ref(props.filters?.date_to || '');

const dateConfig = {
    mode: 'range',
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    locale: {
        rangeSeparator: ' a '
    }
};

const applyFilters = () => {
    // Parse date range if exists
    let dateFrom = '';
    let dateTo = '';

    if (selectedDateFrom.value) {
        const dates = selectedDateFrom.value.split(' a ');
        dateFrom = dates[0] || '';
        dateTo = dates[1] || dates[0] || '';
    }

    router.get(route('orders.index'), {
        supplier_id: selectedSupplier.value,
        status: selectedStatus.value,
        date_from: dateFrom,
        date_to: dateTo,
    }, {
        preserveState: true,
    });
};

const deleteOrder = (id) => {
    if (confirm('Tem certeza que deseja excluir este pedido?')) {
        router.delete(route('orders.destroy', id), {
            preserveScroll: true,
        });
    }
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
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gerenciar Pedidos
                </h2>
                <Link
                    :href="route('orders.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Novo Pedido
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fornecedor
                                </label>
                                <select
                                    v-model="selectedSupplier"
                                    @change="applyFilters"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                >
                                    <option value="">Todos</option>
                                    <option
                                        v-for="supplier in suppliers"
                                        :key="supplier.id"
                                        :value="supplier.id"
                                    >
                                        {{ supplier.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Status
                                </label>
                                <select
                                    v-model="selectedStatus"
                                    @change="applyFilters"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                >
                                    <option value="">Todos</option>
                                    <option value="Pendente">Pendente</option>
                                    <option value="Concluído">Concluído</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Período
                                </label>
                                <flat-pickr
                                    v-model="selectedDateFrom"
                                    :config="dateConfig"
                                    @on-change="applyFilters"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                    placeholder="Selecione o período"
                                />
                            </div>
                            <div class="flex items-end">
                                <button
                                    @click="selectedSupplier = ''; selectedStatus = ''; selectedDateFrom = ''; selectedDateTo = ''; applyFilters()"
                                    class="w-full inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50"
                                >
                                    Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="text-gray-900">ID</th>
                                        <th class="text-gray-900">Fornecedor</th>
                                        <th class="text-gray-900">Data</th>
                                        <th class="text-gray-900">Valor Total</th>
                                        <th class="text-gray-900">Status</th>
                                        <th class="text-gray-900">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-100 border-b border-gray-200">
                                        <td class="text-gray-900 font-medium">#{{ order.id }}</td>
                                        <td>
                                            <span class="inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                                {{ order.supplier?.name }}
                                            </span>
                                        </td>
                                        <td class="text-gray-700">{{ formatDate(order.date) }}</td>
                                        <td class="text-gray-900 font-medium">{{ formatCurrency(order.total_amount) }}</td>
                                        <td>
                                            <span
                                                class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium"
                                                :class="getStatusBadgeClass(order.status)"
                                            >
                                                {{ order.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="route('orders.show', order.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                >
                                                    Ver
                                                </Link>
                                                <Link
                                                    :href="route('orders.edit', order.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    @click="deleteOrder(order.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                                                >
                                                    Deletar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="orders.data.length === 0">
                                        <td colspan="6" class="text-center py-4 text-gray-700">
                                            Nenhum pedido encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-6 gap-2">
                            <template v-for="(link, index) in orders.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-semibold transition duration-150 ease-in-out"
                                    :class="link.active
                                        ? 'border-transparent bg-gray-800 text-white hover:bg-gray-700'
                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

