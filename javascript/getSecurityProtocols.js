// Funzione per ottenere dettagli sui protocolli di sicurezza
export function getSecurityProtocols() {
    const securityProtocols = "TLS 1.2, TLS 1.3";
    const securityProtocolsElement = document.getElementById('securityProtocols');

    if (securityProtocolsElement) {
        securityProtocolsElement.innerText = securityProtocols;
    } else {
        console.error('Elemento con id "securityProtocols" non trovato.');
    }
}