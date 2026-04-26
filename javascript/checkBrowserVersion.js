import { detectBrowser } from './checkBrowserType.js';

/**
 * Rileva la versione del browser.
 * Mantiene allineamento con detectBrowser(): se si è dichiarato "Edge", si estrae la versione di Edge,
 * non quella di Chrome (che pure è presente nell'UA).
 */
export function checkBrowserVersion() {
    const v = detectBrowserVersion();
    const el = document.getElementById('browserVersion');
    if (el) {
        el.innerText = v;
    } else {
        console.warn('Elemento con id "browserVersion" non trovato.');
    }
}

/**
 * @returns {string} Versione (es. "120.0") oppure "Sconosciuta"
 */
export function detectBrowserVersion() {
    const ua = navigator.userAgent || '';
    const browser = detectBrowser();

    // Mappa nome browser → token UA
    const tokenMap = {
        'Microsoft Edge':   ['Edg', 'Edge', 'EdgA', 'EdgiOS'],
        'Opera':            ['OPR', 'Opera', 'OPiOS'],
        'Vivaldi':          ['Vivaldi'],
        'Samsung Internet': ['SamsungBrowser'],
        'UC Browser':       ['UCBrowser'],
        'Mozilla Firefox':  ['Firefox', 'FxiOS'],
        'Brave':            ['Chrome'], // Brave non mette se stesso nell'UA: usa Chrome
        'Google Chrome':    ['CriOS', 'Chrome'],
        'Chromium':         ['Chromium', 'Chrome'],
        'Apple Safari':     ['Version'], // Safari mette "Version/X.Y Safari/Z"
    };

    const tokens = tokenMap[browser] || ['Chrome', 'Firefox', 'Safari', 'Edg', 'OPR'];

    for (const token of tokens) {
        // Cattura "<token>/<versione>" o "<token> <versione>"
        const re = new RegExp(token + '[\\s\\/](\\d+(?:\\.\\d+)*)', 'i');
        const m = ua.match(re);
        if (m && m[1]) return m[1];
    }

    // Fallback Client Hints (versione "ridotta" che il browser sceglie di esporre)
    if (navigator.userAgentData && Array.isArray(navigator.userAgentData.brands)) {
        const brand = navigator.userAgentData.brands.find(b =>
            !/not.?a.?brand/i.test(b.brand || '')
        );
        if (brand && brand.version) return brand.version;
    }

    return 'Sconosciuta';
}
