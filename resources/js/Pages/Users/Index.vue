<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    users: Object,
});

const deleteUser = (userId) => {
    if (confirm('Tem certeza que deseja deletar este usuário?')) {
        router.delete(route('users.destroy', userId), {
            preserveScroll: true,
        });
    }
};

const getStatusBadge = (status) => {
    return status ? 'badge-success' : 'badge-error';
};

const getTypeBadge = (type) => {
    return type === 'admin' ? 'badge-primary' : 'badge-secondary';
};
</script>

<template>
    <Head title="Usuários" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gerenciar Usuários
                </h2>
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Novo Usuário
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
                                        <th class="text-gray-900">ID</th>
                                        <th class="text-gray-900">Nome</th>
                                        <th class="text-gray-900">Email</th>
                                        <th class="text-gray-900">Tipo</th>
                                        <th class="text-gray-900">Status</th>
                                        <th class="text-gray-900">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-100 border-b border-gray-200">
                                        <td class="text-gray-900">{{ user.id }}</td>
                                        <td class="text-gray-900 font-medium">{{ user.name }}</td>
                                        <td class="text-gray-700">{{ user.email }}</td>
                                        <td>
                                            <span class="badge" :class="getTypeBadge(user.type)">
                                                {{ user.type }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getStatusBadge(user.status)">
                                                {{ user.status ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="route('users.edit', user.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    @click="deleteUser(user.id)"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                                                >
                                                    Deletar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0">
                                        <td colspan="6" class="text-center py-4 text-gray-700">
                                            Nenhum usuário encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div v-if="users.links && users.links.length > 3" class="flex justify-center mt-6 gap-2">
                            <template v-for="(link, index) in users.links" :key="index">
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

