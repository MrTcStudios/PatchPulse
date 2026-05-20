/**
 * Stato della connessione HTTPS.
 *
 * Modifiche:
 *  - Si verifica anche `window.isSecureContext` per distinguere mixed content.
 *  - Si aggiunge il caso "localhost", che è considerato secure context anche
 *    via http (utile in dev senza far apparire warning fuorvianti).
 */
import { T } from '../lang/t.js';

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
        el.innerText = T('js.bs.https.active');
    } else if (isHttps && !isSecure) {
        el.innerText = T('js.bs.https.mixed');
    } else if (!isHttps && isLocal) {
        el.innerText = T('js.bs.https.http_local');
    } else {
        el.innerText = T('js.bs.https.http_unsafe');
    }
}
