export function getPublicIpAddresses() {
    document.addEventListener('DOMContentLoaded', () => {
        fetch('https://httpbin.org/anything')
            .then(response => response.json())
            .then(data => {
                console.log(data); // Per debug
                const ipv4Address = data.origin || 'N/D';
                const ipv6Address = extractIpv6Address(data.origin);

                const ipv4Element = document.getElementById('publicIpv4');
                const ipv6Element = document.getElementById('publicIpv6');

                if (ipv4Element) {
                    ipv4Element.innerText = ipv4Address;
                } else {
                    console.warn("Elemento con ID 'publicIpv4' non trovato.");
                }

                if (ipv6Element) {
                    ipv6Element.innerText = ipv6Address || 'N/D';
                } else {
                    console.warn("Elemento con ID 'publicIpv6' non trovato.");
                }
            })
            .catch(() => {
                console.error('Errore nel recupero degli indirizzi IP pubblici.');
                const ipv4Element = document.getElementById('publicIpv4');
                const ipv6Element = document.getElementById('publicIpv6');

                if (ipv4Element) ipv4Element.innerText = 'N/D';
                if (ipv6Element) ipv6Element.innerText = 'N/D';
            });
    });
}

export function extractIpv6Address(origin) {
    const ipv6Regex = /\[([a-fA-F0-9:]+)\]/;
    const match = origin.match(ipv6Regex);
    return match ? match[1] : null;
}
