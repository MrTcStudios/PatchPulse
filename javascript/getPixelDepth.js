/**
 * Pixel Depth dello schermo (di norma uguale a colorDepth, salvo display obsoleti).
 */
import { T } from '../lang/t.js';

export function getPixelDepth() {
    const el = document.getElementById('pixelDepth');
    if (!el) {
        console.warn('Elemento con id "pixelDepth" non trovato.');
        return;
    }
    const depth = (typeof screen !== 'undefined') && screen.pixelDepth;
    el.innerText = depth ? `${depth} bit` : T('js.bs.unavailable');
}
