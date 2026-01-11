<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatCNPJ } from '@/Composables/useMasks';

const props = defineProps({
    user: Object,
    suppliers: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    type: props.user.type,
    status: props.user.status,
    supplier_ids: props.user.suppliers ? props.user.suppliers.map(s => s.id) : [],
});

const toggleSupplier = (supplierId) => {
    const index = form.supplier_ids.indexOf(supplierId);
    if (index > -1) {
        form.supplier_ids.splice(index, 1);
    } else {
        form.supplier_ids.push(supplierId);
    }
};

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="Editar Usuário" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Usuário
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- Senha -->
                        <div>
                            <InputLabel for="password" value="Nova Senha (deixe em branco para não alterar)" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <!-- Confirmar Senha -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Nova Senha" />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <!-- Tipo -->
                        <div>
                            <InputLabel for="type" value="Tipo" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                required
                            >
                                <option value="vendedor">Vendedor</option>
                                <option value="admin">Admin</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-3">
                            <input
                                id="status"
                                v-model="form.status"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <InputLabel for="status" value="Usuário Ativo" class="!mb-0" />
                        </div>
                        <InputError :message="form.errors.status" class="mt-2" />

                        <!-- Fornecedores (apenas para vendedores) -->
                        <div v-if="form.type === 'vendedor' && suppliers && suppliers.length > 0" class="border-t pt-6">
                            <InputLabel value="Fornecedores Vinculados" class="mb-3" />
                            <div class="space-y-2 max-h-60 overflow-y-auto bg-gray-50 p-4 rounded-md">
                                <div
                                    v-for="supplier in suppliers"
                                    :key="supplier.id"
                                    class="flex items-start"
                                >
                                    <div class="flex items-center h-5">
                                        <input
                                            :id="`supplier-${supplier.id}`"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            :checked="form.supplier_ids.includes(supplier.id)"
                                            @change="toggleSupplier(supplier.id)"
                                        />
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label :for="`supplier-${supplier.id}`" class="font-medium text-gray-700 cursor-pointer">
                                            {{ supplier.name }}
                                        </label>
                                        <p class="text-gray-500">{{ formatCNPJ(supplier.cnpj) }}</p>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.supplier_ids" class="mt-2" />
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Atualizar Usuário
                            </PrimaryButton>

                            <Link
                                :href="route('users.index')"
                                class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

