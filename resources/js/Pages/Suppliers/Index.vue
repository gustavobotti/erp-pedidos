<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCNPJ, formatCEP } from '@/Composables/useMasks';
import { onMounted } from 'vue';

const props = defineProps({
    suppliers: Object,
});

onMounted(() => {
    console.log('Suppliers Index mounted');
    if (props.suppliers?.data?.[0]) {
        const firstSupplier = props.suppliers.data[0];
        console.log('First supplier CNPJ:', firstSupplier.cnpj);
        console.log('Formatted CNPJ:', formatCNPJ(firstSupplier.cnpj));
        console.log('First supplier CEP:', firstSupplier.cep);
        console.log('Formatted CEP:', formatCEP(firstSupplier.cep));
    }
});

const deleteSupplier = (id) => {
    if (confirm('Tem certeza que deseja excluir este fornecedor?')) {
        router.delete(route('suppliers.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getStatusBadge = (status) => {
    return status ? 'badge-success' : 'badge-error';
};
</script>

<template>
    <Head title="Fornecedores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gerenciar Fornecedores
                </h2>
                <Link
                    :href="route('suppliers.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Novo Fornecedor
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="text-gray-900">Nome</th>
                                        <th class="text-gray-900">CNPJ</th>
                                        <th class="text-gray-900">CEP</th>
                                        <th class="text-gray-900">Status</th>
                                        <th class="text-gray-900">Vendedores</th>
                                        <th class="text-gray-900">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-gray-100 border-b border-gray-200">
                                        <td class="text-gray-900 font-medium">{{ supplier.name }}</td>
                                        <td class="text-gray-700">{{ formatCNPJ(supplier.cnpj) }}</td>
                                        <td class="text-gray-700">{{ formatCEP(supplier.cep) }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusBadge(supplier.status)">
                                                {{ supplier.status ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ supplier.users?.length || 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="route('suppliers.edit', supplier.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    @click="deleteSupplier(supplier.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                                                >
                                                    Deletar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="6" class="text-center py-4 text-gray-700">
                                            Nenhum fornecedor encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div v-if="suppliers.links && suppliers.links.length > 3" class="flex justify-center mt-6 gap-2">
                            <template v-for="(link, index) in suppliers.links" :key="index">
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

