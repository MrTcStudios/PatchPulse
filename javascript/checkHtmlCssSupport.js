// Funzione per verificare la compatibilità con HTML5 e CSS3
export function checkHtmlCssSupport() {
    const html5Supported = 'querySelector' in document && 'localStorage' in window && 'addEventListener' in window;
    const css3Supported = 'flexBasis' in document.documentElement.style || 'webkitFlexBasis' in document.documentElement.style ||
        'msFlexBasis' in document.documentElement.style || 'flexDirection' in document.documentElement.style;
    const htmlCssSupportElement = document.getElementById('htmlCssSupport');

    if (htmlCssSupportElement) {
        htmlCssSupportElement.innerText = html5Supported && css3Supported ? 'Supportato' : 'Non supportato';
    } else {
        console.error('Elemento con id "htmlCssSupport" non trovato.');
    }
}