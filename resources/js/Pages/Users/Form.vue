<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import { Link } from '@inertiajs/vue3';
import { formatCNPJ } from '@/Composables/useMasks';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        default: () => [],
    },
    submitLabel: {
        type: String,
        default: 'Salvar',
    },
    cancelRoute: {
        type: String,
        default: 'users.index',
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit']);

const customSupplierLabel = (supplier) => {
    return `${supplier.name} - ${formatCNPJ(supplier.cnpj)}`;
};

const handleSubmit = () => {
    emit('submit');
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
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
            <InputLabel
                for="password"
                :value="isEdit ? 'Nova Senha (deixe em branco para não alterar)' : 'Senha'"
            />
            <TextInput
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                :required="!isEdit"
            />
            <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <!-- Confirmar Senha -->
        <div>
            <InputLabel
                for="password_confirmation"
                :value="isEdit ? 'Confirmar Nova Senha' : 'Confirmar Senha'"
            />
            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                :required="!isEdit && form.password"
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
            <MultiSelect
                v-model="form.supplier_ids"
                :options="suppliers"
                track-by="id"
                label="name"
                :custom-label="customSupplierLabel"
                placeholder="Selecione os fornecedores..."
            />
            <InputError :message="form.errors.supplier_ids" class="mt-2" />
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">
                {{ submitLabel }}
            </PrimaryButton>

            <Link
                :href="route(cancelRoute)"
                class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
            >
                Cancelar
            </Link>
        </div>
    </form>
</template>

