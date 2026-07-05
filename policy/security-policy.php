<?php
include("../config.php");
require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/_policy_render.php";

define('PP_LANG_INTERNAL', 1);
$POLICY = require __DIR__ . "/../lang/policy_data.php";

$lang = pp_lang_current();
$bundle = $POLICY[$lang] ?? $POLICY['en'];
$common = $bundle['common'];
$page = $bundle['security'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page['title_tag'], ENT_QUOTES, 'UTF-8') ?></title>
<link rel="stylesheet" href="/css/fonts/primary.css">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --bg:        #0d0d10;
    --ink:       #ece9e1;
    --ink-2:     #b9b4a8;
    --mut:       #87837a;
    --faint:     #5e5b54;
    --line:      rgba(255,255,255,0.11);
    --violet:    #9d86ff;
    --violet-2:  #b7a6ff;

    --purple:        #8b7cf8;
    --sidebar-bg:    #161616;
    --sidebar-border:#2a2a2a;
    --sidebar-text:  #c8c8c8;
    --sidebar-width: 280px;

    --disp: 'Fraunces', Georgia, 'Times New Roman', serif;
    --sans: 'DM Sans', system-ui, -apple-system, sans-serif;
}

html { height: 100%; }
body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--ink);
    display: flex;
    height: 100vh;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}
a { color: inherit; }
::selection { background: var(--violet); color: #14121f; }
:focus-visible { outline: 2px solid var(--violet); outline-offset: 3px; }

#vanta-bg { position: fixed; inset: 0; z-index: 0; background: var(--bg); }
#scrim { position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background: linear-gradient(180deg, rgba(13,13,16,0.5) 0%, rgba(13,13,16,0.68) 55%, rgba(13,13,16,0.82) 100%); }

/* ─────────── SIDEBAR ─────────── */
.sidebar { width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border); z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans); }
.sidebar-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: -0.02em; }
.logo img { width: 35px; height: 35px; object-fit: contain; }
.nav-section { margin-bottom: .5rem; }
.nav-item { display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 10px; text-decoration: none; color: var(--sidebar-text); font-size: .9rem; font-weight: 400; transition: background .18s, color .18s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(139,124,248,.15); color: var(--purple); font-weight: 500; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .8; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper { flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden; background: transparent; color: var(--ink); position: relative; z-index: 1; scroll-behavior: smooth; }

.page-header { max-width: 800px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(.5rem, 2vw, 1rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.2rem, 5vw, 3.4rem); line-height: 1.04; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 0; }

/* ─────────── CONTENUTO LEGALE ─────────── */
.legal-content { max-width: 800px; margin: 0 auto; padding: 2rem clamp(1.5rem, 5vw, 3rem) 5rem; }
.legal-content h2 { font-family: var(--disp); font-weight: 500; font-size: clamp(1.4rem, 3vw, 1.9rem); color: var(--ink); margin: 2.5rem 0 .8rem; letter-spacing: -.015em; line-height: 1.2; }
.legal-content h3 { font-family: var(--disp); font-weight: 500; font-size: 1.15rem; color: var(--ink); margin: 1.6rem 0 .5rem; }
.legal-content p, .legal-content li { color: var(--ink-2); font-size: .96rem; line-height: 1.8; margin-bottom: .6rem; }
.legal-content ul, .legal-content ol { padding-left: 1.4rem; margin-bottom: 1rem; }
.legal-content li::marker { color: var(--violet); }
.legal-content .updated { color: var(--mut); font-size: .82rem; margin-bottom: 2rem; }
.legal-content a { color: var(--violet-2); text-decoration: underline; text-underline-offset: 2px; }
.legal-content a:hover { color: var(--violet); }
.legal-content strong { color: var(--ink); font-weight: 600; }
.legal-content hr { border: none; height: 1px; background: var(--line); margin: 2rem 0; }
.legal-content .note { background: rgba(157,134,255,0.06); border-left: 3px solid var(--violet); border-radius: 0; padding: .9rem 1.1rem; font-size: .9rem; color: var(--ink-2); line-height: 1.7; margin: 1.5rem 0; }

/* ─────────── RESPONSIVE ─────────── */
@media (max-width: 768px) {
    html, body { height: 100%; overflow: hidden; }
    .sidebar { position: fixed; top: 0; left: 0; right: 0; width: 100%; height: auto; min-width: 0; border-right: none; border-bottom: 1px solid var(--sidebar-border); padding: 0; flex-direction: column; overflow: hidden; z-index: 200; }
    .sidebar-top { margin-bottom: 0; padding: 0 1.2rem; width: 100%; height: 56px; flex-shrink: 0; }
    .hamburger { display: flex; }
    .nav-section, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .legal-content { padding: 1.5rem 1.5rem 4rem; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="../home.php" class="logo"><img src="../images/PatchPulseLogo.svg" alt="PatchPulse">PatchPulse</a>
        <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button>
    </div>
    <div class="nav-section">
        <a href="../home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?></a>
    </div>
    <div class="sidebar-bottom">
        <a href="../settings.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?></a>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="../home.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> <?= htmlspecialchars($common['back'], ENT_QUOTES, 'UTF-8') ?></a>
        <p class="page-header-eyebrow"><?= htmlspecialchars($page['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="page-header-title"><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div class="legal-content">
        <?php pp_policy_render_blocks($page['blocks']); ?>
        <div class="note">
            <strong>Hall of Fame</strong> —
            <?= $lang === 'it'
                ? 'ringraziamo chi ci aiuta a migliorare la sicurezza con segnalazioni responsabili. '
                : 'we thank those who help us improve security through responsible disclosure. ' ?>
            <a href="../hall-of-fame.php"><?= $lang === 'it' ? 'Vai alla Hall of Fame &rarr;' : 'Visit the Hall of Fame &rarr;' ?></a>
        </div>
    </div>
</main>

<?php pp_lang_emit_js(); ?>
<script src="../script.js"></script>
<script src="../js/three.min.js"></script>
<script src="../js/vanta.waves.min.js"></script>
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce && window.VANTA && window.VANTA.WAVES) {
        window.VANTA.WAVES({
            el: '#vanta-bg',
            mouseControls: true, touchControls: true, gyroControls: false,
            minHeight: 200.0, minWidth: 200.0, scale: 1.0, scaleMobile: 1.0,
            color: 0x14132a, shininess: 22.0, waveHeight: 13.0, waveSpeed: 0.72, zoom: 0.95
        });
    }
})();
</script>
</body>
</html>
