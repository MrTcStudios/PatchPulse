export function checkWebRTC() {
    let isWebRTCAvailable = false;

    return new Promise(resolve => {
        const RTCPeer =
            window.RTCPeerConnection ||
            window.webkitRTCPeerConnection ||
            window.mozRTCPeerConnection;

        if (!RTCPeer) {
            updateStatus(false);
            return resolve(false);
        }

        const pc = new RTCPeer({ iceServers: [{ urls: "stun:stun.l.google.com:19302" }] });
        let gotCandidate = false;

        pc.createDataChannel("");

        pc.onicecandidate = event => {
            if (event && event.candidate) {
                gotCandidate = true;
            }
        };

        pc.onicegatheringstatechange = () => {
            if (pc.iceGatheringState === "complete") {
                pc.close();
                isWebRTCAvailable = gotCandidate;
                updateStatus(isWebRTCAvailable);
                resolve(isWebRTCAvailable);
            }
        };

        pc.createOffer()
            .then(offer => pc.setLocalDescription(offer))
            .catch(() => {
                updateStatus(false);
                resolve(false);
            });

        setTimeout(() => {
            if (pc.iceGatheringState !== "complete") {
                pc.close();
                updateStatus(gotCandidate);
                resolve(gotCandidate);
            }
        }, 3000);
    });

    function updateStatus(state) {
        const webrtcSupportElement = document.getElementById('webrtcSupport');
        if (webrtcSupportElement) {
            webrtcSupportElement.innerText = state ? 'Abilitato' : 'Disabilitato';
        } else {
            console.error('Elemento con id "webrtcSupport" non trovato.');
        }
    }
}
