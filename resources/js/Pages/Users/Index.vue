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
import { useConfirmDelete } from '@/Composables/useConfirmDelete';
import { useFilters } from '@/Composables/useFilters';

const props = defineProps({
    users: Object,
    filters: Object,
});

const { deleteResource } = useConfirmDelete();

const { filters, applyFilters } = useFilters('users.index', {
    search: props.filters?.search || '',
});

const headers = [
    { label: 'ID', class: 'text-gray-900' },
    { label: 'Nome', class: 'text-gray-900' },
    { label: 'Email', class: 'text-gray-900' },
    { label: 'Tipo', class: 'text-gray-900' },
    { label: 'Status', class: 'text-gray-900' },
    { label: 'Ações', class: 'text-gray-900' },
];
</script>

<template>
    <Head title="Usuários" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Gerenciar Usuários">
                <template #actions>
                    <Link
                        :href="route('users.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Novo Usuário
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
                        placeholder="Digite o nome do usuário..."
                        label="Pesquisar por nome:"
                        @update:model-value="applyFilters"
                    />
                </ContentCard>

                <!-- Users Table -->
                <ContentCard>
                    <DataTable :headers="headers">
                        <template #body>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell>{{ user.id }}</TableCell>
                                <TableCell variant="bold">{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <InfoBadge :variant="user.type === 'admin' ? 'indigo' : 'gray'">
                                        {{ user.type }}
                                    </InfoBadge>
                                </TableCell>
                                <TableCell>
                                    <StatusBadge :status="user.status" />
                                </TableCell>
                                <TableCell>
                                    <ActionButtons
                                        :edit-route="route('users.edit', user.id)"
                                        :on-delete="() => deleteResource('users.destroy', user.id)"
                                    />
                                </TableCell>
                            </TableRow>
                            <EmptyState
                                v-if="users.data.length === 0"
                                message="Nenhum usuário encontrado."
                            />
                        </template>
                    </DataTable>

                    <Pagination :data="users" resource-name="usuários" />
                </ContentCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

