        // Funzione per esaminare il Browser Fingerprinting
        export function checkBrowserFingerprinting() {
            const fingerprint = fingerprintUser();

            const browserFingerprintingElement = document.getElementById('browserFingerprinting');

            if (browserFingerprintingElement) {
                browserFingerprintingElement.innerText = fingerprint;
            } else {
                console.error('Elemento con id "browserFingerprinting" non trovato.');
            }
        }

        // Funzione per generare un fingerprint univoco del browser
        export function fingerprintUser() {
            return navigator.userAgent + navigator.platform;
        }