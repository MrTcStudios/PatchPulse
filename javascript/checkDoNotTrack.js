// Funzione per verificare le politiche di "Do Not Track"
export function checkDoNotTrack() {
    // Modificata la verifica delle politiche di "Do Not Track"
    const doNotTrackEnabled = navigator.doNotTrack === '1' || window.doNotTrack === '1' || navigator.msDoNotTrack === '1';

    const doNotTrackElement = document.getElementById('doNotTrack');

    if (doNotTrackElement) {
        doNotTrackElement.innerText = (doNotTrackEnabled ? 'Attivato' : 'Disattivato');
    } else {
        console.error('Elemento con id "doNotTrack" non trovato.');
    }
}