// Funzione per ottenere i tipi di MIME supportati
export function getMimeTypes() {
    const mimeTypes = Array.from(navigator.mimeTypes).map(mt => mt.type);
    const mimeTypesElement = document.getElementById('mimeTypes');

    if (mimeTypesElement) {
        mimeTypesElement.innerText = mimeTypes.join(', ');
    } else {
        console.error('Elemento con id "mimeTypes" non trovato.');
    }
}