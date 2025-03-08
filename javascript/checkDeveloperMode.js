// Funzione per verificare se il browser è in modalità sviluppatore
export function checkDeveloperMode() {
    const isDeveloperMode = window && window.chrome && window.chrome.devtools;

    const developerModeElement = document.getElementById('developerMode');

    if (developerModeElement) {
        developerModeElement.innerText = (isDeveloperMode ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "developerMode" non trovato.');
    }
}