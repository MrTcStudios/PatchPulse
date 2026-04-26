/**
 * Aggiorna le dimensioni dello schermo nel pannello UI.
 *
 * Modifiche:
 *  - Usa screen.width × screen.height (risoluzione fisica), non window.innerWidth
 *    (dimensione del viewport, che cambia a ogni resize della finestra).
 *  - Aggiunto devicePixelRatio: utile per capire se il display è HiDPI.
 *  - Debounce sul resize per evitare layout thrashing.
 */

let resizeRaf = 0;

export function updateDimensions() {
    const wEl = document.getElementById('width');
    const hEl = document.getElementById('height');
    const displayEl = document.getElementById('screenResolutionDisplay');

    const w = (typeof screen !== 'undefined' && screen.width)  || window.innerWidth  || 0;
    const h = (typeof screen !== 'undefined' && screen.height) || window.innerHeight || 0;
    const dpr = window.devicePixelRatio || 1;

    if (wEl) wEl.textContent = String(w);
    if (hEl) hEl.textContent = String(h);
    if (displayEl) {
        displayEl.textContent = dpr !== 1
            ? `${w} × ${h} (DPR ${dpr})`
            : `${w} × ${h}`;
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateDimensions, { once: true });
} else {
    updateDimensions();
}

window.addEventListener('resize', () => {
    if (resizeRaf) cancelAnimationFrame(resizeRaf);
    resizeRaf = requestAnimationFrame(updateDimensions);
});
