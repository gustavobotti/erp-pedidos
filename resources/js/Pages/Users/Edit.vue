<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Form from './Form.vue';
import { Head, useForm } from '@inertiajs/vue3';

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
                    <Form
                        :form="form"
                        :suppliers="suppliers"
                        submit-label="Atualizar Usuário"
                        is-edit
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

