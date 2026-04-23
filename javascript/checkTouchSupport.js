export function checkTouchSupport() {
    try {
        const hasOntouch = typeof window !== 'undefined' && 'ontouchstart' in window;
        const maxTouchPoints = navigator.maxTouchPoints ?? navigator.msMaxTouchPoints ?? 0;
        const documentTouch = (typeof DocumentTouch !== 'undefined' && document instanceof DocumentTouch) || false;
        const coarsePointer = typeof window !== 'undefined' && window.matchMedia
            ? window.matchMedia('(pointer: coarse)').matches
            : false;

        const isTouch = !!(hasOntouch || maxTouchPoints > 0 || documentTouch || coarsePointer);
        const touchSupport = isTouch ? 'Sì' : 'No';

        const touchSupportElement = document.getElementById('touchSupport');
        if (touchSupportElement) {
            touchSupportElement.innerText = touchSupport;
        } else {
            console.error('Elemento con id "touchSupport" non trovato.');
        }

        return touchSupport;
    } catch (err) {
        console.error('Errore durante il rilevamento del touch support:', err);
        return 'No';
    }
}
