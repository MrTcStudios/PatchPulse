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
import { T } from '../lang/t.js';

export function checkSensorSupport() {
    const el = document.getElementById('sensorsSupported');
    if (!el) {
        console.warn('Elemento con id "sensorsSupported" non trovato.');
        return;
    }

    const sensors = {
        [T('js.bs.sensor.accelerometer')]: 'LinearAccelerationSensor' in window || 'Accelerometer' in window,
        [T('js.bs.sensor.gyroscope')]:     'Gyroscope' in window,
        [T('js.bs.sensor.magnetometer')]:  'Magnetometer' in window,
        [T('js.bs.sensor.orientation')]:   'AbsoluteOrientationSensor' in window || 'RelativeOrientationSensor' in window,
    };

    const anySupported = Object.values(sensors).some(Boolean);
    if (!anySupported) {
        el.innerText = T('js.bs.sensor.unsupported');
        return;
    }

    const yesT = T('js.bs.yes');
    const noT = T('js.bs.no');
    el.innerText = Object.entries(sensors)
        .map(([name, ok]) => `${name}: ${ok ? yesT : noT}`)
        .join(', ');
}
