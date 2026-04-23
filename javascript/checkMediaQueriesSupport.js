export function checkMediaQueriesSupport() {
    const mediaQueriesSupported = window.matchMedia('(pointer: fine)').matches;
    const mediaQueriesSupportedElement = document.getElementById('mediaQueriesSupported');

    if (mediaQueriesSupportedElement) {
        mediaQueriesSupportedElement.innerText = (mediaQueriesSupported ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "mediaQueriesSupported" non trovato.');
    }
}
