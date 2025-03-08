//NON FUNZIONANTE (NON SERVE AL MOMENTO)
/*
function getUserLocationAndIp(event) {
    event.preventDefault();

    
    const userIp = window._cf_chl_opt.ip || 'unknown IP';
    const userCity = window._cf_chl_opt.city || 'unknown city';
    const userCountry = window._cf_chl_opt.country || 'unknown country';

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

    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    form.addEventListener('submit', getUserLocationAndIp);
});
*/
