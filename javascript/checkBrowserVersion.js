// Funzione per ottenere la versione del browser
export function checkBrowserVersion() {
    const browserVersion = detectBrowserVersion();

    const browserVersionElement = document.getElementById('browserVersion');

    if (browserVersionElement) {
        browserVersionElement.innerText = browserVersion;
    } else {
        console.error('Elemento con id "browserVersion" non trovato.');
    }
}

// Funzione per rilevare la versione del browser
export function detectBrowserVersion() {
    const userAgent = navigator.userAgent.toLowerCase();
    const match = userAgent.match(/(edg|chrome|safari|firefox|opr)[\s\/](\d+(\.\d+)?)/);

    return match ? match[2] : 'Sconosciuto';
}