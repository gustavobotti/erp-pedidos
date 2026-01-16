<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();

const getAlertClass = (type) => {
    switch (type) {
        case 'success':
            return 'alert-success';
        case 'error':
            return 'alert-error';
        case 'warning':
            return 'alert-warning';
        case 'info':
        default:
            return 'alert-info';
    }
};
</script>

<template>
    <div class="toast toast-top toast-end z-50">
        <transition-group
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 translate-x-4"
            enter-to-class="transform opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform opacity-100"
            leave-to-class="transform opacity-0 translate-x-4"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="['alert shadow-lg max-w-md', getAlertClass(toast.type)]"
            >
                <div class="flex items-start w-full">
                    <div class="flex-1">
                        <div v-if="typeof toast.message === 'string'" class="whitespace-pre-wrap break-words">
                            {{ toast.message }}
                        </div>
                        <div v-else>
                            <div class="font-bold mb-2">Resposta da API:</div>
                                <pre class="text-xs overflow-x-auto overflow-y-auto max-h-96 max-w-full bg-base-200 p-2 rounded whitespace-pre-wrap break-words">{{ JSON.stringify(toast.message, null, 2) }}</pre>
                        </div>
                    </div>
                    <button @click="removeToast(toast.id)" class="btn btn-sm btn-ghost ml-2 flex-shrink-0">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
}
</style>

