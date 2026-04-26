/**
 * Rileva il tipo di browser con buona precisione.
 * Ordine di check importante: i browser "derivati" devono essere controllati PRIMA di Chrome,
 * altrimenti Edge/Opera/Brave/Vivaldi vengono erroneamente identificati come Chrome.
 */
export function checkBrowserType() {
    const browserType = detectBrowser();
    const el = document.getElementById('browserType');
    if (el) {
        el.innerText = browserType;
    } else {
        console.warn('Elemento con id "browserType" non trovato.');
    }
}

/**
 * Detection robusta: usa userAgentData (Client Hints) quando disponibile,
 * con fallback su userAgent.
 *
 * @returns {string} Nome del browser
 */
export function detectBrowser() {
    const ua = (navigator.userAgent || '').toLowerCase();
    const vendor = (navigator.vendor || '').toLowerCase();

    // 1) Client Hints API: più affidabile dell'userAgent
    if (navigator.userAgentData && Array.isArray(navigator.userAgentData.brands)) {
        const brands = navigator.userAgentData.brands.map(b => (b.brand || '').toLowerCase());
        // Cerca prima i brand più specifici
        if (brands.some(b => b.includes('opera'))) return 'Opera';
        if (brands.some(b => b.includes('edge'))) return 'Microsoft Edge';
        if (brands.some(b => b.includes('brave'))) return 'Brave';
        if (brands.some(b => b.includes('vivaldi'))) return 'Vivaldi';
        if (brands.some(b => b.includes('samsung'))) return 'Samsung Internet';
        if (brands.some(b => b.includes('chromium') || b.includes('google chrome'))) return 'Google Chrome';
    }

    // 2) Brave si rileva tramite navigator.brave (l'UA è identico a Chrome)
    if (navigator.brave && typeof navigator.brave.isBrave === 'function') {
        return 'Brave';
    }

    // 3) Fallback userAgent: ordine specifico → generico
    if (ua.includes('edg/') || ua.includes('edge/') || ua.includes('edga/') || ua.includes('edgios/')) {
        return 'Microsoft Edge';
    }
    if (ua.includes('opr/') || ua.includes('opera/') || ua.includes('opios/')) {
        return 'Opera';
    }
    if (ua.includes('vivaldi/')) {
        return 'Vivaldi';
    }
    if (ua.includes('samsungbrowser/')) {
        return 'Samsung Internet';
    }
    if (ua.includes('ucbrowser/')) {
        return 'UC Browser';
    }
    if (ua.includes('firefox/') || ua.includes('fxios/')) {
        return 'Mozilla Firefox';
    }
    if (ua.includes('crios/')) {
        // Chrome su iOS
        return 'Google Chrome';
    }
    if (ua.includes('chrome/') && !ua.includes('chromium')) {
        return 'Google Chrome';
    }
    if (ua.includes('chromium/')) {
        return 'Chromium';
    }
    // Safari controllato per ULTIMO: l'UA di Chrome iOS contiene "Safari"
    if ((ua.includes('safari/') || vendor.includes('apple')) && !ua.includes('chrome')) {
        return 'Apple Safari';
    }

    return 'Sconosciuto';
}
