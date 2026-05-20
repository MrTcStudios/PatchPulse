/**
 * Mostra il numero di thread logici esposti dal browser.
 *
 * BUG corretto: il calcolo precedente faceva `Math.ceil(threads / 2)` per
 * stimare i "core fisici" assumendo che TUTTE le CPU abbiano hyperthreading.
 * È falso su molte CPU mobile, ARM e CPU low-end → introduceva un errore
 * sistematico per metà degli utenti.
 *
 * Comportamento attuale: mostriamo i thread logici (dato preciso) e
 * indichiamo che il numero di core fisici NON è esposto dal browser.
 */
import { T } from '../lang/t.js';

export function getCPUThreadsAndCores() {
    const threadsEl = document.getElementById('cpuThreads');
    const coresEl   = document.getElementById('cpuCores');

    const logical = navigator.hardwareConcurrency;
    const hasValue = typeof logical === 'number' && logical > 0;

    if (threadsEl) {
        threadsEl.innerText = hasValue
            ? T('js.bs.cpu.threads').replace('{0}', String(logical))
            : T('js.bs.unavailable');
    } else {
        console.warn('Elemento con id "cpuThreads" non trovato.');
    }

    if (coresEl) {
        // Il browser non espone il numero di core fisici. Diciamolo.
        coresEl.innerText = hasValue
            ? T('js.bs.cpu.not_exposed')
            : T('js.bs.unavailable');
    } else {
        console.warn('Elemento con id "cpuCores" non trovato.');
    }
}
