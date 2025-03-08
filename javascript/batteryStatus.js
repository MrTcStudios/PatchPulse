export async function getBatteryStatus() {
    // Controlla se l'API Battery Status è supportata
    if (!navigator.getBattery) {
        document.getElementById('batteryStatus').textContent = "API Battery Status non supportata dal tuo browser.";
        return;
    }

    try {
        const battery = await navigator.getBattery();
        
        // Funzione per aggiornare lo stato della batteria
        function updateBatteryInfo() {
            const level = Math.round(battery.level * 100); // Percentuale di carica
            const charging = battery.charging;
            const chargingTime = battery.chargingTime; // Tempo rimanente per la carica in secondi
            const dischargingTime = battery.dischargingTime; // Tempo rimanente per scarica in secondi

            let statusText = `${level}% `;
            statusText += charging
                ? "(In carica)"
                : "(Non in carica)";

            if (charging && chargingTime !== Infinity) {
                statusText += ` - Tempo per carica completa: ${Math.round(chargingTime / 60)} minuti`;
            } else if (!charging && dischargingTime !== Infinity) {
                statusText += ` - Tempo stimato per scarica: ${Math.round(dischargingTime / 60)} minuti`;
            }

            // Aggiorna il testo e il colore
            const statusDiv = document.getElementById('batteryStatus');
            statusDiv.textContent = statusText;
            statusDiv.className = charging ? 'charging' : 'not-charging';
        }

        // Aggiorna lo stato iniziale
        updateBatteryInfo();

        // Event listeners per i cambiamenti dello stato della batteria
        battery.addEventListener('chargingchange', updateBatteryInfo);
        battery.addEventListener('levelchange', updateBatteryInfo);
        battery.addEventListener('chargingtimechange', updateBatteryInfo);
        battery.addEventListener('dischargingtimechange', updateBatteryInfo);
    } catch (error) {
        document.getElementById('batteryStatus').textContent = "Errore nel recupero delle informazioni sulla batteria.";
        console.error(error);
    }
}
