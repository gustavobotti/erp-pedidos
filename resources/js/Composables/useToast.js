import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

export function useToast() {
    const addToast = (message, type = 'info', duration = 5000) => {
        const id = nextId++;
        const toast = {
            id,
            message,
            type, // 'success', 'error', 'info', 'warning'
            show: true
        };

        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (message, duration = 5000) => {
        return addToast(message, 'success', duration);
    };

    const error = (message, duration = 8000) => {
        return addToast(message, 'error', duration);
    };

    const info = (message, duration = 5000) => {
        return addToast(message, 'info', duration);
    };

    const warning = (message, duration = 5000) => {
        return addToast(message, 'warning', duration);
    };

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        info,
        warning
    };
}

