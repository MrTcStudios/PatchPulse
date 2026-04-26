/**
 * Verifica il supporto a feature HTML/CSS moderne.
 *
 * Modifiche:
 *  - Etichetta migliorata: "feature moderne supportate", non "HTML5/CSS3"
 *    (HTML5/CSS3 sono ovunque ormai → l'etichetta induceva in errore).
 *  - Lista feature ampliata e più rappresentativa (script type=module, dialog,
 *    grid, flex gap, has() selector, container queries, view transitions...).
 *  - Nessun falso positivo dovuto a mancanza di window.CSS.supports.
 */
export function checkHtmlCssSupport() {
    const el = document.getElementById('htmlCssSupport');
    if (!el) {
        console.warn('Elemento con id "htmlCssSupport" non trovato.');
        return;
    }

    const supportsCss = (prop, val) => {
        try {
            return typeof CSS !== 'undefined'
                && typeof CSS.supports === 'function'
                && CSS.supports(prop, val);
        } catch (_) { return false; }
    };

    const checks = [
        ['Flexbox',          supportsCss('display', 'flex')],
        ['CSS Grid',         supportsCss('display', 'grid')],
        [':has() selector',  supportsCss('selector(:has(*))')],
        ['Container Queries',supportsCss('container-type', 'inline-size')],
        ['View Transitions', supportsCss('view-transition-name', 'none')],
        ['<dialog>',         typeof HTMLDialogElement !== 'undefined'],
        ['ES Modules',       'noModule' in HTMLScriptElement.prototype],
        ['OffscreenCanvas',  typeof OffscreenCanvas !== 'undefined'],
        ['SharedArrayBuffer',typeof SharedArrayBuffer !== 'undefined'],
    ];

    const supported = checks.filter(c => c[1]).map(c => c[0]);
    const missing   = checks.filter(c => !c[1]).map(c => c[0]);

    if (missing.length === 0) {
        el.innerText = `Tutte (${supported.length}/${checks.length} feature moderne)`;
    } else {
        el.innerText = `${supported.length}/${checks.length} feature moderne`;
        el.title = `Mancanti: ${missing.join(', ')}`;
    }
}
