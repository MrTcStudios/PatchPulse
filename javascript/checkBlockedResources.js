// Funzione per controllare se ci sono risorse bloccate sulla pagina
export function checkBlockedResources() {
    const blockedResources = [...document.querySelectorAll('img[alt=""], script[src=""]')];

    // Aggiorna la visualizzazione delle risorse bloccate
    const blockedResourcesElement = document.getElementById('blockedResources');

    if (blockedResourcesElement) {
        blockedResourcesElement.innerText = (blockedResources.length > 0 ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "blockedResources" non trovato.');
    }
}