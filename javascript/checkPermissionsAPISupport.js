/**
 * Verifica il supporto alla Permissions API.
 * Null check sull'elemento e check più stretto su `query`.
 */
export function checkPermissionsAPISupport() {
    const el = document.getElementById('permissionsAPISupported');
    if (!el) {
        console.warn('Elemento con id "permissionsAPISupported" non trovato.');
        return;
    }

    const supported =
        typeof navigator !== 'undefined'
        && navigator.permissions
        && typeof navigator.permissions.query === 'function';

    el.innerText = supported ? 'Sì' : 'No';
}
