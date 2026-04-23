export async function checkOSVersion(elementId = 'osVersion') {
    const el = document.getElementById(elementId);
    if (!el) {
        console.warn(`Elemento con id "${elementId}" non trovato.`);
        return;
    }

    const ua = navigator.userAgent || '';
    const platform = navigator.platform || '';

    const getArchitecture = (userAgent, platformString) => {
        if (/\b(?:Win64|WOW64|x86_64|x64|amd64|arm64|aarch64)\b/i.test(userAgent)) {
            return '64-bit';
        }
        
        if (/\b(?:Win64|x86_64|x64|amd64|arm64|aarch64)\b/i.test(platformString)) {
            return '64-bit';
        }
        
        if (/Win32/i.test(platformString)) {
            if (/Windows NT.*(?:Win64|WOW64)/i.test(userAgent)) {
                return '64-bit';
            }
            if (/Windows NT (?:6\.[2-9]|[1-9][0-9]\.)/i.test(userAgent)) {
                return navigator.maxTouchPoints > 0 ? '64-bit' : '64-bit';
            }
        }
        
        if (/Linux.*x86_64/i.test(userAgent) || /Linux.*amd64/i.test(userAgent)) {
            return '64-bit';
        }
        
        return '32-bit';
    };

    const parseFromUA = (uaString) => {
        let name = undefined;
        let version = undefined;

        const winMatch = uaString.match(/Windows NT ([0-9._]+)/i);
        if (winMatch) {
            name = 'Windows';
            const ntVersion = winMatch[1].replace(/_/g, '.');
            
            const windowsVersions = {
                '10.0': '11/10',
                '6.3': '8.1',
                '6.2': '8',
                '6.1': '7',
                '6.0': 'Vista',
                '5.2': 'XP x64',
                '5.1': 'XP',
                '5.0': '2000'
            };
            
            version = windowsVersions[ntVersion] || `NT ${ntVersion}`;
            
            if (ntVersion === '10.0') {
                const buildMatch = uaString.match(/Windows NT 10\.0.*?(\d{5,})/);
                if (buildMatch) {
                    const build = parseInt(buildMatch[1]);
                    if (build >= 22000) {
                        version = '11';
                    } else {
                        version = '10';
                    }
                } else {
                    version = '10/11';
                }
            }
        }

        const macMatch = uaString.match(/Mac OS X ([0-9_\.]+)/i);
        if (macMatch) {
            name = 'macOS';
            version = macMatch[1].replace(/_/g, '.');
        }

        const iosMatch = uaString.match(/(iPhone|iPad|iPod).*?(?:iPhone )?OS\s([0-9_]+)/i);
        if (iosMatch) {
            const device = iosMatch[1];
            name = device === 'iPad' ? 'iPadOS' : 'iOS';
            version = iosMatch[2].replace(/_/g, '.');
        }

        const androidMatch = uaString.match(/Android\s+([0-9\.]+)/i);
        if (androidMatch) {
            name = 'Android';
            version = androidMatch[1];
        }

        if (!name && /Linux/i.test(uaString)) {
            name = 'Linux';
            const distroMatch = uaString.match(/(Ubuntu|Debian|Fedora|CentOS|RedHat|SUSE)/i);
            if (distroMatch) {
                name = `Linux (${distroMatch[1]})`;
            }
        }

        if (/CrOS/i.test(uaString)) {
            name = 'Chrome OS';
            const crosMatch = uaString.match(/CrOS [^\s]+ ([0-9.]+)/);
            if (crosMatch) {
                version = crosMatch[1];
            }
        }

        if (!name) {
            const platformMap = {
                'Win32': 'Windows',
                'Win64': 'Windows',
                'MacIntel': 'macOS',
                'MacPPC': 'macOS (PowerPC)',
                'Linux i686': 'Linux',
                'Linux x86_64': 'Linux',
                'iPhone': 'iOS',
                'iPad': 'iPadOS'
            };
            
            name = platformMap[platform] || platform || 'Sconosciuto';
        }

        const arch = getArchitecture(uaString, platform);

        return { name, version, arch };
    };

    const formatOutput = ({ name, version, arch }) => {
        let result = name || 'Sistema Sconosciuto';
        if (version) {
            result += ` ${version}`;
        }
        if (arch) {
            result += ` (${arch})`;
        }
        return result;
    };

    const initial = parseFromUA(ua);
    el.textContent = formatOutput(initial);

    if (navigator.userAgentData && typeof navigator.userAgentData.getHighEntropyValues === 'function') {
        try {
            const hints = ['platform', 'platformVersion', 'architecture', 'bitness', 'model', 'uaFullVersion'];
            const high = await navigator.userAgentData.getHighEntropyValues(hints);
            
            let name = high.platform || initial.name;
            let version = high.platformVersion || initial.version;
            
            let arch = initial.arch;
            if (high.bitness) {
                arch = high.bitness === '64' ? '64-bit' : high.bitness === '32' ? '32-bit' : high.bitness + '-bit';
            } else if (high.architecture) {
                arch = /64/.test(high.architecture) ? '64-bit' : '32-bit';
            }

            if (name === 'Windows' && high.platformVersion) {
                const versionParts = high.platformVersion.split('.');
                if (versionParts.length >= 3) {
                    const build = parseInt(versionParts[2]);
                    if (build >= 22000) {
                        version = '11';
                    } else if (versionParts[0] === '10') {
                        version = '10';
                    }
                }
            }
            
            el.textContent = formatOutput({ name, version, arch });
            
        } catch (err) {
            console.info('userAgentData.getHighEntropyValues non disponibile:', err.message);
        }
    }

    if (initial.name === 'Windows' && initial.arch === '32-bit' && screen.width >= 1920) {
        console.info('Sistema Windows con risoluzione alta - probabile 64-bit');
        const updated = { ...initial, arch: '64-bit (stimato)' };
        el.textContent = formatOutput(updated);
    }
}
