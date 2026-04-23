export function checkHttpsOnly() {
    var el = document.getElementById('httpsOnly');
    if (!el) return;

    var isHttps = location.protocol === 'https:';
    var isSecure = window.isSecureContext;

    if (isHttps && isSecure) {
        el.innerText = 'Connessione HTTPS sicura';
    } else if (isHttps) {
        el.innerText = 'HTTPS attivo (contesto non sicuro — possibile contenuto misto)';
    } else {
        el.innerText = 'Non attivo — connessione non sicura (HTTP)';
    }
}
