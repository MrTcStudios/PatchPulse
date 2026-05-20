/**
 * Mostra la lingua principale + le lingue accettate.
 * Usa anche navigator.languages (più ricco di navigator.language singolo).
 */
import { T } from '../lang/t.js';

export function checkBrowserLanguage() {
    const el = document.getElementById('browserLanguage');
    if (!el) {
        console.warn('Elemento con id "browserLanguage" non trovato.');
        return;
    }

    const primary = navigator.language || navigator.userLanguage || T('js.bs.nd');
    const list = Array.isArray(navigator.languages) ? navigator.languages : [];

    if (list.length > 1) {
        // Mostra lingua principale + numero di lingue alternative configurate
        const others = list.filter(l => l !== primary);
        el.innerText = others.length
            ? `${primary} (${T('js.bs.lang.also')} ${others.slice(0, 3).join(', ')}${others.length > 3 ? '…' : ''})`
            : primary;
    } else {
        el.innerText = primary;
    }
}
