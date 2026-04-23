export function checkPaymentRequestAPISupport() {
    const paymentRequestAPISupported = 'PaymentRequest' in window && 'show' in PaymentRequest.prototype;
    const paymentRequestAPISupportedElement = document.getElementById('paymentRequestAPISupported');

    if (paymentRequestAPISupportedElement) {
        paymentRequestAPISupportedElement.innerText = (paymentRequestAPISupported ? 'Sì' : 'No');
    } else {
        console.error('Elemento con id "paymentRequestAPISupported" non trovato.');
    }
}
