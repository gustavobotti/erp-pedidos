<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BarChart from '@/Components/BarChart.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    dashboardData: {
        type: Object,
        required: true
    }
});

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};

// Top suppliers chart data
const topSuppliersLabels = computed(() =>
    props.dashboardData.top_suppliers.map(s => s.name)
);

const topSuppliersData = computed(() =>
    props.dashboardData.top_suppliers.map(s => s.total_sales)
);

// Refresh dashboard
const refreshDashboard = () => {
    router.post(route('dashboard.refresh'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message is handled by the toast notification
        }
    });
};

// Status badge colors
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'Concluído':
            return 'badge-success';
        case 'Pendente':
            return 'badge-warning';
        case 'Cancelado':
            return 'badge-error';
        default:
            return 'badge-ghost';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        Dashboard
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Visão geral dos últimos 30 dias</p>
                </div>
                <button
                    @click="refreshDashboard"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Atualizar
                </button>
            </div>
        </template>

        <div class="py-12 bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Orders Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 text-white transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Total de Pedidos</p>
                                <p class="text-4xl font-bold mt-2">{{ dashboardData.total_orders }}</p>
                                <p class="text-blue-100 text-xs mt-2">Últimos 30 dias</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 text-white transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Receita Total</p>
                                <p class="text-4xl font-bold mt-2">{{ formatCurrency(dashboardData.total_revenue) }}</p>
                                <p class="text-green-100 text-xs mt-2">Pedidos ativos</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Top Supplier Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 text-white transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">Fornecedor Líder</p>
                                <p class="text-2xl font-bold mt-2 truncate" :title="dashboardData.top_suppliers[0]?.name || 'N/A'">
                                    {{ dashboardData.top_suppliers[0]?.name || 'N/A' }}
                                </p>
                                <p class="text-purple-100 text-xs mt-2" v-if="dashboardData.top_suppliers[0]">
                                    {{ formatCurrency(dashboardData.top_suppliers[0].total_sales) }}
                                </p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4 ml-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 10 Suppliers Chart -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Top 10 Fornecedores por Volume de Vendas</h3>
                    </div>
                    <BarChart
                        v-if="topSuppliersData.length > 0"
                        title=""
                        :labels="topSuppliersLabels"
                        :data="topSuppliersData"
                        label="Vendas (R$)"
                        backgroundColor="rgba(59, 130, 246, 0.8)"
                        borderColor="rgba(59, 130, 246, 1)"
                    />
                    <div v-else class="text-center text-gray-500 py-12 bg-gray-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-lg font-medium">Nenhum dado disponível para exibir</p>
                    </div>
                </div>

                <!-- Orders by Status -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Pedidos por Status</h3>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Quantidade</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="status in dashboardData.orders_by_status" :key="status.status" class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge" :class="getStatusBadgeClass(status.status)">
                                            {{ status.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ status.count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">{{ formatCurrency(status.total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8 border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-cyan-500 to-blue-500 rounded-lg p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Pedidos Recentes</h3>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Fornecedor</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Vendedor</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in dashboardData.recent_orders" :key="order.id" class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">#{{ order.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ order.supplier }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.user }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">{{ formatCurrency(order.total_amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge" :class="getStatusBadgeClass(order.status)">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Cache Info -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-6 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-blue-800 mb-1">💡 Informação sobre Cache</h3>
                            <p class="text-sm text-blue-700">
                                Os dados são armazenados em cache no Redis por 1 hora para melhorar o desempenho.
                                Clique em "Atualizar" para forçar uma atualização dos dados.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
