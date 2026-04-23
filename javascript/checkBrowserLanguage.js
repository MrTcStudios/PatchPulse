 export function checkBrowserLanguage() {
    const browserLanguage = navigator.language || navigator.userLanguage;

    const browserLanguageElement = document.getElementById('browserLanguage');

    if (browserLanguageElement) {
        browserLanguageElement.innerText = browserLanguage;
    } else {
        console.error('Elemento con id "browserLanguage" non trovato.');
    }
}
