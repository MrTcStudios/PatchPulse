export async function getSecurityProtocols() {
  const el = document.getElementById('securityProtocols');

  function setEl(text) {
    if (el) el.innerText = text;
    else console.error('Elemento con id "securityProtocols" non trovato.');
  }

  function log(msg){ console.debug('[getSecurityProtocols] ' + msg); }

  function decodeTlsVersionHex(hex) {
    if (!hex) return null;
    const up = String(hex).toUpperCase().replace(/[^0-9A-F]/g, '');
    const map = { 'FEFD': 'DTLS 1.2', 'FEFC': 'DTLS 1.3', 'FEFF': 'DTLS 1.0' };
    if (map[up]) return map[up];
    if (/^DTLS\s?1\.[0-9]/i.test(hex) || /^TLS\s?1\.[0-9]/i.test(hex)) return hex;
    return `sconosciuto (${hex})`;
  }

  const ND = "N/D";

  if (typeof RTCPeerConnection === 'undefined') {
    setEl(ND);
    return ND;
  }

  let pc1 = null;
  let pc2 = null;
  let dc = null;
  let statsInterval = null;

  try {
    pc1 = new RTCPeerConnection();
    pc2 = new RTCPeerConnection();

    pc1.onicecandidate = (e) => { if (e.candidate) pc2.addIceCandidate(e.candidate).catch(err => log('pc2.addIceCandidate err: ' + err)); };
    pc2.onicecandidate = (e) => { if (e.candidate) pc1.addIceCandidate(e.candidate).catch(err => log('pc1.addIceCandidate err: ' + err)); };

    pc1.onconnectionstatechange = () => log('pc1 connectionState: ' + pc1.connectionState);
    pc2.onconnectionstatechange = () => log('pc2 connectionState: ' + pc2.connectionState);

    dc = pc1.createDataChannel('probe-channel');
    dc.onopen = () => log('DataChannel aperto');
    pc2.ondatachannel = (ev) => {
      ev.channel.onopen = () => log('pc2 datachannel open');
      ev.channel.onmessage = () => {};
    };

    const offer = await pc1.createOffer();
    await pc1.setLocalDescription(offer);
    await pc2.setRemoteDescription(offer);

    const answer = await pc2.createAnswer();
    await pc2.setLocalDescription(answer);
    await pc1.setRemoteDescription(answer);

    const waitForConnected = (pc, timeoutMs = 8000) => new Promise(resolve => {
      const start = performance.now();
      (function check() {
        const st = pc.connectionState || pc.iceConnectionState || 'unknown';
        if (pc.connectionState === 'connected' || pc.connectionState === 'completed' ||
            pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
          return resolve(true);
        }
        if (performance.now() - start > timeoutMs) return resolve(false);
        setTimeout(check, 200);
      })();
    });

    await waitForConnected(pc1, 8000);

    const report = await pc1.getStats(null);
    let found = { dtlsCipher: null, tlsVersion: null, srtpCipher: null };

    report.forEach(stat => {
      if (!found.dtlsCipher && 'dtlsCipher' in stat) found.dtlsCipher = stat.dtlsCipher;
      if (!found.tlsVersion && 'tlsVersion' in stat) found.tlsVersion = stat.tlsVersion;
      if (!found.srtpCipher && 'srtpCipher' in stat) found.srtpCipher = stat.srtpCipher;

      for (const k in stat) {
        if (!found.dtlsCipher && k.toLowerCase().includes('dtlscipher') && stat[k]) found.dtlsCipher = stat[k];
        if (!found.tlsVersion && k.toLowerCase().includes('tlsversion') && stat[k]) found.tlsVersion = stat[k];
        if (!found.srtpCipher && k.toLowerCase().includes('srtpcipher') && stat[k]) found.srtpCipher = stat[k];
      }
    });

    const tlsDecoded = found.tlsVersion ? decodeTlsVersionHex(found.tlsVersion) : null;

    if (!found.dtlsCipher && !tlsDecoded && !found.srtpCipher) {
      setEl(ND);
      return ND;
    }

    const textParts = [];
    if (found.dtlsCipher) textParts.push(`DTLS cipher: ${found.dtlsCipher}`);
    else textParts.push(`DTLS cipher: ${ND}`);
    if (tlsDecoded) textParts.push(`DTLS/TLS version: ${tlsDecoded}`);
    else if (found.tlsVersion) textParts.push(`DTLS/TLS version: ${found.tlsVersion}`);
    else textParts.push(`DTLS/TLS version: ${ND}`);
    if (found.srtpCipher) textParts.push(`SRTP cipher: ${found.srtpCipher}`);
    else textParts.push(`SRTP cipher: ${ND}`);

    const finalText = textParts.join(' | ');
    setEl(finalText);

    return {
      dtlsCipher: found.dtlsCipher || null,
      tlsVersion: tlsDecoded || (found.tlsVersion || null),
      srtpCipher: found.srtpCipher || null
    };

  } catch (err) {
    log('Errore durante rilevamento protocolli: ' + (err && err.message ? err.message : err));
    setEl(ND);
    return ND;
  } finally {
    try { if (dc) dc.close(); } catch(e){}
    try { if (pc1) pc1.close(); } catch(e){}
    try { if (pc2) pc2.close(); } catch(e){}
    dc = pc1 = pc2 = null;
  }
}
