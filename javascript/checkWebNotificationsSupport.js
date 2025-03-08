// Funzione per verificare il supporto alle Web Notifications
export function checkWebNotificationsSupport() {
    const webNotificationsSupported = 'Notification' in window;
    const webNotificationsSupportedElement = document.getElementById('webNotificationsSupported');

    if (webNotificationsSupportedElement) {
        webNotificationsSupportedElement.innerText = (webNotificationsSupported ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "webNotificationsSupported" non trovato.');
    }
}