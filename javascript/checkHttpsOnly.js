/**
 * Stato della connessione HTTPS.
 *
 * Modifiche:
 *  - Si verifica anche `window.isSecureContext` per distinguere mixed content.
 *  - Si aggiunge il caso "localhost", che è considerato secure context anche
 *    via http (utile in dev senza far apparire warning fuorvianti).
 */
export function checkHttpsOnly() {
    const el = document.getElementById('httpsOnly');
    if (!el) {
        console.warn('Elemento con id "httpsOnly" non trovato.');
        return;
    }

    const isHttps  = location.protocol === 'https:';
    const isSecure = !!window.isSecureContext;
    const isLocal  = ['localhost', '127.0.0.1', '::1'].includes(location.hostname);

    if (isHttps && isSecure) {
        el.innerText = 'HTTPS attivo (connessione sicura)';
    } else if (isHttps && !isSecure) {
        el.innerText = 'HTTPS attivo, ma contesto non sicuro (probabile mixed content)';
    } else if (!isHttps && isLocal) {
        el.innerText = 'HTTP locale (sviluppo)';
    } else {
        el.innerText = 'HTTPS non attivo (connessione non sicura)';
    }
}
