<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/Layout/PageHeader.vue';
import ContentCard from '@/Components/Layout/ContentCard.vue';
import SearchInput from '@/Components/Form/SearchInput.vue';
import StatusBadge from '@/Components/Badge/StatusBadge.vue';
import InfoBadge from '@/Components/Badge/InfoBadge.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import TableRow from '@/Components/DataTable/TableRow.vue';
import TableCell from '@/Components/DataTable/TableCell.vue';
import Pagination from '@/Components/Pagination/Pagination.vue';
import EmptyState from '@/Components/Table/EmptyState.vue';
import ActionButtons from '@/Components/Actions/ActionButtons.vue';
import { formatCNPJ, formatCEP } from '@/Composables/useMasks';
import { useConfirmDelete } from '@/Composables/useConfirmDelete';
import { useFilters } from '@/Composables/useFilters';

const props = defineProps({
    suppliers: Object,
    filters: Object,
});

const { deleteResource } = useConfirmDelete();

const { filters, applyFilters } = useFilters('suppliers.index', {
    search: props.filters?.search || '',
});

const headers = [
    { label: 'Nome', class: 'text-gray-900' },
    { label: 'CNPJ', class: 'text-gray-900' },
    { label: 'CEP', class: 'text-gray-900' },
    { label: 'Status', class: 'text-gray-900' },
    { label: 'Vendedores', class: 'text-gray-900' },
    { label: 'Ações', class: 'text-gray-900' },
];
</script>

<template>
    <Head title="Fornecedores" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Gerenciar Fornecedores">
                <template #actions>
                    <Link
                        :href="route('suppliers.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Novo Fornecedor
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Search Filter -->
                <ContentCard>
                    <SearchInput
                        v-model="filters.search"
                        label="Pesquisar por nome:"
                        placeholder="Digite o nome do fornecedor..."
                        @update:model-value="applyFilters"
                    />
                </ContentCard>

                <!-- Suppliers Table -->
                <ContentCard>
                    <DataTable :headers="headers">
                        <template #body>
                            <TableRow v-for="supplier in suppliers.data" :key="supplier.id">
                                <TableCell variant="bold">{{ supplier.name }}</TableCell>
                                <TableCell>{{ formatCNPJ(supplier.cnpj) }}</TableCell>
                                <TableCell>{{ formatCEP(supplier.cep) }}</TableCell>
                                <TableCell>
                                    <StatusBadge :status="supplier.status" />
                                </TableCell>
                                <TableCell>
                                    <InfoBadge variant="info">
                                        {{ supplier.users?.length || 0 }}
                                    </InfoBadge>
                                </TableCell>
                                <TableCell>
                                    <ActionButtons
                                        :edit-route="route('suppliers.edit', supplier.id)"
                                        :on-delete="() => deleteResource('suppliers.destroy', supplier.id)"
                                    />
                                </TableCell>
                            </TableRow>
                            <EmptyState
                                v-if="suppliers.data.length === 0"
                                message="Nenhum fornecedor encontrado."
                            />
                        </template>
                    </DataTable>

                    <Pagination :data="suppliers" resource-name="fornecedores" />
                </ContentCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

