/**
 * Recupera l'IP pubblico del client tramite endpoint same-origin /API/client-ip.php.
 *
 * Migliorie:
 *  - Verifica esplicita del Content-Type prima di tentare il parse JSON
 *    (se l'endpoint non c'è ancora deployato il server risponde HTML 404
 *    e prima crashava con "JSON.parse: unexpected character").
 *  - Same-origin → niente esposizione/leak a server esterni.
 */
export function getPublicIpAddresses() {
    const update = (ipv4, ipv6) => {
        const v4El = document.getElementById('publicIpv4');
        const v6El = document.getElementById('publicIpv6');
        if (v4El) v4El.innerText = ipv4 || 'N/D';
        if (v6El) v6El.innerText = ipv6 || 'N/D';
    };

    const run = () => {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        fetch('API/client-ip.php', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            signal: controller.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(async (res) => {
                clearTimeout(timeoutId);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                // Se il server non restituisce JSON, evita JSON.parse crash
                const ct = res.headers.get('Content-Type') || '';
                if (!ct.includes('application/json')) {
                    throw new Error('Risposta non JSON (' + ct + ')');
                }
                return res.json();
            })
            .then((data) => {
                if (!data || typeof data !== 'object') throw new Error('Risposta vuota');
                update(data.ipv4, data.ipv6);
            })
            .catch((err) => {
                clearTimeout(timeoutId);
                console.debug('getPublicIpAddresses error:', err && err.message);
                update(null, null);
            });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
        run();
    }
}

/**
 * Estrae un IPv6 da una stringa "[ipv6]" o ipv6 puro. Retro-compatibilità.
 */
export function extractIpv6Address(origin) {
    if (typeof origin !== 'string') return null;
    const bracket = origin.match(/\[([a-fA-F0-9:]+)\]/);
    if (bracket) return bracket[1];
    if (/^[a-fA-F0-9:]+$/.test(origin) && (origin.match(/:/g) || []).length >= 2) {
        return origin;
    }
    return null;
}
