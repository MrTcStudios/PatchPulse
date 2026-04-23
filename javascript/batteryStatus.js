export async function getBatteryStatus() {
    var statusDiv = document.getElementById('batteryStatus');
    if (!statusDiv) return;

    if (!navigator.getBattery) {
        statusDiv.textContent = "Non supportata dal tuo browser";
        statusDiv.classList.remove('loading');
        return;
    }

    try {
        var battery = await navigator.getBattery();

        function updateBatteryInfo() {
            var level = Math.round(battery.level * 100);
            var charging = battery.charging;
            var chargingTime = battery.chargingTime;
            var dischargingTime = battery.dischargingTime;

            var statusText = level + '% ';
            statusText += charging ? '(In carica)' : '(Non in carica)';

            if (charging && chargingTime !== Infinity) {
                statusText += ' - Carica completa in: ' + Math.round(chargingTime / 60) + ' min';
            } else if (!charging && dischargingTime !== Infinity) {
                statusText += ' - Scarica in: ' + Math.round(dischargingTime / 60) + ' min';
            }

            statusDiv.textContent = statusText;
            statusDiv.classList.remove('loading');
        }

        updateBatteryInfo();
        battery.addEventListener('chargingchange', updateBatteryInfo);
        battery.addEventListener('levelchange', updateBatteryInfo);
        battery.addEventListener('chargingtimechange', updateBatteryInfo);
        battery.addEventListener('dischargingtimechange', updateBatteryInfo);
    } catch (error) {
        statusDiv.textContent = 'Non disponibile';
        statusDiv.classList.remove('loading');
    }
}
