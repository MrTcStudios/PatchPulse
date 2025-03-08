// Funzione per ottenere il Color Depth
export function getColorDepth() {
    const colorDepth = screen.colorDepth || screen.pixelDepth;
    const colorDepthElement = document.getElementById('colorDepth');

    if (colorDepthElement) {
        colorDepthElement.innerText = colorDepth;
    } else {
        console.error('Elemento con id "colorDepth" non trovato.');
    }
}