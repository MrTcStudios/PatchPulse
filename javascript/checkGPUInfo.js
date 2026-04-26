/**
 * Recupera informazioni sulla GPU tramite WebGL.
 *
 * Strategia:
 *  - Tenta `WEBGL_debug_renderer_info` (UNMASKED_*) che dà il valore reale,
 *    ma solo nei browser dove è ancora supportato (Chromium con flag).
 *  - Se non disponibile o deprecato (Firefox ≥ 88, Safari), usa
 *    `gl.getParameter(gl.RENDERER)` / `gl.getParameter(gl.VENDOR)` —
 *    valori "mascherati" decisi dal browser per ridurre il fingerprinting.
 *
 * Modifiche:
 *  - Niente più `WEBGL_lose_context.loseContext()`: provocava warning
 *    "WebGL context was lost" nei DevTools. Il canvas viene comunque GC.
 */
export function checkGPUInfo() {
    const el = document.getElementById('gpuName');
    if (!el) {
        console.warn('Elemento con id "gpuName" non trovato.');
        return;
    }

    let canvas, gl;
    try {
        canvas = document.createElement('canvas');
        // Non vogliamo il warning di context lost
        canvas.addEventListener('webglcontextlost', (e) => e.preventDefault(), { once: true });
        gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    } catch (_) {
        el.innerText = 'WebGL non disponibile';
        return;
    }

    if (!gl) {
        el.innerText = 'WebGL non disponibile';
        return;
    }

    try {
        let vendor = '';
        let renderer = '';
        let masked = true;

        // 1) Tenta UNMASKED_* (deprecato in Firefox)
        const ext = gl.getExtension('WEBGL_debug_renderer_info');
        if (ext) {
            try {
                vendor   = String(gl.getParameter(ext.UNMASKED_VENDOR_WEBGL)   || '').trim();
                renderer = String(gl.getParameter(ext.UNMASKED_RENDERER_WEBGL) || '').trim();
                masked = false;
            } catch (_) { /* ignora, fallback sotto */ }
        }

        // 2) Fallback sui parametri standard (mascherati ma sempre disponibili)
        if (!vendor)   vendor   = String(gl.getParameter(gl.VENDOR)   || '').trim();
        if (!renderer) renderer = String(gl.getParameter(gl.RENDERER) || '').trim();

        if (!vendor && !renderer) {
            el.innerText = 'Info GPU non disponibile';
            return;
        }

        const text = [vendor, renderer].filter(Boolean).join(' — ');
        el.innerText = masked ? `${text} (mascherato dal browser)` : text;
    } catch (err) {
        console.debug('checkGPUInfo error:', err && err.message);
        el.innerText = 'N/D';
    }
}
