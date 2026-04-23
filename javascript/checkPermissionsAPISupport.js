export function checkPermissionsAPISupport() {
    const permissionsAPISupported = 'permissions' in navigator && 'query' in navigator.permissions;
    const permissionsAPISupportedElement = document.getElementById('permissionsAPISupported');

    if (permissionsAPISupportedElement) {
        permissionsAPISupportedElement.innerText = (permissionsAPISupported ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "permissionsAPISupported" non trovato.');
    }
}
