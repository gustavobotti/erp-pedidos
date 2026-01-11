<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    suppliers: Array,
});

const form = useForm({
    supplier_id: props.product.supplier_id,
    reference: props.product.reference,
    name: props.product.name,
    color: props.product.color,
    price: props.product.price,
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head title="Editar Produto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Produto
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Fornecedor -->
                        <div>
                            <InputLabel for="supplier_id" value="Fornecedor" />
                            <select
                                id="supplier_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                                v-model="form.supplier_id"
                                required
                            >
                                <option value="" disabled>Selecione um fornecedor</option>
                                <option
                                    v-for="supplier in suppliers"
                                    :key="supplier.id"
                                    :value="supplier.id"
                                >
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.supplier_id" />
                        </div>

                        <!-- Referência -->
                        <div>
                            <InputLabel for="reference" value="Referência" />
                            <TextInput
                                id="reference"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.reference"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.reference" />
                        </div>

                        <!-- Nome -->
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Cor -->
                        <div>
                            <InputLabel for="color" value="Cor" />
                            <TextInput
                                id="color"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.color"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.color" />
                        </div>

                        <!-- Preço -->
                        <div>
                            <InputLabel for="price" value="Preço" />
                            <TextInput
                                id="price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                v-model="form.price"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Atualizar Produto
                            </PrimaryButton>

                            <Link
                                :href="route('products.index')"
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

