<?php
include("config.php");
require_once __DIR__ . "/lang/lang.php";
$currentLang = pp_lang_current();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('hof.title_tag') ?></title>
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
    --mono: ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace;
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

.page-header { max-width: 700px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(.5rem, 2vw, 1rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 0; }

/* ─────────── HALL OF FAME ─────────── */
.hof-content { max-width: 700px; margin: 0 auto; padding: 1.5rem clamp(1.5rem, 5vw, 3rem) 5rem; }
.hof-intro { color: var(--ink-2); font-size: 1rem; line-height: 1.75; margin-bottom: 2.5rem; max-width: 60ch; }
.hof-intro a { color: var(--violet-2); text-decoration: underline; text-underline-offset: 2px; }
.hof-intro a:hover { color: var(--violet); }
.hof-empty { text-align: center; padding: 3rem 2rem; background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; }
.hof-empty-icon { margin-bottom: 1rem; color: var(--violet); line-height: 0; }
.hof-empty-icon svg { width: 44px; height: 44px; }
.hof-empty p { color: var(--mut); font-size: .98rem; line-height: 1.7; }
.hof-empty a { color: var(--violet-2); text-decoration: underline; text-underline-offset: 2px; }
.hof-empty a:hover { color: var(--violet); }
.hof-entry { display: flex; align-items: center; gap: 1rem; padding: 1.2rem 1.4rem; background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; margin-bottom: .8rem; transition: border-color .2s; }
.hof-entry:hover { border-color: rgba(157,134,255,0.4); }
.hof-rank { width: 36px; height: 36px; background: var(--violet); color: #14121f; border-radius: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
.hof-name { font-family: var(--disp); font-weight: 500; color: var(--ink); font-size: 1.05rem; }
.hof-detail { font-size: .85rem; color: var(--mut); }
.hof-date { margin-left: auto; font-family: var(--mono); font-size: .78rem; color: var(--faint); flex-shrink: 0; white-space: nowrap; }

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
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo"><img src="images/PatchPulseLogo.svg" alt="PatchPulse">PatchPulse</a>
        <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?></a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="account.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><?= t('nav.account') ?></a><?php else: ?><a href="log-reg.php#login" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span><?= t('nav.login') ?></a><?php endif; ?>
        <a href="settings.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?></a>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="home.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> <?= t('nav.back_home') ?></a>
        <p class="page-header-eyebrow"><?= t('hof.eyebrow') ?></p>
        <h1 class="page-header-title"><?= t('hof.title') ?></h1>
    </div>
    <div class="hof-content">
        <p class="hof-intro"><?= t('hof.intro_pre') ?> <a href="policy/security-policy.php"><?= t('hof.intro_link') ?></a>.</p>

        <!-- Quando qualcuno segnala una vulnerabilità, aggiungi un blocco così:
        <div class="hof-entry">
            <div class="hof-rank">1</div>
            <div>
                <div class="hof-name">Nome Ricercatore</div>
                <div class="hof-detail">Tipo di vulnerabilità trovata</div>
            </div>
            <span class="hof-date">Apr 2026</span>
        </div>
        -->

        <div class="hof-empty">
            <div class="hof-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
            <p><?= t('hof.empty') ?><br><?= t('hof.empty_cta_pre') ?> <a href="policy/security-policy.php"><?= t('hof.empty_cta_link') ?></a>.</p>
        </div>
    </div>
</main>
<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
<script src="js/three.min.js"></script>
<script src="js/vanta.waves.min.js"></script>
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
