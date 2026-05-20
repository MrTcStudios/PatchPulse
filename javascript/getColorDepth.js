/**
 * Profondità di colore dello schermo (bit per pixel).
 * Aggiunto suffisso "bit" per chiarezza UX.
 */
import { T } from '../lang/t.js';

export function getColorDepth() {
    const el = document.getElementById('colorDepth');
    if (!el) {
        console.warn('Elemento con id "colorDepth" non trovato.');
        return;
    }
    const depth = (typeof screen !== 'undefined') && (screen.colorDepth || screen.pixelDepth);
    el.innerText = depth ? `${depth} bit` : T('js.bs.unavailable');
}
