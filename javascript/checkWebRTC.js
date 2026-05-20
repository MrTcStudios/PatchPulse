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
import { T } from '../lang/t.js';

export function checkWebRTC() {
    return new Promise((resolve) => {
        const el = document.getElementById('webrtcSupport');
        const setText = (txt) => { if (el) el.innerText = txt; };

        const RTCPeer = window.RTCPeerConnection
            || window.webkitRTCPeerConnection
            || window.mozRTCPeerConnection;

        if (!RTCPeer) {
            setText(T('js.bs.webrtc.unsupported'));
            return resolve(false);
        }

        let pc;
        try {
            pc = new RTCPeer({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
        } catch (_) {
            setText(T('js.bs.webrtc.unsupported'));
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
                    finish(gotCandidate ? T('js.bs.webrtc.enabled') : T('js.bs.webrtc.limited_no_candidate'), gotCandidate);
                }
            };
            pc.createOffer()
                .then((offer) => pc.setLocalDescription(offer))
                .catch(() => finish(T('js.bs.webrtc.limited_offer_err'), false));
        } catch (_) {
            return finish(T('js.bs.webrtc.limited_init_err'), false);
        }

        // Timeout di sicurezza
        setTimeout(() => {
            if (resolved) return;
            finish(gotCandidate ? T('js.bs.webrtc.enabled') : T('js.bs.webrtc.limited_timeout'), gotCandidate);
        }, 3000);
    });
}
