 // Funzione per ottenere il tipo di browser
 export function checkBrowserType() {
    const browserType = detectBrowser();

    const browserTypeElement = document.getElementById('browserType');

    if (browserTypeElement) {
        browserTypeElement.innerText = browserType;
    } else {
        console.error('Elemento con id "browserType" non trovato.');
    }
}

// Funzione per rilevare il tipo di browser
export function detectBrowser() {
    const userAgent = navigator.userAgent.toLowerCase();

    if (userAgent.includes('edg')) {
        return 'Microsoft Edge';
    } else if (userAgent.includes('chrome')) {
        return 'Google Chrome';
    } else if (userAgent.includes('firefox')) {
        return 'Mozilla Firefox';
    } else if (userAgent.includes('safari')) {
        return 'Apple Safari';
    } else if (userAgent.includes('opera') || userAgent.includes('opr')) {
        return 'Opera';
    } else {
        return 'Sconosciuto';
    }
}