/**
 * Recupera la Referrer Policy attiva.
 *
 * Modifiche:
 *  - Usa `document.referrerPolicy` (più affidabile) come prima fonte.
 *    Riflette la policy effettiva, anche se impostata via header HTTP.
 *  - Fallback: meta tag <meta name="referrer">.
 *  - "no-referrer-when-downgrade" è il default browser → lo indichiamo.
 */
export function getReferrerPolicy() {
    const el = document.getElementById('referrerPolicy');
    if (!el) {
        console.warn('Elemento con id "referrerPolicy" non trovato.');
        return;
    }

    // 1) Prima fonte: la proprietà ufficiale del Document
    let policy = (typeof document !== 'undefined' && document.referrerPolicy) || '';

    // 2) Fallback: meta tag (anche se document.referrerPolicy è quasi sempre presente)
    if (!policy) {
        const meta = document.querySelector('meta[name="referrer" i]');
        if (meta) policy = meta.getAttribute('content') || '';
    }

    if (!policy) {
        // Default del browser quando non specificata
        el.innerText = 'strict-origin-when-cross-origin (default)';
        return;
    }

    el.innerText = policy;
}
