/**
 * Mostra la memoria del dispositivo (Device Memory API).
 *
 * Note importanti per ridurre interpretazioni errate:
 *  - L'API arrotonda al valore più vicino in {0.25, 0.5, 1, 2, 4, 8} per privacy.
 *  - Il valore "8" significa "≥ 8 GB" (capped).
 *  - L'API non è supportata da Firefox/Safari → mostriamo messaggio chiaro.
 */
export function checkDeviceMemory() {
    const el = document.getElementById('deviceMemory');
    if (!el) {
        console.warn('Elemento con id "deviceMemory" non trovato.');
        return;
    }

    const mem = navigator.deviceMemory;

    if (typeof mem === 'number' && mem > 0) {
        if (mem >= 8) {
            el.innerText = '≥ 8 GB (limite massimo esposto dall\'API)';
        } else if (mem < 1) {
            el.innerText = `${mem} GB (≈ ${Math.round(mem * 1024)} MB)`;
        } else {
            el.innerText = `${mem} GB (approssimazione fornita dal browser)`;
        }
    } else {
        el.innerText = 'Non disponibile (API non supportata)';
    }
}
