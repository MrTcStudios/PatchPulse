// Funzione per verificare se i pop-up sono abilitati
export function checkPopupsEnabled() {
    // Modificata la verifica dei pop-up
    const popupsEnabled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone || document.hasFocus() || window.innerWidth > 0 || window.innerHeight > 0;

    // Aggiorna la visualizzazione dello stato dei pop-up
    const popupsEnabledElement = document.getElementById('popupsEnabled');

    if (popupsEnabledElement) {
        popupsEnabledElement.innerText = (popupsEnabled ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "popupsEnabled" non trovato.');
    }
}