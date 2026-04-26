/**
 * Verifica il supporto alle Media Queries.
 *
 * BUG corretto: il check precedente usava `(pointer: fine)`, che è VERO solo con un
 * mouse: dava falso negativo su tutti i dispositivi touch. Le Media Queries sono
 * supportate da chiunque abbia `window.matchMedia`, indipendentemente dal puntatore.
 */
export function checkMediaQueriesSupport() {
    const el = document.getElementById('mediaQueriesSupported');
    if (!el) {
        console.warn('Elemento con id "mediaQueriesSupported" non trovato.');
        return;
    }

    try {
        const supported =
            typeof window !== 'undefined' &&
            typeof window.matchMedia === 'function' &&
            // Media query banale che dovrebbe sempre matchare (verifica concreta del parser)
            window.matchMedia('all').matches === true;

        el.innerText = supported ? 'Sì' : 'No';
    } catch (err) {
        console.debug('checkMediaQueriesSupport error:', err && err.message);
        el.innerText = 'No';
    }
}
