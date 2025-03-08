// Funzione per verificare se i cookies sono abilitati (inclusi i cookies di terze parti)
export function checkCookiesEnabled() {
    const cookiesEnabled = navigator.cookieEnabled;

    const cookiesEnabledElement = document.getElementById('cookiesEnabled');

    if (cookiesEnabledElement) {
        cookiesEnabledElement.innerText = (cookiesEnabled ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "cookiesEnabled" non trovato.');
    }
}