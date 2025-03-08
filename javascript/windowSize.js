// Funzione per aggiornare le dimensioni della finestra
export function updateDimensions() {
    const width = window.innerWidth;
    const height = window.innerHeight;
    document.getElementById('width').textContent = width;
    document.getElementById('height').textContent = height;
}

// Aggiorna le dimensioni iniziali
updateDimensions();

// Listener per aggiornare le dimensioni quando la finestra viene ridimensionata
window.addEventListener('resize', updateDimensions);