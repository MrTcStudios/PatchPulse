// Funzione per rilevare la modalità incognito
export async function detectIncognito() {
    return await new Promise(function (resolve, reject) {
        let browserName = 'Unknown';
        
        function __callback(isPrivate) {
            resolve({
                isPrivate: isPrivate,
                browserName: browserName
            });
        }

        function identifyChromium() {
            const ua = navigator.userAgent;
            if (ua.match(/Chrome/)) {
                if (navigator.brave !== undefined) return 'Brave';
                if (ua.match(/Edg/)) return 'Edge';
                if (ua.match(/OPR/)) return 'Opera';
                return 'Chrome';
            }
            return 'Chromium';
        }

        function isSafari() {
            try {
                (-1).toFixed(-1);
            } catch (e) {
                return e.message.length === 44;
            }
            return false;
        }

        function safariPrivateTest() {
            const tmp_name = String(Math.random());
            try {
                const db = window.indexedDB.open(tmp_name, 1);
                db.onupgradeneeded = function (i) {
                    try {
                        i.target.result.createObjectStore('test').put(new Blob());
                        __callback(false);
                    } catch (e) {
                        __callback(true);
                    }
                };
            } catch (e) {
                __callback(false);
            }
        }

        function storageQuotaChromePrivateTest() {
            navigator.webkitTemporaryStorage.queryUsageAndQuota((_, quota) => {
                const quotaInMib = Math.round(quota / (1024 * 1024));
                const quotaLimitInMib = Math.round(performance.memory.jsHeapSizeLimit / (1024 * 1024)) * 2;
                __callback(quotaInMib < quotaLimitInMib);
            });
        }

        function chromePrivateTest() {
            storageQuotaChromePrivateTest();
        }

        function firefoxPrivateTest() {
            __callback(navigator.serviceWorker === undefined);
        }

        function main() {
            if (isSafari()) {
                browserName = 'Safari';
                safariPrivateTest();
            } else if (navigator.userAgent.includes('Firefox')) {
                browserName = 'Firefox';
                firefoxPrivateTest();
            } else if (navigator.userAgent.includes('Chrome')) {
                browserName = identifyChromium();
                chromePrivateTest();
            } else {
                reject(new Error('Browser non identificato.'));
            }
        }

        main();
    });
}

export async function updateIncognitoStatus() {
    const statusElement = document.getElementById('incognitoMode');
    try {
        const result = await detectIncognito();
        if (result.isPrivate) {
            statusElement.textContent = `ATTIVA (${result.browserName}).`;
        } else {
            statusElement.textContent = `NON attiva (${result.browserName}).`;
        }
    } catch (error) {
        statusElement.textContent = "Errore durante il rilevamento.";
        console.error(error);
    }
}

