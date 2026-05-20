/**
 * Recupera dettagli sui protocolli di sicurezza WebRTC (DTLS/SRTP/TLS).
 *
 * Note su precisione:
 *  - Solo Chromium espone `dtlsCipher` e `srtpCipher` in modo affidabile.
 *  - `tlsVersion` può arrivare come hex DTLS (FEFD = DTLS 1.2, FEFC = DTLS 1.3).
 *  - In Firefox/Safari di norma non sono esposti → mostriamo "Non esposto".
 *
 * Sicurezza: nessuna chiamata di rete; tutto avviene tra due RTCPeerConnection
 * locali (loopback). Il DataChannel non scambia dati.
 */
import { T } from '../lang/t.js';

export async function getSecurityProtocols() {
    const el = document.getElementById('securityProtocols');
    const setText = (t) => { if (el) el.innerText = t; };

    if (typeof RTCPeerConnection === 'undefined') {
        setText(T('js.bs.sec.no_webrtc'));
        return null;
    }

    const decodeTls = (hex) => {
        if (!hex) return null;
        const norm = String(hex).toUpperCase().replace(/[^0-9A-F]/g, '');
        const map = { 'FEFD': 'DTLS 1.2', 'FEFC': 'DTLS 1.3', 'FEFF': 'DTLS 1.0' };
        if (map[norm]) return map[norm];
        if (/^DTLS\s?1\.[0-9]/i.test(hex) || /^TLS\s?1\.[0-9]/i.test(hex)) return hex;
        return `${T('js.bs.sec.unknown_prefix')} (${hex})`;
    };

    let pc1, pc2, dc;
    try {
        pc1 = new RTCPeerConnection();
        pc2 = new RTCPeerConnection();

        pc1.onicecandidate = (e) => {
            if (e.candidate) pc2.addIceCandidate(e.candidate).catch(() => {});
        };
        pc2.onicecandidate = (e) => {
            if (e.candidate) pc1.addIceCandidate(e.candidate).catch(() => {});
        };

        dc = pc1.createDataChannel('probe');

        const offer = await pc1.createOffer();
        await pc1.setLocalDescription(offer);
        await pc2.setRemoteDescription(offer);
        const answer = await pc2.createAnswer();
        await pc2.setLocalDescription(answer);
        await pc1.setRemoteDescription(answer);

        // Aspetta che la connessione si stabilisca (max 5s)
        await new Promise((resolve) => {
            const start = performance.now();
            const tick = () => {
                if (pc1.connectionState === 'connected'
                    || pc1.iceConnectionState === 'connected'
                    || pc1.iceConnectionState === 'completed') return resolve();
                if (performance.now() - start > 5000) return resolve();
                setTimeout(tick, 200);
            };
            tick();
        });

        const stats = await pc1.getStats(null);
        let dtlsCipher = null, tlsVersion = null, srtpCipher = null;

        stats.forEach((s) => {
            if (!dtlsCipher && s.dtlsCipher) dtlsCipher = s.dtlsCipher;
            if (!tlsVersion && s.tlsVersion) tlsVersion = s.tlsVersion;
            if (!srtpCipher && s.srtpCipher) srtpCipher = s.srtpCipher;
        });

        const tlsDecoded = tlsVersion ? decodeTls(tlsVersion) : null;

        if (!dtlsCipher && !tlsDecoded && !srtpCipher) {
            setText(T('js.bs.sec.not_exposed'));
            return null;
        }

        const nd = T('js.bs.nd');
        const parts = [];
        parts.push(`${T('js.bs.sec.label_dtls')}: ${dtlsCipher || nd}`);
        parts.push(`${T('js.bs.sec.label_version')}: ${tlsDecoded || nd}`);
        parts.push(`${T('js.bs.sec.label_srtp')}: ${srtpCipher || nd}`);

        setText(parts.join(' | '));
        return { dtlsCipher, tlsVersion: tlsDecoded || tlsVersion, srtpCipher };
    } catch (err) {
        console.debug('getSecurityProtocols error:', err && err.message);
        setText(T('js.bs.sec.undetectable'));
        return null;
    } finally {
        try { if (dc)  dc.close(); }  catch (_) {}
        try { if (pc1) pc1.close(); } catch (_) {}
        try { if (pc2) pc2.close(); } catch (_) {}
    }
}
