import { router } from '@inertiajs/vue3';

export function useConfirmDelete() {
    const confirmDelete = (routeName, id, message = 'Tem certeza que deseja excluir este registro?') => {
        if (confirm(message)) {
            router.delete(route(routeName, id), {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Registro excluído com sucesso!');
                },
                onError: (errors) => {
                    console.error('Erro ao excluir registro:', errors);
                }
            });
        }
    };

    const deleteResource = confirmDelete;

    return {
        confirmDelete,
        deleteResource,
    };
}

