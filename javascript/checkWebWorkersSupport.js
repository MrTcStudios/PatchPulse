// Funzione per verificare il supporto ai Web Workers
export function checkWebWorkersSupport() {
    const webWorkersSupported = typeof(Worker) !== 'undefined';
    const webWorkersSupportedElement = document.getElementById('webWorkersSupported');

    if (webWorkersSupportedElement) {
        webWorkersSupportedElement.innerText = (webWorkersSupported ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "webWorkersSupported" non trovato.');
    }
}