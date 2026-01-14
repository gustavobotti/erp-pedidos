<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    initialProducts: {
        type: Array,
        default: () => [],
    },
    submitLabel: {
        type: String,
        default: 'Salvar',
    },
    cancelRoute: {
        type: String,
        default: 'orders.index',
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit']);

const productsAvailable = ref(props.initialProducts || []);
const loadingProducts = ref(false);

// Watch for supplier changes to load products from cache
watch(() => props.form.supplier_id, async (newSupplierId, oldSupplierId) => {
    if (newSupplierId && (!props.isEdit || newSupplierId !== oldSupplierId)) {
        loadingProducts.value = true;
        try {
            const response = await axios.get(`/api/suppliers/${newSupplierId}/products`);
            productsAvailable.value = response.data;
            // Clear selected products when changing supplier (only if not editing or supplier changed)
            if (!props.isEdit || (props.isEdit && newSupplierId !== oldSupplierId)) {
                props.form.products = [];
            }
        } catch (error) {
            console.error('Error loading products:', error);
            alert('Erro ao carregar produtos do fornecedor.');
        } finally {
            loadingProducts.value = false;
        }
    }
});

const addProduct = () => {
    props.form.products.push({
        product_id: '',
        quantity: 1,
        unit_price: 0,
    });
};

const removeProduct = (index) => {
    props.form.products.splice(index, 1);
};

const onProductChange = (index) => {
    const selectedProduct = productsAvailable.value.find(
        p => p.id === Number(props.form.products[index].product_id)
    );
    if (selectedProduct) {
        props.form.products[index].unit_price = parseFloat(selectedProduct.price);
    }
};

const calculateSubtotal = (product) => {
    return (product.quantity || 0) * (product.unit_price || 0);
};

const totalAmount = computed(() => {
    return props.form.products.reduce((sum, product) => {
        return sum + calculateSubtotal(product);
    }, 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};

const handleSubmit = () => {
    emit('submit');
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Dados do Pedido -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Dados do Pedido
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fornecedor <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.supplier_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                            :class="{ 'border-red-500': form.errors.supplier_id }"
                            required
                        >
                            <option value="">Selecione um fornecedor</option>
                            <option
                                v-for="supplier in suppliers"
                                :key="supplier.id"
                                :value="supplier.id"
                            >
                                {{ supplier.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.supplier_id" class="mt-1 text-sm text-red-500">
                            {{ form.errors.supplier_id }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Observação
                        </label>
                        <textarea
                            v-model="form.observation"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                            placeholder="Digite observações sobre o pedido"
                        ></textarea>
                        <p v-if="form.errors.observation" class="mt-1 text-sm text-red-500">
                            {{ form.errors.observation }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos do Pedido -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Produtos do Pedido
                    </h3>
                    <button
                        type="button"
                        @click="addProduct"
                        :disabled="!form.supplier_id || loadingProducts"
                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        + Adicionar Produto
                    </button>
                </div>

                <div v-if="loadingProducts" class="text-center py-4">
                    <p class="text-gray-600">Carregando produtos...</p>
                </div>

                <div v-else-if="!form.supplier_id" class="text-center py-4">
                    <p class="text-gray-600">Selecione um fornecedor para adicionar produtos</p>
                </div>

                <div v-else-if="form.products.length === 0" class="text-center py-4">
                    <p class="text-gray-600">Nenhum produto adicionado. Clique em "Adicionar Produto" para começar.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(product, index) in form.products"
                        :key="index"
                        class="border border-gray-300 rounded-lg p-4 bg-gray-50"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Produto <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="product.product_id"
                                    @change="onProductChange(index)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                    required
                                >
                                    <option value="">Selecione um produto</option>
                                    <option
                                        v-for="p in productsAvailable"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.name }} - {{ p.reference }} ({{ p.color }})
                                    </option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantidade <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model.number="product.quantity"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                    required
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Valor Unitário <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model.number="product.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                    readonly
                                    required
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subtotal
                                </label>
                                <div class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-gray-900 font-medium">
                                    {{ formatCurrency(calculateSubtotal(product)) }}
                                </div>
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button
                                    type="button"
                                    @click="removeProduct(index)"
                                    class="w-full inline-flex justify-center items-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-xs font-semibold text-white transition duration-150 ease-in-out hover:bg-red-500"
                                    title="Remover produto"
                                >
                                    ❌
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <p v-if="form.errors.products" class="mt-2 text-sm text-red-500">
                    {{ form.errors.products }}
                </p>
            </div>
        </div>

        <!-- Resumo -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Resumo
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Total de Itens
                        </label>
                        <div class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-gray-900 font-medium">
                            {{ form.products.length }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Valor Total
                        </label>
                        <div class="w-full rounded-md border border-gray-300 bg-indigo-50 px-3 py-2 text-gray-900 font-bold text-lg">
                            {{ formatCurrency(totalAmount) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 flex items-center gap-4">
                <button
                    type="submit"
                    :disabled="form.processing || form.products.length === 0"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ submitLabel }}
                </button>
                <Link
                    :href="route(cancelRoute)"
                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                >
                    Cancelar
                </Link>
            </div>
        </div>
    </form>
</template>

