export function checkWebNotificationsSupport() {
    const el = document.getElementById('webNotificationsSupported');

    let denied = false;

    if (typeof window !== 'undefined' && 'Notification' in window) {
        denied = (Notification.permission === 'denied');
    }

    if (el) {
        el.textContent = denied ? 'No' : 'Sì';
    } else {
        console.error('Elemento con id "webNotificationsSupported" non trovato.');
    }
}
