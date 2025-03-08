// Funzione per verificare l'utilizzo sempre di connessioni sicure (HTTPS) (DA RIVEDERE)
export function checkHttpsOnly() {
    const httpsOnlyElement = document.getElementById('httpsOnly');
    const isHttpsOnlyEnabled = window.isSecureContext;

    if (httpsOnlyElement) {
        httpsOnlyElement.innerText = (isHttpsOnlyEnabled ? 'Attivo' : 'Non Attivo');
    } else {
        console.error('Elemento con id "httpsOnly" non trovato.');
    }
}