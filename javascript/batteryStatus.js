/**
 * Recupera lo stato della batteria via Battery Status API.
 *
 * Modifiche:
 *  - Firefox e Safari NON espongono `navigator.getBattery` da anni:
 *    indichiamo "Non supportata" invece di lasciare un loading infinito.
 *  - Aggiunto null check sull'elemento UI.
 *  - I listener vengono registrati una sola volta (no leak).
 */
export async function getBatteryStatus() {
    const el = document.getElementById('batteryStatus');
    if (!el) return;

    if (typeof navigator === 'undefined' || typeof navigator.getBattery !== 'function') {
        el.textContent = 'Non supportata dal browser';
        el.classList.remove('loading');
        return;
    }

    let battery;
    try {
        battery = await navigator.getBattery();
    } catch (_) {
        el.textContent = 'Non disponibile';
        el.classList.remove('loading');
        return;
    }

    const render = () => {
        const level = Math.round((battery.level || 0) * 100);
        const charging = !!battery.charging;

        let text = `${level}% ${charging ? '(In carica)' : '(Non in carica)'}`;

        if (charging
            && Number.isFinite(battery.chargingTime)
            && battery.chargingTime !== Infinity) {
            text += ` — Carica completa in ${Math.round(battery.chargingTime / 60)} min`;
        } else if (!charging
            && Number.isFinite(battery.dischargingTime)
            && battery.dischargingTime !== Infinity) {
            text += ` — Scarica in ${Math.round(battery.dischargingTime / 60)} min`;
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
