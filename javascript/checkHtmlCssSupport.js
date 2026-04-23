// Funzione per verificare la compatibilità con HTML5 e CSS3
export function checkHtmlCssSupport() {
    var el = document.getElementById('htmlCssSupport');
    if (!el) return;

    var features = [];
    var missing = [];

    // HTML5 APIs che variano tra browser
    if (window.SharedArrayBuffer) features.push('SharedArrayBuffer');
    else missing.push('SharedArrayBuffer');

    if (window.OffscreenCanvas) features.push('OffscreenCanvas');
    else missing.push('OffscreenCanvas');

    if (CSS.supports && CSS.supports('container-type', 'inline-size')) features.push('Container Queries');
    else missing.push('Container Queries');

    if (CSS.supports && CSS.supports('view-transition-name', 'none')) features.push('View Transitions');
    else missing.push('View Transitions');

    if ('scheduling' in navigator) features.push('Scheduling API');
    else missing.push('Scheduling API');

    if (missing.length === 0) {
        el.innerText = 'Completo (' + features.length + '/' + features.length + ' feature moderne)';
    } else {
        el.innerText = features.length + '/' + (features.length + missing.length) + ' feature moderne — manca: ' + missing.join(', ');
    }
}
