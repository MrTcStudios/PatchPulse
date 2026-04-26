/**
 * Verifica il supporto a WebGL/WebGL2 e indica il rischio di fingerprinting.
 *
 * Modifiche:
 *  - Non chiama più `WEBGL_debug_renderer_info` direttamente (deprecato in Firefox);
 *    se l'estensione c'è viene usata, altrimenti si usa `gl.RENDERER`/`gl.VENDOR`.
 *  - Niente più `loseContext()` per evitare il warning "WebGL context was lost".
 */
export function checkWebGLFingerprinting() {
    const el = document.getElementById('webglFingerprinting');
    if (!el) {
        console.warn('Elemento con id "webglFingerprinting" non trovato.');
        return;
    }

    let canvas, gl1, gl2;
    try {
        canvas = document.createElement('canvas');
        canvas.addEventListener('webglcontextlost', (e) => e.preventDefault(), { once: true });
        gl1 = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        gl2 = canvas.getContext('webgl2');
    } catch (_) {
        el.innerText = 'WebGL non disponibile';
        return;
    }

    const supportsWebGL  = !!gl1;
    const supportsWebGL2 = !!gl2;

    let masked = true;
    if (gl1) {
        try {
            const ext = gl1.getExtension('WEBGL_debug_renderer_info');
            if (ext) {
                // Se l'estensione c'è e ritorna stringhe non vuote, NON è mascherato
                const v = String(gl1.getParameter(ext.UNMASKED_VENDOR_WEBGL)   || '').trim();
                const r = String(gl1.getParameter(ext.UNMASKED_RENDERER_WEBGL) || '').trim();
                masked = !(v && r);
            } else {
                masked = true;
            }
        } catch (_) {
            masked = true;
        }
    }

    let txt = `WebGL: ${supportsWebGL ? 'Sì' : 'No'}, WebGL2: ${supportsWebGL2 ? 'Sì' : 'No'}`;
    if (supportsWebGL) {
        txt += masked
            ? ' — info GPU mascherate (basso rischio)'
            : ' — info GPU esposte (rischio fingerprinting)';
    }
    el.innerText = txt;
}
