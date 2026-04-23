export function updateDimensions() {
    const wEl = document.getElementById('width');
    const hEl = document.getElementById('height');
    const displayEl = document.getElementById('screenResolutionDisplay');
    if (wEl) wEl.textContent = screen.width;
    if (hEl) hEl.textContent = screen.height;
    if (displayEl) displayEl.textContent = screen.width + ' x ' + screen.height;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateDimensions);
} else {
    updateDimensions();
}

window.addEventListener('resize', updateDimensions);
