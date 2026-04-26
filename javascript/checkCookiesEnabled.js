/**
 * Verifica se i cookies sono effettivamente utilizzabili dal browser.
 *
 * `navigator.cookieEnabled` può essere unreliable (alcuni browser ritornano
 * sempre `true`). Eseguiamo anche un test concreto: scriviamo un cookie di
 * sessione con SameSite=Strict e verifichiamo che venga letto immediatamente.
 *
 * Nota privacy: il cookie viene scritto solo per il test e cancellato subito.
 * Non finisce né sul server né viene persistito.
 */
export function checkCookiesEnabled() {
    const el = document.getElementById('cookiesEnabled');
    if (!el) {
        console.warn('Elemento con id "cookiesEnabled" non trovato.');
        return;
    }

    const apiSays = navigator.cookieEnabled === true;

    // Test reale
    let realTest = false;
    try {
        const name = '__ppCookieTest';
        const value = String(Date.now());
        document.cookie = `${name}=${value}; path=/; SameSite=Strict`;
        const found = document.cookie.split('; ').some(c => c === `${name}=${value}`);
        // Cancella il cookie di test
        document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Strict`;
        realTest = found;
    } catch (_) {
        realTest = false;
    }

    if (apiSays && realTest) {
        el.innerText = 'Sì';
    } else if (!apiSays && !realTest) {
        el.innerText = 'No';
    } else {
        // Caso ambiguo (es. Brave che blocca i cookie persistenti ma riporta true)
        el.innerText = realTest ? 'Sì (parziale)' : 'No (parziale)';
    }
}
