<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/Layout/PageHeader.vue';
import ContentCard from '@/Components/Layout/ContentCard.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import TableRow from '@/Components/DataTable/TableRow.vue';
import TableCell from '@/Components/DataTable/TableCell.vue';
import Pagination from '@/Components/Pagination/Pagination.vue';
import EmptyState from '@/Components/Table/EmptyState.vue';
import ActionButtons from '@/Components/Actions/ActionButtons.vue';
import InfoBadge from '@/Components/Badge/InfoBadge.vue';
import { useConfirmDelete } from '@/Composables/useConfirmDelete';
import { useFormatters } from '@/Composables/useFormatters';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    products: Object,
    suppliers: Array,
    filters: Object,
});

const { deleteResource } = useConfirmDelete();
const { formatCurrency } = useFormatters();

const selectedSupplier = ref(props.filters?.supplier_id || '');

watch(selectedSupplier, (value) => {
    router.get(route('products.index'), {
        supplier_id: value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const headers = [
    { label: 'Referência', class: 'text-gray-900' },
    { label: 'Nome', class: 'text-gray-900' },
    { label: 'Cor', class: 'text-gray-900' },
    { label: 'Preço', class: 'text-gray-900' },
    { label: 'Fornecedor', class: 'text-gray-900' },
    { label: 'Ações', class: 'text-gray-900' },
];

const supplierOptions = computed(() =>
    props.suppliers.map(s => ({ value: s.id, label: s.name }))
);
</script>

<template>
    <Head title="Produtos" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Gerenciar Produtos">
                <template #actions>
                    <Link
                        :href="route('products.import')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Importar Produtos
                    </Link>
                    <Link
                        :href="route('products.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Novo Produto
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Filter -->
                <ContentCard>
                    <SelectInput
                        v-model="filters.supplier_id"
                        :options="supplierOptions"
                        label="Filtrar por fornecedor:"
                        placeholder="Todos"
                        value-key="value"
                        label-key="label"
                        @change="applyFilters"
                    />
                </ContentCard>

                <!-- Products Table -->
                <ContentCard>
                    <DataTable :headers="headers">
                        <template #body>
                            <TableRow v-for="product in products.data" :key="product.id">
                                <TableCell>{{ product.reference }}</TableCell>
                                <TableCell variant="bold">{{ product.name }}</TableCell>
                                <TableCell>{{ product.color }}</TableCell>
                                <TableCell variant="medium">{{ formatCurrency(product.price) }}</TableCell>
                                <TableCell>
                                    <InfoBadge variant="info">
                                        {{ product.supplier?.name }}
                                    </InfoBadge>
                                </TableCell>
                                <TableCell>
                                    <ActionButtons
                                        :edit-route="route('products.edit', product.id)"
                                        :on-delete="() => deleteResource('products.destroy', product.id)"
                                    />
                                </TableCell>
                            </TableRow>
                            <EmptyState
                                v-if="products.data.length === 0"
                                message="Nenhum produto encontrado."
                            />
                        </template>
                    </DataTable>

                    <Pagination :data="products" resource-name="produtos" />
                </ContentCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
