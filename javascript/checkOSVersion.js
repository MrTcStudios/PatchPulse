// Funzione per ottenere la versione del sistema operativo (DA RIVEDERE)
export function checkOSVersion() {
    // Modificata la visualizzazione della versione del sistema operativo
    const osVersion = navigator.platform + ' ' + (navigator.userAgent.includes('Win64') || navigator.userAgent.includes('WOW64') ? '64-bit' : '32-bit');

    const osVersionElement = document.getElementById('osVersion');

    if (osVersionElement) {
        osVersionElement.innerText = osVersion;
    } else {
        console.error('Elemento con id "osVersion" non trovato.');
    }
}