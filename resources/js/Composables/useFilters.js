import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useFilters(routeName, initialFilters = {}) {
    const filters = ref({ ...initialFilters });

    const applyFilters = (additionalParams = {}) => {
        const params = { ...filters.value, ...additionalParams };

        // Remove empty values
        Object.keys(params).forEach(key => {
            if (params[key] === '' || params[key] === null || params[key] === undefined) {
                delete params[key];
            }
        });

        router.get(route(routeName), params, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        Object.keys(filters.value).forEach(key => {
            filters.value[key] = '';
        });
        applyFilters();
    };

    const setFilter = (key, value) => {
        filters.value[key] = value;
    };

    return {
        filters,
        applyFilters,
        clearFilters,
        setFilter,
    };
}

