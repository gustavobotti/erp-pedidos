<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Array,
});

const form = useForm({
    supplier_id: '',
    file: null,
    csv_text: '',
});

const fileInput = ref(null);
const fileName = ref('');
const importMode = ref('file'); // 'file' or 'text'

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;
        fileName.value = file.name;
        form.csv_text = ''; // Clear text if file is selected
    }
};

const handleTextChange = () => {
    if (form.csv_text) {
        form.file = null;
        fileName.value = '';
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const canSubmit = computed(() => {
    return form.supplier_id && (form.file || form.csv_text.trim());
});

const submit = () => {
    form.post(route('products.import.process'), {
        forceFormData: true,
        onSuccess: () => {
            console.log('Form submitted successfully');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
        onFinish: () => {
            console.log('Form submission finished');
        }
    });
};
</script>

<template>
    <Head title="Importar Produtos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Importar Produtos
                </h2>
                <Link
                    :href="route('products.index')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Instructions Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">
                            📋 Instruções para Importação
                        </h3>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p>O arquivo CSV deve seguir o seguinte formato:</p>
                            <div class="bg-white rounded p-3 font-mono text-xs mt-2 border border-blue-200">
                                <div class="text-gray-600">referencia,nome,cor,preco</div>
                                <div class="text-gray-800">REF001,Produto A,Azul,99.90</div>
                                <div class="text-gray-800">REF002,Produto B,Vermelho,149.50</div>
                            </div>
                            <ul class="list-disc list-inside mt-3 space-y-1">
                                <li>A primeira linha pode conter o cabeçalho (opcional)</li>
                                <li>Use vírgula (,) como separador</li>
                                <li>O preço deve usar ponto (.) como separador decimal</li>
                                <li>Todos os campos são obrigatórios</li>
                                <li>Tamanho máximo do arquivo: 10MB</li>
                            </ul>
                            <p class="mt-3 font-semibold">
                                ✉️ Você receberá um email quando a importação for concluída.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Import Form Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
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

                            <!-- Import Mode Tabs -->
                            <div>
                                <InputLabel value="Método de Importação" />
                                <div class="mt-2 border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8">
                                        <button
                                            type="button"
                                            @click="importMode = 'file'"
                                            :class="[
                                                importMode === 'file'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                                            ]"
                                        >
                                            📁 Upload de Arquivo
                                        </button>
                                        <button
                                            type="button"
                                            @click="importMode = 'text'"
                                            :class="[
                                                importMode === 'text'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                                            ]"
                                        >
                                            📝 Colar Texto CSV
                                        </button>
                                    </nav>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div v-if="importMode === 'file'">
                                <InputLabel for="file" value="Arquivo CSV" />
                                <div class="mt-1">
                                    <input
                                        type="file"
                                        id="file"
                                        ref="fileInput"
                                        accept=".csv,.txt"
                                        @change="handleFileChange"
                                        class="hidden"
                                    />
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            @click="fileInput.click()"
                                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            Escolher Arquivo
                                        </button>
                                        <span v-if="fileName" class="text-sm text-gray-600">
                                            {{ fileName }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400">
                                            Nenhum arquivo selecionado
                                        </span>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.file" />
                            </div>

                            <!-- CSV Text Input -->
                            <div v-if="importMode === 'text'">
                                <InputLabel for="csv_text" value="Texto CSV" />
                                <textarea
                                    id="csv_text"
                                    v-model="form.csv_text"
                                    @input="handleTextChange"
                                    rows="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900 font-mono text-sm"
                                    placeholder="Cole aqui o conteúdo CSV:&#10;referencia,nome,cor,preco&#10;REF001,Produto A,Azul,99.90&#10;REF002,Produto B,Vermelho,149.50"
                                ></textarea>
                                <p class="mt-2 text-xs text-gray-500">
                                    Cole o conteúdo CSV diretamente aqui, incluindo o cabeçalho.
                                </p>
                                <InputError class="mt-2" :message="form.errors.csv_text" />
                            </div>

                            <!-- Botões -->
                            <div class="flex items-center gap-4 pt-4">
                                <PrimaryButton :disabled="form.processing || !canSubmit">
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Enviando...' : 'Importar Produtos' }}
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
        </div>
    </AuthenticatedLayout>
</template>

