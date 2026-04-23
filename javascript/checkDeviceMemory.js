export function checkDeviceMemory() {
    var deviceMemoryElement = document.getElementById('deviceMemory');

    if (!deviceMemoryElement) {
        console.error('Elemento con id "deviceMemory" non trovato.');
        return;
    }

    if (navigator.deviceMemory) {
        deviceMemoryElement.innerText = navigator.deviceMemory + ' GB (approssimativa, limitata dal browser per privacy)';
    } else {
        deviceMemoryElement.innerText = 'Non rilevabile (API non supportata da questo browser)';
    }
}
