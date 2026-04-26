/**
 * Verifica il supporto a WebAssembly senza compilare moduli.
 *
 * Compilare `new WebAssembly.Module(bytes)` richiede `'wasm-unsafe-eval'`
 * (o `'unsafe-eval'`) nella CSP — la CSP del sito non li abilita, ed è giusto così.
 * Quindi ci limitiamo a verificare la presenza dell'oggetto e dei suoi metodi.
 */
export function checkWebAssemblySupport() {
    const el = document.getElementById('webAssemblySupport');
    if (!el) {
        console.warn('Elemento con id "webAssemblySupport" non trovato.');
        return;
    }

    const supported =
        typeof WebAssembly === 'object'
        && typeof WebAssembly.compile === 'function'
        && typeof WebAssembly.instantiate === 'function';

    el.innerText = supported ? 'Sì' : 'No';
}
