<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    editRoute: {
        type: String,
        default: null,
    },
    showRoute: {
        type: String,
        default: null,
    },
    onDelete: {
        type: Function,
        default: null,
    },
    deleteText: {
        type: String,
        default: 'Deletar',
    },
    editText: {
        type: String,
        default: 'Editar',
    },
    showText: {
        type: String,
        default: 'Ver',
    },
});
</script>

<template>
    <div class="flex gap-2">
        <Link
            v-if="showRoute"
            :href="showRoute"
            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            {{ showText }}
        </Link>
        <Link
            v-if="editRoute"
            :href="editRoute"
            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
        >
            {{ editText }}
        </Link>
        <button
            v-if="onDelete"
            @click="onDelete"
            class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700"
        >
            {{ deleteText }}
        </button>
    </div>
</template>

