/**
 * Verifica il supporto alle Sensor API (Generic Sensor API).
 *
 * Modifiche:
 *  - "Si" → "Sì" (italiano corretto)
 *  - "N/D" → "No" (più chiaro: assenza dell'oggetto significa no support)
 *  - Aggiunto `AbsoluteOrientationSensor` e `RelativeOrientationSensor`
 *  - Su iOS Safari l'oggetto non esiste mai (Apple non supporta la Generic Sensor API),
 *    quindi indichiamo "Non supportato" invece di lista "No, No, No".
 */
export function checkSensorSupport() {
    const el = document.getElementById('sensorsSupported');
    if (!el) {
        console.warn('Elemento con id "sensorsSupported" non trovato.');
        return;
    }

    const sensors = {
        'Accelerometro': 'LinearAccelerationSensor' in window || 'Accelerometer' in window,
        'Giroscopio':    'Gyroscope' in window,
        'Magnetometro':  'Magnetometer' in window,
        'Orientamento':  'AbsoluteOrientationSensor' in window || 'RelativeOrientationSensor' in window,
    };

    const anySupported = Object.values(sensors).some(Boolean);
    if (!anySupported) {
        el.innerText = 'Non supportata (Generic Sensor API non disponibile)';
        return;
    }

    el.innerText = Object.entries(sensors)
        .map(([name, ok]) => `${name}: ${ok ? 'Sì' : 'No'}`)
        .join(', ');
}
