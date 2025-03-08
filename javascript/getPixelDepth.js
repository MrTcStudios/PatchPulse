// Funzione per ottenere il Pixel Depth
export function getPixelDepth() {
    const pixelDepth = screen.pixelDepth;
    const pixelDepthElement = document.getElementById('pixelDepth');

    if (pixelDepthElement) {
        pixelDepthElement.innerText = pixelDepth;
    } else {
        console.error('Elemento con id "pixelDepth" non trovato.');
    }
}