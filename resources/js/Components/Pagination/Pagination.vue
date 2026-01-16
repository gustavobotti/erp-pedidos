<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    resourceName: {
        type: String,
        default: 'registros',
    },
});

const showPagination = computed(() => {
    return props.data.data?.length > 0 && props.data.links?.length > 3;
});
</script>

<template>
    <div v-if="data.data?.length > 0" class="mt-6">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700 text-center mb-4">
            Mostrando
            <span class="font-medium">{{ data.from }}</span> a
            <span class="font-medium">{{ data.to }}</span> de
            <span class="font-medium">{{ data.total }}</span>
            {{ resourceName }}
        </div>

        <!-- Pagination Links -->
        <div v-if="showPagination" class="flex justify-center gap-2 flex-wrap">
            <template v-for="(link, index) in data.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-semibold transition duration-150 ease-in-out"
                    :class="link.active
                        ? 'border-transparent bg-gray-800 text-white hover:bg-gray-700'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-400 cursor-not-allowed"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>

