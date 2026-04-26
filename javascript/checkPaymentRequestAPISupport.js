/**
 * Verifica il supporto alla Payment Request API.
 *
 * Modifiche:
 *  - Null check sull'elemento (evita TypeError se l'ID cambia).
 *  - Aggiunto controllo che la prototype chain abbia davvero `show` come funzione.
 */
export function checkPaymentRequestAPISupport() {
    const el = document.getElementById('paymentRequestAPISupported');
    if (!el) {
        console.warn('Elemento con id "paymentRequestAPISupported" non trovato.');
        return;
    }

    const supported =
        typeof PaymentRequest === 'function'
        && typeof PaymentRequest.prototype === 'object'
        && typeof PaymentRequest.prototype.show === 'function';

    el.innerText = supported ? 'Sì' : 'No';
}
