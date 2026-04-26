/**
 * Stato del motore JavaScript del browser.
 *
 * Se questo codice gira, JavaScript è ovviamente attivo. Aggiungiamo solo
 * un'indicazione delle feature ES moderne disponibili — usando soltanto
 * `typeof` / `in`, senza `new Function()` che richiederebbe `unsafe-eval` nella CSP.
 */
export function checkJavaScriptStatus() {
    const el = document.getElementById('javascriptStatus');
    if (!el) {
        console.warn('Elemento con id "javascriptStatus" non trovato.');
        return;
    }

    const features = [];
    if ('noModule' in HTMLScriptElement.prototype) features.push('ES Modules');
    // Detect async/await: la function async è una funzione "AsyncFunction"
    if (typeof (async () => {}).constructor === 'function'
        && (async () => {}).constructor.name === 'AsyncFunction') {
        features.push('async/await');
    }
    if (typeof BigInt === 'function') features.push('BigInt');
    if (typeof Promise === 'function' && typeof Promise.allSettled === 'function') {
        features.push('Promise.allSettled');
    }

    el.innerText = features.length
        ? `Attivo (${features.join(', ')})`
        : 'Attivo';
}
