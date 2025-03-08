// Funzione per verificare il supporto di WebRTC senza la parte sulle potenziali fughe (DA RIVEDERE)
export function checkWebRTC() {
    // Verifica il supporto di WebRTC
    const isWebRTCAvailable = !!(
        window.RTCPeerConnection ||
        window.webkitRTCPeerConnection ||
        window.mozRTCPeerConnection
    );

    const webrtcSupportElement = document.getElementById('webrtcSupport');

    if (webrtcSupportElement) {
        webrtcSupportElement.innerText = (isWebRTCAvailable ? 'Abilitato' : 'Disabilitato');
    } else {
        console.error('Elemento con id "webrtcSupport" non trovato.');
    }
}