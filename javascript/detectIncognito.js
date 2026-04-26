/**
 * Rileva la modalità "navigazione privata / incognito" del browser.
 *
 * Stato dell'arte (2025):
 *  - Chromium (Chrome/Edge/Opera/Brave): rilevabile via `storage.estimate().quota`,
 *    in incognito è cappata a ~1 GiB indipendentemente dal disco.
 *  - Firefox ≥ 138: Mozilla ha INTENZIONALMENTE rimosso le differenze fra
 *    normal e Private Browsing (storage.estimate, serviceWorker, cache, ecc.)
 *    proprio per impedire la detection. Per FF moderni la modalità privata
 *    è progettualmente non rilevabile dai siti.
 *  - Safari ≥ 17: stesso discorso. Apple ha rimosso "BlobURLs not supported"
 *    e altri segnali storici per ridurre il fingerprinting.
 *  - Browser più vecchi: i check legacy (serviceWorker undefined, openDatabase,
 *    localStorage QuotaExceeded) restano validi.
 *
 * Restituisce: Promise<{
 *   status: 'private' | 'normal' | 'unknown',
 *   browserName: string,
 *   reason?: string
 * }>
 */
export async function detectIncognito() {
    const browserName = identifyBrowser();

    if (isChromium(browserName)) {
        return await detectChromium(browserName);
    }
    if (browserName === 'Firefox') {
        return await detectFirefox(browserName);
    }
    if (browserName === 'Safari') {
        return await detectSafari(browserName);
    }
    return { status: 'unknown', browserName, reason: 'browser non identificato' };
}

// ----------------------------------------------------------------------------
// Identificazione browser
// ----------------------------------------------------------------------------

function identifyBrowser() {
    const ua = navigator.userAgent || '';
    if (navigator.brave && typeof navigator.brave.isBrave === 'function') return 'Brave';
    if (/Edg\//i.test(ua))            return 'Edge';
    if (/OPR\//i.test(ua))            return 'Opera';
    if (/Vivaldi\//i.test(ua))        return 'Vivaldi';
    if (/SamsungBrowser\//i.test(ua)) return 'Samsung Internet';
    if (/Firefox\//i.test(ua))        return 'Firefox';
    if (/Chrome\//i.test(ua))         return 'Chrome';
    if (/Safari\//i.test(ua) && /Apple/i.test(navigator.vendor || '')) return 'Safari';
    return 'Unknown';
}

function isChromium(name) {
    return ['Chrome', 'Edge', 'Opera', 'Vivaldi', 'Brave', 'Samsung Internet'].includes(name);
}

// ----------------------------------------------------------------------------
// Chromium: storage quota check è ancora affidabile
// ----------------------------------------------------------------------------

async function detectChromium(browserName) {
    // Legacy fallback (Chrome < 76)
    if (!navigator.storage || typeof navigator.storage.estimate !== 'function') {
        return new Promise((resolve) => {
            try {
                const fs = window.webkitRequestFileSystem;
                if (!fs) return resolve({ status: 'unknown', browserName });
                fs(0, 1,
                    () => resolve({ status: 'normal',  browserName }),
                    () => resolve({ status: 'private', browserName })
                );
            } catch (_) {
                resolve({ status: 'unknown', browserName });
            }
        });
    }

    try {
        const { quota } = await navigator.storage.estimate();
        if (typeof quota !== 'number' || !isFinite(quota) || quota <= 0) {
            return { status: 'unknown', browserName };
        }
        const heapLimit = (window.performance && performance.memory && performance.memory.jsHeapSizeLimit)
            ? performance.memory.jsHeapSizeLimit
            : 1073741824;
        const isPrivate = quota < heapLimit * 2;
        return { status: isPrivate ? 'private' : 'normal', browserName };
    } catch (_) {
        return { status: 'unknown', browserName };
    }
}

// ----------------------------------------------------------------------------
// Firefox: < 138 rilevabile, ≥ 138 progettualmente non rilevabile
// ----------------------------------------------------------------------------

async function detectFirefox(browserName) {
    // 1) Firefox < 138: in PB `serviceWorker` non era esposto.
    if (navigator.serviceWorker === undefined) {
        return { status: 'private', browserName };
    }

    // 2) Firefox 138+: l'API Origin Private File System (OPFS) rifiuta in PB
    //    con un "Security error", mentre in normal mode la chiamata risolve.
    //    Tecnica usata anche dalla libreria detectIncognito di Joe12387.
    if (navigator.storage && typeof navigator.storage.getDirectory === 'function') {
        try {
            await navigator.storage.getDirectory();
            return { status: 'normal', browserName };
        } catch (e) {
            const msg = (e && e.message) ? String(e.message).toLowerCase() : '';
            // Firefox PB: "The operation is insecure" / "Security error".
            // Altri errori non sono indicativi → fallthrough.
            if (msg.includes('security') || msg.includes('insecure')) {
                return { status: 'private', browserName };
            }
        }
    }

    // 3) Firefox < ~115: in PB la quota era cappata a pochi MiB.
    if (navigator.storage && typeof navigator.storage.estimate === 'function') {
        try {
            const { quota } = await navigator.storage.estimate();
            if (typeof quota === 'number' && quota > 0 && quota < 100 * 1024 * 1024) {
                return { status: 'private', browserName };
            }
        } catch (_) {}
    }

    return {
        status: 'unknown',
        browserName,
        reason: 'Firefox limita i segnali di detection'
    };
}

// ----------------------------------------------------------------------------
// Safari: < 17 rilevabile via storage / openDatabase, ≥ 17 difficile
// ----------------------------------------------------------------------------

async function detectSafari(browserName) {
    // Safari < 11: openDatabase lancia in PB
    try {
        if (typeof window.openDatabase === 'function') {
            try { window.openDatabase(null, null, null, null); }
            catch (_) { return { status: 'private', browserName }; }
        }
    } catch (_) {}

    // Safari < 11: localStorage.setItem lancia QuotaExceededError in PB
    try {
        window.localStorage.setItem('__pp_inc_test', '1');
        window.localStorage.removeItem('__pp_inc_test');
    } catch (_) {
        return { status: 'private', browserName };
    }

    // Safari moderno: prova storage.estimate (cap a ~1 GiB in PB)
    if (navigator.storage && typeof navigator.storage.estimate === 'function') {
        try {
            const { quota } = await navigator.storage.estimate();
            if (typeof quota === 'number' && quota > 0) {
                if (quota < 1.2 * 1024 * 1024 * 1024) {
                    return { status: 'private', browserName };
                }
                return { status: 'normal', browserName };
            }
        } catch (_) {}
    }

    return {
        status: 'unknown',
        browserName,
        reason: 'Safari moderno limita i segnali di detection'
    };
}

// ----------------------------------------------------------------------------
// UI updater
// ----------------------------------------------------------------------------

export async function updateIncognitoStatus() {
    const el = document.getElementById('incognitoMode');
    if (!el) return;
    try {
        const { status, browserName, reason } = await detectIncognito();
        if (status === 'private') {
            el.textContent = `ATTIVA (${browserName})`;
        } else if (status === 'normal') {
            el.textContent = `NON attiva (${browserName})`;
        } else {
            // Quando il browser ha disabilitato i segnali di detection
            // diciamo onestamente all'utente che non possiamo saperlo.
            el.textContent = `Non rilevabile (${browserName})`;
            if (reason) el.title = reason;
        }
    } catch (err) {
        console.debug('detectIncognito error:', err && err.message);
        el.textContent = 'Non rilevabile';
    }
}
