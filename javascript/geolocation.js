/**
 * Verifica solo lo STATO del permesso di geolocalizzazione, senza richiederla.
 *
 * BUG corretto: il codice precedente scriveva su `#status`, un elemento che
 * non esiste in browser-scan.php. L'elemento corretto è `#geolocationInfo`.
 *
 * Non chiama mai navigator.geolocation.getCurrentPosition: la richiesta vera
 * è gestita da getLocation.js, dove l'utente acconsente esplicitamente.
 */
export function checkGeolocation() {
    const el = document.getElementById('geolocationInfo');
    if (!el) {
        console.warn('Elemento con id "geolocationInfo" non trovato.');
        return;
    }

    if (!('geolocation' in navigator)) {
        el.textContent = 'Geolocalizzazione non supportata dal browser.';
        return;
    }

    if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
        // Permissions API non disponibile (Safari fino a recente): ci limitiamo a dire "supportata"
        el.textContent = 'Disponibile (richiede consenso)';
        return;
    }

    navigator.permissions.query({ name: 'geolocation' })
        .then((result) => {
            switch (result.state) {
                case 'granted':
                    el.textContent = 'Permesso concesso';
                    break;
                case 'prompt':
                    el.textContent = 'Disponibile (richiede consenso)';
                    break;
                case 'denied':
                    el.textContent = 'Permesso negato';
                    break;
                default:
                    el.textContent = 'Stato sconosciuto';
            }
        })
        .catch(() => {
            el.textContent = 'Stato non verificabile';
        });
}
