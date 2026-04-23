export function getMimeTypes() {
    try {
        const rawMimeList = (typeof navigator !== 'undefined' && navigator.mimeTypes)
            ? Array.from(navigator.mimeTypes)
            : [];

        const types = rawMimeList
            .map(mt => (mt && typeof mt.type === 'string') ? mt.type.trim() : '')
            .filter(Boolean);

        const uniqueSorted = Array.from(new Set(types)).sort((a, b) => a.localeCompare(b));

        const displayText = uniqueSorted.length ? uniqueSorted.join(', ') : 'Nessuno';

        const mimeTypesElement = document.getElementById('mimeTypes');
        if (mimeTypesElement) {
            mimeTypesElement.innerText = displayText;

            if (uniqueSorted.length) {
                mimeTypesElement.setAttribute('title', displayText);
                mimeTypesElement.setAttribute('aria-label', `Tipi MIME supportati: ${displayText}`);
            } else {
                mimeTypesElement.removeAttribute('title');
                mimeTypesElement.setAttribute('aria-label', 'Nessun tipo MIME rilevato');
            }
        } else {
            console.error('Elemento con id "mimeTypes" non trovato.');
        }

        return uniqueSorted;
    } catch (err) {
        console.error('Errore durante l\'ottenimento dei tipi MIME:', err);

        const mimeTypesElement = document.getElementById('mimeTypes');
        if (mimeTypesElement) mimeTypesElement.innerText = 'Nessuno';

        return [];
    }
}
