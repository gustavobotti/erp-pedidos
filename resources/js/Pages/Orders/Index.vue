<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Components/Layout/PageHeader.vue';
import ContentCard from '@/Components/Layout/ContentCard.vue';
import FilterCard from '@/Components/Filter/FilterCard.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import DateRangePicker from '@/Components/Form/DateRangePicker.vue';
import StatusBadge from '@/Components/Badge/StatusBadge.vue';
import InfoBadge from '@/Components/Badge/InfoBadge.vue';
import DataTable from '@/Components/DataTable/DataTable.vue';
import TableRow from '@/Components/DataTable/TableRow.vue';
import TableCell from '@/Components/DataTable/TableCell.vue';
import Pagination from '@/Components/Pagination/Pagination.vue';
import EmptyState from '@/Components/Table/EmptyState.vue';
import ActionButtons from '@/Components/Actions/ActionButtons.vue';
import { useFilters } from '@/Composables/useFilters';
import { useFormatters } from '@/Composables/useFormatters';
import { useConfirmDelete } from '@/Composables/useConfirmDelete';

const props = defineProps({
    orders: Object,
    suppliers: Array,
    filters: Object,
});

const { formatDate, formatCurrency } = useFormatters();
const { deleteResource } = useConfirmDelete();

const { filters, applyFilters, clearFilters, setFilter } = useFilters('orders.index', {
    supplier_id: props.filters?.supplier_id || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const statusOptions = [
    { value: 'Pendente', label: 'Pendente' },
    { value: 'Concluído', label: 'Concluído' },
    { value: 'Cancelado', label: 'Cancelado' },
];

const supplierOptions = props.suppliers.map(s => ({
    value: s.id,
    label: s.name,
}));

const handleDateChange = ({ dateStr }) => {
    if (dateStr) {
        const dates = dateStr.split(' a ');
        setFilter('date_from', dates[0] || '');
        setFilter('date_to', dates[1] || dates[0] || '');
    } else {
        setFilter('date_from', '');
        setFilter('date_to', '');
    }
    applyFilters();
};

const sendDailyReport = () => {
    if (confirm('Deseja receber o relatório dos últimos 7 dias no seu email?')) {
        router.post(route('orders.send-daily-report'), {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Relatório enviado com sucesso! Verifique seu email.');
            },
            onError: () => {
                alert('Erro ao enviar relatório. Tente novamente.');
            }
        });
    }
};

const headers = [
    { label: 'ID', class: 'text-gray-900' },
    { label: 'Fornecedor', class: 'text-gray-900' },
    { label: 'Data', class: 'text-gray-900' },
    { label: 'Valor Total', class: 'text-gray-900' },
    { label: 'Status', class: 'text-gray-900' },
    { label: 'Ações', class: 'text-gray-900' },
];
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Gerenciar Pedidos">
                <template #actions>
                    <button
                        @click="sendDailyReport"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Enviar Relatório
                    </button>
                    <Link
                        :href="route('orders.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Novo Pedido
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <FilterCard>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <SelectInput
                            v-model="filters.supplier_id"
                            :options="supplierOptions"
                            label="Fornecedor"
                            placeholder="Todos"
                            @change="applyFilters"
                        />

                        <SelectInput
                            v-model="filters.status"
                            :options="statusOptions"
                            label="Status"
                            placeholder="Todos"
                            @change="applyFilters"
                        />

                        <DateRangePicker
                            :model-value="`${filters.date_from}${filters.date_to ? ' a ' + filters.date_to : ''}`"
                            label="Período"
                            placeholder="Selecione o período"
                            @change="handleDateChange"
                        />

                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full inline-flex justify-center items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50"
                            >
                                Limpar Filtros
                            </button>
                        </div>
                    </div>
                </FilterCard>

                <!-- Orders Table -->
                <ContentCard>
                    <DataTable :headers="headers">
                        <template #body>
                            <TableRow v-for="order in orders.data" :key="order.id">
                                <TableCell variant="bold">#{{ order.id }}</TableCell>
                                <TableCell>
                                    <InfoBadge variant="indigo">
                                        {{ order.supplier?.name }}
                                    </InfoBadge>
                                </TableCell>
                                <TableCell>{{ formatDate(order.date) }}</TableCell>
                                <TableCell variant="medium">{{ formatCurrency(order.total_amount) }}</TableCell>
                                <TableCell>
                                    <StatusBadge :status="order.status" />
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('orders.show', order.id)"
                                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Ver
                                        </Link>
                                        <ActionButtons
                                            :edit-route="route('orders.edit', order.id)"
                                            :on-delete="() => deleteResource('orders.destroy', order.id)"
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <EmptyState
                                v-if="orders.data.length === 0"
                                message="Nenhum pedido encontrado."
                            />
                        </template>
                    </DataTable>

                    <Pagination :data="orders" resource-name="pedidos" />
                </ContentCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

