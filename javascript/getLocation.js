// Funzione per ottenere informazioni sulla posizione geografica
export function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
        const geolocationInfoElement = document.getElementById('geolocationInfo');
        if (geolocationInfoElement) {
            geolocationInfoElement.innerText = 'Geolocalizzazione non supportata dal browser.';
        } else {
            console.error('Elemento con id "geolocationInfo" non trovato.');
        }
    }
}

export function showPosition(position) {
    const geolocationInfoElement = document.getElementById('geolocationInfo');
    const mapFrame = document.getElementById('mapFrame');
    if (geolocationInfoElement && mapFrame) {
        let Latitude = position.coords.latitude;
        let Longitude = position.coords.longitude;
        
        geolocationInfoElement.innerText = 'Latitudine: ' + Latitude + '°, Longitudine: ' + Longitude + '°';
        
        var mapUrl = "https://www.openstreetmap.org/export/embed.html?bbox=";
        mapUrl += (Longitude - 0.01) + "," + (Latitude - 0.01) + "," + (Longitude + 0.01) + "," + (Latitude + 0.01);
        mapUrl += "&layer=mapnik&marker=" + Latitude + "," + Longitude;

        mapFrame.setAttribute('src', mapUrl);

        mapFrame.style.display = 'block';
    } else {
        console.error('Elemento con id "geolocationInfo" o "mapFrame" non trovato.');
    }
}

export function showError(error) {
    const geolocationInfoElement = document.getElementById('geolocationInfo');
    const mapFrame = document.getElementById('mapFrame');
    if (geolocationInfoElement) {
        geolocationInfoElement.innerText = 'Errore durante l\'ottenimento della posizione: ' + error.message;
        
        fetch('https://ipinfo.io/json?token=')
            .then(response => response.json())
            .then(data => {
                const loc = data.loc.split(',');
                const Latitude = parseFloat(loc[0]);
                const Longitude = parseFloat(loc[1]);
                
                geolocationInfoElement.innerText += ` (Posizione approssimativa: Latitudine: ${Latitude}°, Longitudine: ${Longitude}°)`;
                
                var mapUrl = "https://www.openstreetmap.org/export/embed.html?bbox=";
                mapUrl += (Longitude - 0.1) + "," + (Latitude - 0.1) + "," + (Longitude + 0.1) + "," + (Latitude + 0.1);
                mapUrl += "&layer=mapnik&marker=" + Latitude + "," + Longitude;

                mapFrame.setAttribute('src', mapUrl);

                mapFrame.style.display = 'block';
            })
            .catch(err => console.error('Errore durante l\'ottenimento della posizione approssimativa:', err));
    } else {
        console.error('Elemento con id "geolocationInfo" non trovato.');
    }
}