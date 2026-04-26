/**
 * Recupera i MIME type "plug-in" supportati.
 *
 * Note:
 *  - I browser moderni hanno deprecato `navigator.mimeTypes` e ne ritornano
 *    una versione molto ridotta (Chromium hard-coda quattro PDF mime).
 *    Questo è atteso, NON è un bug.
 *  - In Safari/iOS e in Firefox la lista è quasi sempre vuota.
 */
export function getMimeTypes() {
    const el = document.getElementById('mimeTypes');
    if (!el) {
        console.warn('Elemento con id "mimeTypes" non trovato.');
        return [];
    }

    let unique = [];
    try {
        const raw = (typeof navigator !== 'undefined' && navigator.mimeTypes)
            ? Array.from(navigator.mimeTypes)
            : [];
        const types = raw
            .map(mt => (mt && typeof mt.type === 'string') ? mt.type.trim() : '')
            .filter(Boolean);
        unique = Array.from(new Set(types)).sort((a, b) => a.localeCompare(b));
    } catch (err) {
        console.debug('getMimeTypes error:', err && err.message);
    }

    if (unique.length === 0) {
        el.innerText = 'Nessuno (i browser moderni espongono pochissimi MIME type)';
        el.removeAttribute('title');
        return [];
    }

    const text = unique.join(', ');
    el.innerText = text;
    el.setAttribute('title', text);
    el.setAttribute('aria-label', `Tipi MIME supportati: ${text}`);
    return unique;
}
