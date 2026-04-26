/**
 * Verifica le politiche di tracciamento DNT / GPC.
 *
 * Modifiche:
 *  - Aggiunto fallback su `window.top.doNotTrack` (alcuni Safari più vecchi).
 *  - Gestione di tutti i valori validi del DNT ('1', 'yes', '0', 'unspecified').
 *  - GPC ha precedenza su DNT nel report perché DNT è deprecato.
 */
export function checkDoNotTrack() {
    const el = document.getElementById('doNotTrack');
    if (!el) return;

    const dntValue =
        navigator.doNotTrack
        ?? window.doNotTrack
        ?? (window.top && window.top.doNotTrack)
        ?? null;

    const dntActive = dntValue === '1' || dntValue === 'yes';
    const gpcActive = navigator.globalPrivacyControl === true;

    if (gpcActive && dntActive) {
        el.innerText = 'Attivato (DNT + Global Privacy Control)';
    } else if (gpcActive) {
        el.innerText = 'Attivato (Global Privacy Control)';
    } else if (dntActive) {
        el.innerText = 'Attivato (DNT — deprecato, molti siti lo ignorano)';
    } else {
        el.innerText = 'Disattivato';
    }
}
