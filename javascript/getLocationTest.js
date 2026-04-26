/*function getUserLocationAndIp(event) {
    event.preventDefault();

    // Sostituito il fetch a freegeoip.app con IPstack
    fetch('http://api.ipstack.com/check?access_key=159d8b50c64e7129abd15ff7a79bcf73')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Usa i dati ricevuti da IPstack
            const userIp = data.ip || 'N/D';
            const userCity = data.city || 'N/D';
            const userCountry = data.country_name || 'N/D';
            const userRegion = data.region_name || 'N/D';  // Aggiunta la regione per maggiore precisione

            console.log('Dati ricevuti da IPstack:', { userIp, userCity, userCountry, userRegion });

            // Passa i dati al modulo
            addHiddenFieldsAndSubmit(userIp, userCity, userCountry, userRegion);
        })
        .catch(err => {
            console.error('Errore nel recupero delle informazioni utente, provando con ipinfo.io:', err);

            // Fallback a ipinfo.io in caso di errore
            return fetch('https://ipinfo.io/json?token=be364e973667a6');
        })
        .then(response => {
            if (response && response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok for ipinfo.io');
        })
        .then(data => {
            // Usa i dati ricevuti da ipinfo.io in caso di fallback
            const userIp = data.ip || 'N/D';
            const userCity = data.city || 'N/D';
            const userCountry = data.country || 'N/D';
            const userRegion = data.region || 'N/D';  // Aggiunta la regione per maggiore precisione

            console.log('Dati ricevuti da ipinfo.io:', { userIp, userCity, userCountry, userRegion });

            // Passa i dati al modulo
            addHiddenFieldsAndSubmit(userIp, userCity, userCountry, userRegion);
        })
        .catch(err => console.error('Errore finale nel recupero delle informazioni utente:', err));
}

function addHiddenFieldsAndSubmit(userIp, userCity, userCountry, userRegion) {
    const form = document.getElementById('changePasswordForm');

    const ipField = document.createElement('input');
    ipField.type = 'hidden';
    ipField.name = 'user_ip';
    ipField.value = userIp;
    form.appendChild(ipField);

    const cityField = document.createElement('input');
    cityField.type = 'hidden';
    cityField.name = 'user_city';
    cityField.value = userCity;
    form.appendChild(cityField);

    const countryField = document.createElement('input');
    countryField.type = 'hidden';
    countryField.name = 'user_country';
    countryField.value = userCountry;
    form.appendChild(countryField);

    const regionField = document.createElement('input');
    regionField.type = 'hidden';
    regionField.name = 'user_region';
    regionField.value = userRegion;  // Aggiunta la regione
    form.appendChild(regionField);

    console.log('Invio del modulo con i seguenti dati:', { userIp, userCity, userCountry, userRegion });
    form.submit();
}

// Aggiungi l'evento al pulsante per cambiare password
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    form.addEventListener('submit', getUserLocationAndIp);
});
*/

// Funzione per ottenere l'IP, la città e il paese e inviarli al PHP
function getUserLocationAndIp(event) {
    event.preventDefault();

    // Utilizza Cloudflare per ottenere l'IP e la posizione
    const userIp = window._cf_chl_opt.ip || 'unknown IP';  // Assicurati che _cf_chl_opt.contenga l'IP
    const userCity = window._cf_chl_opt.city || 'unknown city';  // Città
    const userCountry = window._cf_chl_opt.country || 'unknown country';  // Paese

    // Aggiungi i dati al modulo come campi nascosti
    const form = document.getElementById('changePasswordForm');
    
    const ipField = document.createElement('input');
    ipField.type = 'hidden';
    ipField.name = 'user_ip';
    ipField.value = userIp;
    form.appendChild(ipField);

    const cityField = document.createElement('input');
    cityField.type = 'hidden';
    cityField.name = 'user_city';
    cityField.value = userCity;
    form.appendChild(cityField);

    const countryField = document.createElement('input');
    countryField.type = 'hidden';
    countryField.name = 'user_country';
    countryField.value = userCountry;
    form.appendChild(countryField);

    // Invia il modulo dopo aver aggiunto i campi nascosti
    form.submit();
}

// Aggiungi l'event listener quando il documento è pronto
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    form.addEventListener('submit', getUserLocationAndIp);
});

