/**
 * Verifica il supporto a WebRTC.
 *
 * Modifica chiave: il check originale dichiarava "Disabilitato" se non
 * arrivava un ICE candidate, ma WebRTC potrebbe essere SUPPORTATO e solo
 * bloccato dal firewall/VPN. Distinguiamo:
 *  - "Non supportato": l'API RTCPeerConnection non esiste.
 *  - "Abilitato": almeno un candidate ricevuto dal server STUN.
 *  - "Limitato": API presente ma niente candidate (firewall / WebRTC disabilitato lato browser).
 *
 * Sicurezza: usiamo lo STUN server pubblico di Google solo per testing dell'IP
 * pubblico. Se non si vuole nessuna chiamata esterna, si può rimuovere
 * `iceServers` e WebRTC tornerà comunque candidate locali (host).
 */
export function checkWebRTC() {
    return new Promise((resolve) => {
        const el = document.getElementById('webrtcSupport');
        const setText = (txt) => { if (el) el.innerText = txt; };

        const RTCPeer = window.RTCPeerConnection
            || window.webkitRTCPeerConnection
            || window.mozRTCPeerConnection;

        if (!RTCPeer) {
            setText('Non supportato');
            return resolve(false);
        }

        let pc;
        try {
            pc = new RTCPeer({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
        } catch (_) {
            setText('Non supportato');
            return resolve(false);
        }

        let gotCandidate = false;
        let resolved = false;

        const finish = (label, value) => {
            if (resolved) return;
            resolved = true;
            try { pc.close(); } catch (_) {}
            setText(label);
            resolve(value);
        };

        try {
            pc.createDataChannel('probe');
            pc.onicecandidate = (e) => { if (e && e.candidate) gotCandidate = true; };
            pc.onicegatheringstatechange = () => {
                if (pc.iceGatheringState === 'complete') {
                    finish(gotCandidate ? 'Abilitato' : 'Limitato (API attiva, niente candidate)', gotCandidate);
                }
            };
            pc.createOffer()
                .then((offer) => pc.setLocalDescription(offer))
                .catch(() => finish('Limitato (errore offerta)', false));
        } catch (_) {
            return finish('Limitato (errore inizializzazione)', false);
        }

        // Timeout di sicurezza
        setTimeout(() => {
            if (resolved) return;
            finish(gotCandidate ? 'Abilitato' : 'Limitato (timeout ICE)', gotCandidate);
        }, 3000);
    });
}
