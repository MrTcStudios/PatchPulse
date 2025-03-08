// Funzione per verificare se JavaScript è attivo
export function checkJavaScriptStatus() {
    const isJavaScriptActive = true;
    const javascriptStatusElement = document.getElementById('javascriptStatus');

    if (javascriptStatusElement) {
        javascriptStatusElement.innerText = (isJavaScriptActive ? 'Attivo' : 'Non Attivo');
    } else {
        console.error('Elemento con id "javascriptStatus" non trovato.');
    }
}