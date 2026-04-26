/**
 * Stato delle Web Notifications.
 *
 * Modifica importante: il check originale dichiarava "Sì" anche quando l'API
 * non era affatto disponibile (es. iOS Safari < 16.4) → falso positivo.
 * Ora distinguiamo tre stati: non supportato / negato / disponibile.
 */
export function checkWebNotificationsSupport() {
    const el = document.getElementById('webNotificationsSupported');
    if (!el) {
        console.warn('Elemento con id "webNotificationsSupported" non trovato.');
        return;
    }

    if (typeof window === 'undefined' || !('Notification' in window)) {
        el.textContent = 'Non supportate';
        return;
    }

    switch (Notification.permission) {
        case 'granted':
            el.textContent = 'Sì (consenso concesso)';
            break;
        case 'denied':
            el.textContent = 'No (consenso negato)';
            break;
        case 'default':
        default:
            el.textContent = 'Sì (richiede consenso)';
    }
}
