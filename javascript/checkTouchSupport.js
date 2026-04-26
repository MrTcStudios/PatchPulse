/**
 * Verifica il supporto al touch.
 *
 * Modifiche:
 *  - Rimosso il check su DocumentTouch (rimosso da tutti i browser moderni).
 *  - Diamo priorità a `pointer: coarse` + `maxTouchPoints` (più affidabili
 *    di `ontouchstart`, che alcuni browser desktop dichiarano comunque).
 *  - Niente più "any" booleano: indichiamo anche se è solo "coarse pointer"
 *    (mouse/penna su PC convertibile) per ridurre falsi positivi.
 */
export function checkTouchSupport() {
    const el = document.getElementById('touchSupport');
    try {
        const maxTouch = (navigator.maxTouchPoints ?? 0) || (navigator.msMaxTouchPoints ?? 0);
        const hasOnTouch = typeof window !== 'undefined' && 'ontouchstart' in window;
        const coarse = (typeof window !== 'undefined' && window.matchMedia)
            ? window.matchMedia('(pointer: coarse)').matches
            : false;
        const noPointerHover = (typeof window !== 'undefined' && window.matchMedia)
            ? window.matchMedia('(any-hover: none)').matches
            : false;

        // Touch "vero": maxTouch > 0 oppure puntatore coarse senza hover
        const isRealTouch = maxTouch > 0 || (coarse && noPointerHover);

        let text;
        if (isRealTouch) {
            text = `Sì (max ${maxTouch || 1} punti)`;
        } else if (hasOnTouch || coarse) {
            // Solo segnali deboli — il dispositivo "potrebbe" supportare touch
            text = 'Possibile (segnali deboli)';
        } else {
            text = 'No';
        }

        if (el) el.innerText = text;
        return text;
    } catch (err) {
        console.debug('checkTouchSupport error:', err && err.message);
        if (el) el.innerText = 'No';
        return 'No';
    }
}
