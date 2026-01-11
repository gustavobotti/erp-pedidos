/**
 * Format CNPJ with mask: 00.000.000/0000-00
 */
export function formatCNPJ(value) {
    if (!value) return '';
    // Convert to string in case it's a number
    const cleaned = String(value).replace(/\D/g, '');
    if (cleaned.length === 0) return '';
    if (cleaned.length <= 14) {
        return cleaned
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }
    return cleaned.substring(0, 14)
        .replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
}

/**
 * Format CEP with mask: 00000-000
 */
export function formatCEP(value) {
    if (!value) return '';
    // Convert to string in case it's a number
    const cleaned = String(value).replace(/\D/g, '');
    if (cleaned.length === 0) return '';
    if (cleaned.length <= 8) {
        return cleaned.replace(/^(\d{5})(\d)/, '$1-$2');
    }
    return cleaned.substring(0, 8).replace(/^(\d{5})(\d{3})/, '$1-$2');
}

/**
 * Remove all non-numeric characters
 */
export function unmask(value) {
    if (!value) return '';
    return value.replace(/\D/g, '');
}

/**
 * Masks for vue-the-mask
 */
export const masks = {
    cnpj: '##.###.###/####-##',
    cep: '#####-###',
};

