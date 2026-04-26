/**
 * Verifica il supporto ai Web Workers.
 *
 * Modifiche:
 *  - Oltre al check `typeof Worker`, indichiamo se sono disponibili anche
 *    SharedWorker e ServiceWorker (utile per debugging/consapevolezza).
 *  - Note: alcune Content-Security-Policy possono impedire la creazione
 *    di Worker da blob: → indichiamo solo "API disponibile", non "tutto funzionante".
 */
export function checkWebWorkersSupport() {
    const el = document.getElementById('webWorkersSupported');
    if (!el) {
        console.warn('Elemento con id "webWorkersSupported" non trovato.');
        return;
    }

    const w  = typeof Worker !== 'undefined';
    const sw = typeof SharedWorker !== 'undefined';
    const svw = typeof navigator !== 'undefined' && 'serviceWorker' in navigator;

    if (!w) {
        el.innerText = 'No';
        return;
    }

    const extras = [];
    if (sw)  extras.push('SharedWorker');
    if (svw) extras.push('ServiceWorker');

    el.innerText = extras.length
        ? `Sì (anche ${extras.join(' + ')})`
        : 'Sì';
}
