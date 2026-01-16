/**
 * Format date to Brazilian format (dd/mm/yyyy)
 */
export function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

/**
 * Format currency to Brazilian Real
 */
export function formatCurrency(value) {
    if (value === null || value === undefined) return 'R$ 0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

/**
 * Format number with decimal places
 */
export function formatNumber(value, decimals = 2) {
    if (value === null || value === undefined) return '0';
    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
}

/**
 * Format phone number
 */
export function formatPhone(value) {
    if (!value) return '';
    const cleaned = String(value).replace(/\D/g, '');

    if (cleaned.length === 11) {
        return cleaned.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }

    if (cleaned.length === 10) {
        return cleaned.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    return value;
}

/**
 * Composable that exports all formatters
 */
export function useFormatters() {
    return {
        formatDate,
        formatCurrency,
        formatNumber,
        formatPhone,
    };
}

