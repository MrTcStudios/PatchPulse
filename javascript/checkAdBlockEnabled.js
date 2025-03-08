 // Funzione per verificare se è attivo un blocco degli annunci
 export function checkAdBlockEnabled() {
    const adBlockEnabled = window.chrome && window.chrome.webstore || navigator.userAgent.includes('Firefox') && !window.chrome;

    const adBlockEnabledElement = document.getElementById('adBlockEnabled');

    if (adBlockEnabledElement) {
        adBlockEnabledElement.innerText = (adBlockEnabled ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "adBlockEnabled" non trovato.');
    }
}