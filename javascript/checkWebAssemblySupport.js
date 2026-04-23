export function checkWebAssemblySupport() {
    const webAssemblySupport = typeof WebAssembly === 'object' ? 'Sì' : 'No';
    const webAssemblySupportElement = document.getElementById('webAssemblySupport');

    if (webAssemblySupportElement) {
        webAssemblySupportElement.innerText = webAssemblySupport;
    } else {
        console.error('Elemento con id "webAssemblySupport" non trovato.');
    }
}
