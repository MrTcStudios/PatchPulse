// Funzione per ottenere informazioni sulla memoria del dispositivo (DA RIVEDERE)
export function checkDeviceMemory() {
    const deviceMemory = navigator.deviceMemory || 'sconosciuta';

    const deviceMemoryElement = document.getElementById('deviceMemory');

    if (deviceMemoryElement) {
        deviceMemoryElement.innerText = (deviceMemory) + ' GB (Approssimativa)';
    } else {
        console.error('Elemento con id "deviceMemory" non trovato.');
    }
}