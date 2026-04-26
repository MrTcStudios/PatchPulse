/**
 * Rileva sistema operativo + versione + architettura.
 * Bug corretti rispetto alla versione precedente:
 *  - L'architettura per Windows ritornava sempre '64-bit' (ternario tautologico).
 *  - Lo "stimato" basato sulla risoluzione era arbitrario.
 *
 * Ora:
 *  - Si dichiara "64-bit" SOLO quando ci sono indicatori chiari nell'UA.
 *  - Si usa userAgentData.getHighEntropyValues quando disponibile (più affidabile).
 *  - Quando incerto, si dichiara "Sconosciuta" invece di indovinare.
 */
export async function checkOSVersion(elementId = 'osVersion') {
    const el = document.getElementById(elementId);
    if (!el) {
        console.warn(`Elemento con id "${elementId}" non trovato.`);
        return;
    }

    const ua = navigator.userAgent || '';
    const platform = navigator.platform || '';

    /**
     * Architettura: solo segnali concreti, niente "stima".
     */
    const detectArch = (uaStr, platStr) => {
        const sig64 = /\b(?:Win64|WOW64|x86_64|x64|amd64|arm64|aarch64)\b/i;
        if (sig64.test(uaStr) || sig64.test(platStr)) return '64-bit';
        // Solo i Mac Intel storici e Linux i686 sono 32-bit espliciti
        if (/i[3-6]86/.test(uaStr) || /Linux i\d86/i.test(platStr)) return '32-bit';
        // Win32 nell'UA non significa "32-bit": è semplicemente l'API Windows.
        // Quindi non concludiamo niente se non abbiamo indicatori 64-bit.
        return null;
    };

    const parseFromUA = (uaStr) => {
        let name, version;

        // Windows
        const winMatch = uaStr.match(/Windows NT ([0-9._]+)/i);
        if (winMatch) {
            name = 'Windows';
            const nt = winMatch[1].replace(/_/g, '.');
            const map = {
                '10.0': '10/11', '6.3': '8.1', '6.2': '8',
                '6.1': '7', '6.0': 'Vista', '5.2': 'XP x64',
                '5.1': 'XP', '5.0': '2000'
            };
            version = map[nt] || `NT ${nt}`;
        }

        // macOS
        const macMatch = uaStr.match(/Mac OS X ([0-9_\.]+)/i);
        if (macMatch) {
            name = 'macOS';
            version = macMatch[1].replace(/_/g, '.');
        }

        // iOS / iPadOS
        const iosMatch = uaStr.match(/(iPhone|iPad|iPod).*?OS\s([0-9_]+)/i);
        if (iosMatch) {
            name = iosMatch[1] === 'iPad' ? 'iPadOS' : 'iOS';
            version = iosMatch[2].replace(/_/g, '.');
        }

        // Android
        const androidMatch = uaStr.match(/Android\s+([0-9\.]+)/i);
        if (androidMatch) {
            name = 'Android';
            version = androidMatch[1];
        }

        // Chrome OS
        if (/CrOS/i.test(uaStr)) {
            name = 'Chrome OS';
            const m = uaStr.match(/CrOS [^\s]+ ([0-9.]+)/);
            if (m) version = m[1];
        }

        // Linux generico (solo se non già identificato)
        if (!name && /Linux/i.test(uaStr)) {
            name = 'Linux';
            const distro = uaStr.match(/(Ubuntu|Debian|Fedora|CentOS|RedHat|SUSE)/i);
            if (distro) name = `Linux (${distro[1]})`;
        }

        // Fallback su platform
        if (!name) {
            const map = {
                'Win32': 'Windows', 'Win64': 'Windows',
                'MacIntel': 'macOS', 'MacPPC': 'macOS (PowerPC)',
                'Linux i686': 'Linux', 'Linux x86_64': 'Linux',
                'iPhone': 'iOS', 'iPad': 'iPadOS'
            };
            name = map[platform] || platform || 'Sconosciuto';
        }

        const arch = detectArch(uaStr, platform);
        return { name, version, arch };
    };

    const formatOutput = ({ name, version, arch }) => {
        let out = name || 'Sistema sconosciuto';
        if (version) out += ` ${version}`;
        if (arch)    out += ` (${arch})`;
        return out;
    };

    // 1) Risultato sincrono dall'UA
    const initial = parseFromUA(ua);
    el.textContent = formatOutput(initial);

    // 2) Affina con Client Hints (richiede HTTPS / Secure Context)
    if (
        navigator.userAgentData &&
        typeof navigator.userAgentData.getHighEntropyValues === 'function'
    ) {
        try {
            const high = await navigator.userAgentData.getHighEntropyValues([
                'platform', 'platformVersion', 'architecture', 'bitness'
            ]);

            let name = high.platform || initial.name;
            let version = high.platformVersion || initial.version;
            let arch = initial.arch;

            if (high.bitness === '64' || high.bitness === '32') {
                arch = `${high.bitness}-bit`;
            } else if (high.architecture) {
                if (/64/.test(high.architecture)) arch = '64-bit';
                else if (/86|32/.test(high.architecture)) arch = '32-bit';
            }

            // Distinzione Win10/Win11 via build number (richiede platformVersion)
            if (name === 'Windows' && high.platformVersion) {
                const parts = high.platformVersion.split('.');
                const major = parseInt(parts[0], 10);
                if (Number.isFinite(major)) {
                    // Da platformVersion: major ≥ 13 = Win11, altrimenti Win10
                    // (cfr. https://learn.microsoft.com/microsoft-edge/web-platform/how-to-detect-win11)
                    version = major >= 13 ? '11' : '10';
                }
            }

            el.textContent = formatOutput({ name, version, arch });
        } catch (err) {
            console.debug('userAgentData non disponibile:', err && err.message);
        }
    }
}
