// Funzione per ottenere informazioni sulla connessione di rete
export function checkConnectionType() {
    const connectionType = navigator.connection ? navigator.connection.effectiveType : 'Sconosciuto';

    const connectionTypeElement = document.getElementById('connectionType');

    if (connectionTypeElement) {
        connectionTypeElement.innerText = connectionType;
    } else {
        console.error('Elemento con id "connectionType" non trovato.');
    }
}