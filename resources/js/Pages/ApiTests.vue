<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { Head } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const { success, error, info } = useToast();

// Estado para armazenar o token
const authToken = ref(localStorage.getItem('api_token') || '');
const loading = ref({});

// Dados hard-coded iguais ao do seeder para teste
const testData = {
    auth: {
        email: 'admin@erp.com',
        password: 'password'
    },
    supplier: {
        cnpj: '97150072000157' // TechSupply Ltda
    },
    orderId: 1,
    newOrder: {
        supplier_id: 1, // TechSupply Ltda
        date: '2026-01-13',
        status: 'Pendente',
        products: [
            {
                id: 1,
                quantity: 5,
                unit_price: 100.00
            }
        ]
    },
    updateOrder: {
        status: 'Concluído',
        date: '2026-01-13'
    }
};

// Função helper para fazer requisições
const makeRequest = async (endpoint, method = 'GET', data = null, useAuth = true) => {
    const loadingKey = `${method}_${endpoint}`;
    loading.value[loadingKey] = true;

    try {
        const config = {
            method,
            url: `/api/v1${endpoint}`,
            headers: {}
        };

        if (useAuth && authToken.value) {
            config.headers['Authorization'] = `Bearer ${authToken.value}`;
        }

        if (data) {
            config.data = data;
            config.headers['Content-Type'] = 'application/json';
        }

        const response = await axios(config);

        success({
            status: response.status,
            data: response.data
        }, 10000);

        return response.data;
    } catch (err) {
        const errorMessage = {
            status: err.response?.status || 'Network Error',
            message: err.response?.data?.message || err.message,
            errors: err.response?.data?.errors || null,
            data: err.response?.data || null
        };

        error(errorMessage, 10000);
        throw err;
    } finally {
        loading.value[loadingKey] = false;
    }
};

// Requisições da API
const testLogin = async () => {
    try {
        const response = await makeRequest('/autenticacao', 'POST', testData.auth, false);
        if (response.token) {
            authToken.value = response.token;
            localStorage.setItem('api_token', response.token);
        }
    } catch (err) {
        console.error('Login failed:', err);
    }
};

const testGetOrdersBySupplier = async () => {
    await makeRequest(`/fornecedor/${testData.supplier.cnpj}/pedidos`, 'GET');
};

const testGetOrderById = async () => {
    await makeRequest(`/pedidos/${testData.orderId}`, 'GET');
};

const testCreateOrder = async () => {
    await makeRequest('/pedidos', 'POST', testData.newOrder);
};

const testUpdateOrder = async () => {
    await makeRequest(`/pedidos/${testData.orderId}`, 'PUT', testData.updateOrder);
};

const testDeleteOrder = async () => {
    if (confirm('Tem certeza que deseja deletar o pedido? Esta ação não pode ser desfeita.')) {
        await makeRequest(`/pedidos/${testData.orderId}`, 'DELETE');
    }
};

const clearToken = () => {
    authToken.value = '';
    localStorage.removeItem('api_token');
    info('Token removido!', 3000);
};

const isLoading = (endpoint, method) => {
    const loadingKey = `${method}_${endpoint}`;
    return loading.value[loadingKey] || false;
};
</script>

<template>
    <Head title="Testes API" />

    <AuthenticatedLayout>
        <ToastNotification />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Testes API
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Token Status -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold mb-2 text-gray-800">Status de Autenticação</h3>
                                    <p class="text-sm text-gray-700">
                                        Token:
                                        <span :class="authToken ? 'text-green-700 font-mono font-semibold' : 'text-red-700 font-semibold'">
                                            {{ authToken ?? 'Não autenticado' }}
                                        </span>
                                    </p>
                                </div>
                                <button
                                    v-if="authToken"
                                    @click="clearToken"
                                    class="btn btn-sm btn-outline btn-error"
                                >
                                    Limpar Token
                                </button>
                            </div>
                        </div>

                        <!-- API Endpoints -->
                        <div class="space-y-4">
                            <!-- POST /api/v1/autenticacao -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="testLogin"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading('/autenticacao', 'POST') }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-blue-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-success font-bold text-white">POST</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/autenticacao</span>
                                        <span class="text-gray-600 text-sm">- Autenticar usuário</span>
                                    </div>
                                    <div v-if="isLoading('/autenticacao', 'POST')" class="loading loading-spinner loading-sm"></div>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Dados enviados:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">{{ JSON.stringify(testData.auth, null, 2) }}</pre>
                                </div>
                            </div>

                            <!-- GET /api/v1/fornecedor/{cnpj}/pedidos -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="!authToken ? null : testGetOrdersBySupplier()"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading(`/fornecedor/${testData.supplier.cnpj}/pedidos`, 'GET') || !authToken }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-blue-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-info font-bold text-white">GET</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/fornecedor/{{ testData.supplier.cnpj }}/pedidos</span>
                                        <span class="text-gray-600 text-sm">- Listar pedidos do fornecedor</span>
                                    </div>
                                    <div v-if="isLoading(`/fornecedor/${testData.supplier.cnpj}/pedidos`, 'GET')" class="loading loading-spinner loading-sm"></div>
                                    <span v-else-if="!authToken" class="text-red-600 text-xs font-semibold">Requer autenticação</span>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Parâmetros:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">CNPJ: {{ testData.supplier.cnpj }} (TechSupply Ltda)</pre>
                                </div>
                            </div>

                            <!-- GET /api/v1/pedidos/{id} -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="!authToken ? null : testGetOrderById()"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading(`/pedidos/${testData.orderId}`, 'GET') || !authToken }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-blue-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-info font-bold text-white">GET</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/pedidos/{{ testData.orderId }}</span>
                                        <span class="text-gray-600 text-sm">- Obter pedido específico</span>
                                    </div>
                                    <div v-if="isLoading(`/pedidos/${testData.orderId}`, 'GET')" class="loading loading-spinner loading-sm"></div>
                                    <span v-else-if="!authToken" class="text-red-600 text-xs font-semibold">Requer autenticação</span>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Parâmetros:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">ID: {{ testData.orderId }}</pre>
                                </div>
                            </div>

                            <!-- POST /api/v1/pedidos -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="!authToken ? null : testCreateOrder()"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading('/pedidos', 'POST') || !authToken }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-blue-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-success font-bold text-white">POST</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/pedidos</span>
                                        <span class="text-gray-600 text-sm">- Criar novo pedido</span>
                                    </div>
                                    <div v-if="isLoading('/pedidos', 'POST')" class="loading loading-spinner loading-sm"></div>
                                    <span v-else-if="!authToken" class="text-red-600 text-xs font-semibold">Requer autenticação</span>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Dados enviados:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">{{ JSON.stringify(testData.newOrder, null, 2) }}</pre>
                                </div>
                            </div>

                            <!-- PUT /api/v1/pedidos/{id} -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="!authToken ? null : testUpdateOrder()"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading(`/pedidos/${testData.orderId}`, 'PUT') || !authToken }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-blue-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-warning font-bold text-gray-800">PUT</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/pedidos/{{ testData.orderId }}</span>
                                        <span class="text-gray-600 text-sm">- Atualizar pedido</span>
                                    </div>
                                    <div v-if="isLoading(`/pedidos/${testData.orderId}`, 'PUT')" class="loading loading-spinner loading-sm"></div>
                                    <span v-else-if="!authToken" class="text-red-600 text-xs font-semibold">Requer autenticação</span>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Dados enviados:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">{{ JSON.stringify(testData.updateOrder, null, 2) }}</pre>
                                </div>
                            </div>

                            <!-- DELETE /api/v1/pedidos/{id} -->
                            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div
                                    @click="!authToken ? null : testDeleteOrder()"
                                    :class="{ 'opacity-50 cursor-not-allowed': isLoading(`/pedidos/${testData.orderId}`, 'DELETE') || !authToken }"
                                    class="w-full flex items-center justify-between p-4 bg-white hover:bg-red-50 transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center space-x-4">
                                        <span class="badge badge-error font-bold text-white">DELETE</span>
                                        <span class="font-mono text-sm text-gray-800 font-semibold">/api/v1/pedidos/{{ testData.orderId }}</span>
                                        <span class="text-gray-600 text-sm">- Deletar pedido</span>
                                    </div>
                                    <div v-if="isLoading(`/pedidos/${testData.orderId}`, 'DELETE')" class="loading loading-spinner loading-sm"></div>
                                    <span v-else-if="!authToken" class="text-red-600 text-xs font-semibold">Requer autenticação</span>
                                </div>
                                <div class="px-4 pb-4 bg-gray-100 text-xs">
                                    <p class="font-semibold mb-1 text-gray-800">Parâmetros:</p>
                                    <pre class="bg-white p-2 rounded border border-gray-300 text-gray-900">ID: {{ testData.orderId }}</pre>
                                    <p class="text-red-700 mt-2 font-semibold">⚠️ Esta ação não pode ser desfeita!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-semibold text-blue-900 mb-2">📝 Notas:</h4>
                            <ul class="text-sm text-gray-800 space-y-1 list-disc list-inside">
                                <li>Primeiro, faça login para obter o token de autenticação</li>
                                <li>O token é salvo automaticamente no localStorage</li>
                                <li>Todos os endpoints (exceto login) requerem autenticação</li>
                                <li>As respostas da API são exibidas em toasts no canto superior direito</li>
                                <li>Dados de teste são baseados no DatabaseSeederWithFactories</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

