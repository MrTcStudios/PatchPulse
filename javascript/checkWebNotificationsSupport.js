/**
 * Stato delle Web Notifications.
 *
 * Modifica importante: il check originale dichiarava "Sì" anche quando l'API
 * non era affatto disponibile (es. iOS Safari < 16.4) → falso positivo.
 * Ora distinguiamo tre stati: non supportato / negato / disponibile.
 */
import { T } from '../lang/t.js';

export function checkWebNotificationsSupport() {
    const el = document.getElementById('webNotificationsSupported');
    if (!el) {
        console.warn('Elemento con id "webNotificationsSupported" non trovato.');
        return;
    }

    if (typeof window === 'undefined' || !('Notification' in window)) {
        el.textContent = T('js.bs.notif.unsupported');
        return;
    }

    switch (Notification.permission) {
        case 'granted':
            el.textContent = T('js.bs.notif.granted');
            break;
        case 'denied':
            el.textContent = T('js.bs.notif.denied');
            break;
        case 'default':
        default:
            el.textContent = T('js.bs.notif.default');
    }
}
