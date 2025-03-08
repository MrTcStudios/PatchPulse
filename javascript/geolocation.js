export function checkGeolocation() {
    const statusElement = document.getElementById('status');

    if ('geolocation' in navigator) {
        navigator.permissions.query({ name: 'geolocation' })
            .then((result) => {
                if (result.state === 'granted') {
                    statusElement.textContent = "Geolocalizzazione abilitata";
                    statusElement.className = "status enabled";
                } else if (result.state === 'prompt') {
                    statusElement.textContent = "Geolocalizzazione abilitabile (richiede conferma).";
                    statusElement.className = "status enabled";
                } else {
                    statusElement.textContent = "Geolocalizzazione disabilitata o negata.";
                    statusElement.className = "status disabled";
                }
            })
            .catch(() => {
                statusElement.textContent = "Impossibile verificare lo stato della geolocalizzazione.";
                statusElement.className = "status disabled";
            });
    } else {
        statusElement.textContent = "Geolocalizzazione non supportata dal browser.";
        statusElement.className = "status disabled";
    }
}
