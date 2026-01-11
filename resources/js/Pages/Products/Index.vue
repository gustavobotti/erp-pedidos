<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    suppliers: Array,
    filters: Object,
});

const selectedSupplier = ref(props.filters?.supplier_id || '');

const filterBySupplier = () => {
    router.get(route('products.index'), {
        supplier_id: selectedSupplier.value
    }, {
        preserveState: true,
    });
};

const deleteProduct = (id) => {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        router.delete(route('products.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Produtos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gerenciar Produtos
                </h2>
                <Link
                    :href="route('products.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Novo Produto
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">
                                Filtrar por fornecedor:
                            </label>
                            <select
                                v-model="selectedSupplier"
                                @change="filterBySupplier"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
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
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="text-gray-900">Referência</th>
                                        <th class="text-gray-900">Nome</th>
                                        <th class="text-gray-900">Cor</th>
                                        <th class="text-gray-900">Preço</th>
                                        <th class="text-gray-900">Fornecedor</th>
                                        <th class="text-gray-900">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-100 border-b border-gray-200">
                                        <td class="text-gray-700">{{ product.reference }}</td>
                                        <td class="text-gray-900 font-medium">{{ product.name }}</td>
                                        <td class="text-gray-700">{{ product.color }}</td>
                                        <td class="text-gray-900">R$ {{ parseFloat(product.price).toFixed(2) }}</td>
                                        <td>
                                            <span class="inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                                {{ product.supplier?.name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="route('products.edit', product.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    @click="deleteProduct(product.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                                                >
                                                    Deletar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="6" class="text-center py-4 text-gray-700">
                                            Nenhum produto encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div v-if="products.links && products.links.length > 3" class="flex justify-center mt-6 gap-2">
                            <template v-for="(link, index) in products.links" :key="index">
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

