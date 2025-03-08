// Funzione per verificare la possibilità di identificare univocamente un utente attraverso l'utilizzo di WebGL
export function checkWebGLFingerprinting() {
    const canvas = document.createElement('canvas');
    const supportsWebGL = !!canvas.getContext('webgl');
    const supportsWebGL2 = !!canvas.getContext('webgl2');
    const webglFingerprint = 'WebGL: ' + (supportsWebGL ? 'Supportato' : 'Non Supportato') + ', WebGL2: ' + (supportsWebGL2 ? 'Supportato' : 'Non Supportato');
    document.getElementById('webglFingerprinting').innerText = webglFingerprint;
}