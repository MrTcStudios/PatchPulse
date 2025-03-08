// Funzione per verificare il supporto al Touch
export function checkTouchSupport() {
    const touchSupport = ('ontouchstart' in window || navigator.maxTouchPoints > 0 || window.DocumentTouch && document instanceof DocumentTouch) ? 'Sì' : 'No';
    const touchSupportElement = document.getElementById('touchSupport');

    if (touchSupportElement) {
        touchSupportElement.innerText = touchSupport;
    } else {
        console.error('Elemento con id "touchSupport" non trovato.');
    }
}