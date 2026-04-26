/**
 * Rileva se è attivo un AdBlocker tramite tecniche locali (no fetch esterni).
 * Usa SOLO bait DOM con classi/ID nelle filter list più diffuse (EasyList, uBO).
 * Riduce i falsi positivi: nessuna richiesta a googlesyndication o googleadservices,
 * che fallirebbero anche solo per problemi di rete o blocco firewall.
 *
 * @returns {Promise<boolean>} true se AdBlocker rilevato
 */
export function checkAdBlockEnabled() {
    return new Promise((resolve) => {
        // Crea bait con nomi che le filter list bloccano in modo molto consistente.
        const bait1 = document.createElement('div');
        bait1.className = 'ad-banner ad_unit ad-unit text-ad ad-placement pub_300x250';
        bait1.style.cssText = 'width:1px;height:1px;position:absolute;left:-9999px;top:-9999px;';
        bait1.innerHTML = '&nbsp;';

        const bait2 = document.createElement('div');
        bait2.id = 'ad-slot';
        bait2.className = 'adsbygoogle';
        bait2.style.cssText = 'width:1px;height:1px;position:absolute;left:-9999px;top:-9999px;';
        bait2.innerHTML = '&nbsp;';

        // Bait con tag <ins> tipico di AdSense — molti AdBlocker lo nascondono
        const bait3 = document.createElement('ins');
        bait3.className = 'adsbygoogle';
        bait3.style.cssText = 'display:block;width:1px;height:1px;position:absolute;left:-9999px;top:-9999px;';

        if (!document.body) {
            return resolve(false);
        }
        document.body.appendChild(bait1);
        document.body.appendChild(bait2);
        document.body.appendChild(bait3);

        const isHidden = (el) => {
            if (!el || !document.body.contains(el)) return true;
            const cs = window.getComputedStyle(el);
            // Un elemento è "nascosto" da un AdBlocker se ha dimensioni 0 o display:none/visibility:hidden
            return (
                el.offsetParent === null ||
                el.offsetHeight === 0 ||
                el.clientHeight === 0 ||
                cs.display === 'none' ||
                cs.visibility === 'hidden'
            );
        };

        // Servono 2 frame per dare al motore CSS dell'estensione il tempo di applicare le regole
        requestAnimationFrame(() => requestAnimationFrame(() => {
            setTimeout(() => {
                const blocked = [bait1, bait2, bait3].filter(isHidden).length;

                // Soglia: almeno 2 dei 3 bait nascosti per considerarlo bloccato.
                // Riduce i falsi positivi (es. fogli di stile del sito che nascondono accidentalmente un bait).
                const adBlockDetected = blocked >= 2;

                // Cleanup
                [bait1, bait2, bait3].forEach(el => {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                });

                const out = document.getElementById('adBlockEnabled');
                if (out) {
                    out.innerText = adBlockDetected ? 'Sì' : 'No';
                    out.dataset.detected = String(adBlockDetected);
                } else {
                    console.warn('Elemento con id "adBlockEnabled" non trovato.');
                }

                resolve(adBlockDetected);
            }, 150);
        }));
    });
}

/**
 * Wrapper retro-compatibile.
 * Non aggiunge una seconda detection: la prima è già robusta.
 */
export function detectAdBlock() {
    return checkAdBlockEnabled();
}
