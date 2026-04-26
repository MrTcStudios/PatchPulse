/**
 * Recupera la posizione geografica del browser.
 *
 * Modifiche di sicurezza/privacy:
 *  - Non chiamare AUTOMATICAMENTE il backend ipinfo se il GPS fallisce:
 *    significherebbe geolocalizzare l'utente senza il suo consenso esplicito.
 *    Mostriamo invece un messaggio chiaro e (opzionale) un bottone esplicito.
 *  - Tutti gli inserimenti DOM avvengono via textContent → niente XSS dai
 *    parametri (lat/lng vengono passati come number, non concatenati nell'HTML).
 *  - Il src dell'iframe ha valori numerici solo, validati con Number.isFinite.
 */
export function getLocation() {
    const el = document.getElementById('geolocationInfo');
    if (!el) {
        console.warn('Elemento con id "geolocationInfo" non trovato.');
        return;
    }

    if (!navigator.geolocation) {
        el.textContent = 'Geolocalizzazione non supportata dal browser.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => showPosition(pos, el),
        (err) => showError(err, el),
        { timeout: 8000, maximumAge: 60000 }
    );
}

export function showPosition(position, el) {
    el = el || document.getElementById('geolocationInfo');
    const mapFrame = document.getElementById('mapFrame');
    if (!el) return;

    const lat = Number(position?.coords?.latitude);
    const lng = Number(position?.coords?.longitude);

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        el.textContent = 'Posizione non disponibile.';
        return;
    }

    el.textContent = `Latitudine: ${lat.toFixed(6)}°, Longitudine: ${lng.toFixed(6)}°`;

    if (mapFrame) {
        const bbox = [lng - 0.01, lat - 0.01, lng + 0.01, lat + 0.01].join(',');
        const url  = `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat},${lng}`;
        mapFrame.setAttribute('src', url);
        mapFrame.style.display = 'block';
    }
}

export function showError(error, el) {
    el = el || document.getElementById('geolocationInfo');
    if (!el) return;

    let msg;
    switch (error?.code) {
        case 1: msg = 'Permesso negato dall\'utente.'; break;
        case 2: msg = 'Posizione non disponibile.'; break;
        case 3: msg = 'Timeout durante la richiesta.'; break;
        default: msg = error?.message || 'Errore sconosciuto.';
    }

    el.textContent = `Geolocalizzazione non disponibile (${msg})`;
    // Niente fallback automatico al server: rispettiamo la scelta dell'utente.
}
