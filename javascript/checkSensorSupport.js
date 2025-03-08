// Funzione per verificare il supporto ai sensori (DA RIVEDERE)
export function checkSensorSupport() {
    const accelerometerSupported = 'LinearAccelerationSensor' in window ? 'Si' : 'N/D';
    const gyroscopeSupported = 'Gyroscope' in window ? 'Si' : 'N/D';
    const magnetometerSupported = 'Magnetometer' in window ? 'Si' : 'N/D';
    const sensorsSupportedElement = document.getElementById('sensorsSupported');

    if (sensorsSupportedElement) {
        sensorsSupportedElement.innerText = 'Accelerometro: ' + accelerometerSupported + ', Giroscopio: ' + gyroscopeSupported + ', Magnetometro: ' + magnetometerSupported;
    } else {
        console.error('Elemento con id "sensorsSupported" non trovato.');
    }
}