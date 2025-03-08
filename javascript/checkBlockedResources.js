export function checkBlockedResources() {
    const blockedResources = [...document.querySelectorAll('img[alt=""], script[src=""]')];

    const blockedResourcesElement = document.getElementById('blockedResources');

    if (blockedResourcesElement) {
        blockedResourcesElement.innerText = (blockedResources.length > 0 ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "blockedResources" non trovato.');
    }
}