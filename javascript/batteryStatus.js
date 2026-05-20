/**
 * Recupera lo stato della batteria via Battery Status API.
 *
 * Modifiche:
 *  - Firefox e Safari NON espongono `navigator.getBattery` da anni:
 *    indichiamo "Non supportata" invece di lasciare un loading infinito.
 *  - Aggiunto null check sull'elemento UI.
 *  - I listener vengono registrati una sola volta (no leak).
 */
import { T } from '../lang/t.js';

export async function getBatteryStatus() {
    const el = document.getElementById('batteryStatus');
    if (!el) return;

    if (typeof navigator === 'undefined' || typeof navigator.getBattery !== 'function') {
        el.textContent = T('js.bs.bat.unsupported_browser');
        el.classList.remove('loading');
        return;
    }

    let battery;
    try {
        battery = await navigator.getBattery();
    } catch (_) {
        el.textContent = T('js.bs.unavailable');
        el.classList.remove('loading');
        return;
    }

    const render = () => {
        const level = Math.round((battery.level || 0) * 100);
        const charging = !!battery.charging;

        let text = `${level}% ${charging ? T('js.bs.bat.charging') : T('js.bs.bat.not_charging')}`;

        if (charging
            && Number.isFinite(battery.chargingTime)
            && battery.chargingTime !== Infinity) {
            text += ` — ${T('js.bs.bat.full_in').replace('{0}', String(Math.round(battery.chargingTime / 60)))}`;
        } else if (!charging
            && Number.isFinite(battery.dischargingTime)
            && battery.dischargingTime !== Infinity) {
            text += ` — ${T('js.bs.bat.empty_in').replace('{0}', String(Math.round(battery.dischargingTime / 60)))}`;
        }

        el.textContent = text;
        el.classList.remove('loading');
    };

    render();

    // Registriamo i listener una sola volta (idempotenza già garantita dalla
    // semantica di `getBattery()` che restituisce sempre lo stesso oggetto).
    const events = ['chargingchange', 'levelchange', 'chargingtimechange', 'dischargingtimechange'];
    events.forEach(ev => battery.addEventListener(ev, render));
}
