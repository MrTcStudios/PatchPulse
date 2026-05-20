/**
 * Mostra lo user agent del browser, ovvero la "firma" principale che ogni sito
 * web vede quando l'utente lo visita. Questa stringa identifica browser, versione,
 * sistema operativo e architettura, ed è uno dei segnali più importanti usati
 * per il fingerprinting passivo.
 */
import { T } from '../lang/t.js';

export function checkBrowserFingerprinting() {
    const ua = fingerprintUser();
    const el = document.getElementById('browserFingerprinting');
    if (!el) {
        console.warn('Elemento con id "browserFingerprinting" non trovato.');
        return;
    }

    el.innerText = ua || T('js.bs.unavailable');
    // Tooltip con la stringa completa, utile se viene troncata visivamente
    el.title = ua || '';
}

/**
 * Ritorna lo user agent del browser.
 * Mantenuto come export separato perché chiamato direttamente da fastScan.js.
 */
export function fingerprintUser() {
    return (navigator && navigator.userAgent) ? navigator.userAgent : '';
}
