/**
 * Heuristic detection dei DevTools / "Developer Mode".
 * Nota tecnica: window.chrome.devtools è disponibile SOLO dentro estensioni Chrome,
 * quindi il check originale ("window.chrome && window.chrome.devtools") era sempre false.
 *
 * Combina due tecniche:
 *  1) Differenza tra outerWidth/innerHeight e innerWidth/innerHeight (DevTools docked)
 *  2) Toggle stato: il rilevamento è una euristica, non garantisce 100%
 *
 * Per evitare falsi positivi (mobile, monitor con scaling, sidebar del browser),
 * usiamo soglie conservative.
 */
export function checkDeveloperMode() {
    const el = document.getElementById('developerMode');
    if (!el) return;

    try {
        const widthDiff  = Math.abs((window.outerWidth  || 0) - (window.innerWidth  || 0));
        const heightDiff = Math.abs((window.outerHeight || 0) - (window.innerHeight || 0));

        // Soglie: DevTools docked tipicamente ≥ 160px in larghezza o ≥ 160px in altezza.
        // Se il browser è in modalità mobile non è significativo.
        const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
        const threshold = 160;

        let detected = false;
        if (!isMobile) {
            // Considera "probabile DevTools aperto" se la differenza supera la soglia
            // su almeno un asse, escludendo i casi di window molto piccola.
            if (window.outerWidth > 400 && window.outerHeight > 400) {
                detected = (widthDiff > threshold) || (heightDiff > threshold);
            }
        }

        // Stato: usiamo un'etichetta esplicita perché 100% certi non si può essere
        if (detected) {
            el.innerText = 'Probabile (DevTools aperti)';
        } else {
            el.innerText = 'No';
        }
    } catch (err) {
        console.debug('checkDeveloperMode error:', err && err.message);
        el.innerText = 'Non rilevabile';
    }
}
