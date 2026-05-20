/**
 * Verifica solo lo STATO del permesso di geolocalizzazione, senza richiederla.
 *
 * BUG corretto: il codice precedente scriveva su `#status`, un elemento che
 * non esiste in browser-scan.php. L'elemento corretto è `#geolocationInfo`.
 *
 * Non chiama mai navigator.geolocation.getCurrentPosition: la richiesta vera
 * è gestita da getLocation.js, dove l'utente acconsente esplicitamente.
 */
import { T } from '../lang/t.js';

export function checkGeolocation() {
    const el = document.getElementById('geolocationInfo');
    if (!el) {
        console.warn('Elemento con id "geolocationInfo" non trovato.');
        return;
    }

    if (!('geolocation' in navigator)) {
        el.textContent = T('js.bs.geo.unsupported');
        return;
    }

    if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
        // Permissions API non disponibile (Safari fino a recente): ci limitiamo a dire "supportata"
        el.textContent = T('js.bs.geo.available_prompt');
        return;
    }

    navigator.permissions.query({ name: 'geolocation' })
        .then((result) => {
            switch (result.state) {
                case 'granted':
                    el.textContent = T('js.bs.geo.granted');
                    break;
                case 'prompt':
                    el.textContent = T('js.bs.geo.available_prompt');
                    break;
                case 'denied':
                    el.textContent = T('js.bs.geo.denied_perm');
                    break;
                default:
                    el.textContent = T('js.bs.geo.unknown_state');
            }
        })
        .catch(() => {
            el.textContent = T('js.bs.geo.unverifiable');
        });
}
