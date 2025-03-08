// Funzione per ottenere la Referrer Policy (DA RIVEDERE)
export function getReferrerPolicy() {
    const metaTags = document.getElementsByTagName('meta');
    let referrerPolicy = 'N/D';

    for (let i = 0; i < metaTags.length; i++) {
        if (metaTags[i].getAttribute('name') === 'referrer') {
            referrerPolicy = metaTags[i].getAttribute('content');
            break;
        }
    }

    const referrerPolicyElement = document.getElementById('referrerPolicy');

    if (referrerPolicyElement) {
        referrerPolicyElement.innerText = referrerPolicy;
    } else {
        console.error('Elemento con id "referrerPolicy" non trovato.');
    }
}