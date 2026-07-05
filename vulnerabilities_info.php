<?php
include("config.php");
require_once __DIR__ . "/lang/lang.php";

define('PP_LANG_INTERNAL', 1);
$VULN_DATA = require __DIR__ . "/lang/vulnerabilities_data.php";

$lang = pp_lang_current();
$data = $VULN_DATA[$lang] ?? $VULN_DATA['en'];
$page = $data['page'];

// Whitelist of risk levels → CSS class. Anything outside falls back to medium.
$riskClass = ['low' => 'risk-low', 'medium' => 'risk-medium', 'high' => 'risk-high'];
$riskLabel = [
    'low'    => $page['risk_low'],
    'medium' => $page['risk_medium'],
    'high'   => $page['risk_high'],
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('vi.title_tag') ?></title>
<link rel="stylesheet" href="/css/fonts/primary.css">
<style>
/* ════════════════════════════════════════════════════════════════
   PATCHPULSE — INFO VULNERABILITÀ (skin scuro, coerente con la home).
   Struttura funzionale invariata: skin soltanto.
   ════════════════════════════════════════════════════════════════ */
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
    --ok:        #5fd6a3;
    --amber:     #e3b457;
    --red:       #e8736b;

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
    background: linear-gradient(180deg, rgba(13,13,16,0.46) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

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
.nav-item.active .nav-icon { opacity: 1; }
.nav-sub-icon { width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .5; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper { flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden; background: transparent; color: var(--ink); position: relative; z-index: 1; scroll-behavior: smooth; }

.page-header { max-width: 900px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.2rem, 3vw, 2rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 1rem; }
.page-header-desc { font-family: var(--disp); font-weight: 400; font-size: clamp(1.05rem, 1.8vw, 1.3rem); line-height: 1.5; color: var(--ink-2); max-width: 64ch; }

/* ─────────── CONTENUTO ─────────── */
.vuln-content { max-width: 900px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }
.category-nav { display: flex; justify-content: center; gap: .6rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
.category-btn { padding: .55rem 1.2rem; background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; font-family: var(--sans); font-size: .88rem; font-weight: 500; color: var(--mut); cursor: pointer; transition: border-color .2s, color .2s, background .2s; text-decoration: none; }
.category-btn:hover, .category-btn.active { border-color: var(--violet); color: var(--violet); background: rgba(157,134,255,0.08); }

.info-card { background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; padding: 1.8rem; margin-bottom: 1.2rem; transition: border-color .2s; scroll-margin-top: 1.5rem; }
.info-card:hover { border-color: rgba(157,134,255,0.3); }
.info-card h2 { font-family: var(--disp); color: var(--ink); font-size: clamp(1.3rem, 2.6vw, 1.6rem); font-weight: 500; margin-bottom: .9rem; display: flex; align-items: center; gap: .7rem; flex-wrap: wrap; letter-spacing: -.012em; }
.info-card h3 { font-family: var(--disp); color: var(--ink); font-size: 1.12rem; font-weight: 500; margin: 1.3rem 0 .5rem; }
.info-card p { color: var(--ink-2); font-size: .95rem; line-height: 1.7; margin-bottom: .8rem; }
.info-card ul { color: var(--ink-2); margin-left: 1.2rem; margin-bottom: .8rem; font-size: .92rem; }
.info-card li { margin-bottom: .4rem; line-height: 1.6; }
.info-card li::marker { color: var(--violet); }

.risk-level { padding: .22rem .65rem; border-radius: 0; font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
.risk-low { background: rgba(95,214,163,0.12); color: var(--ok); }
.risk-medium { background: rgba(227,180,87,0.12); color: var(--amber); }
.risk-high { background: rgba(232,115,107,0.12); color: var(--red); }

.protection-tip { background: rgba(157,134,255,0.06); border-left: 3px solid var(--violet); border-radius: 0; padding: .9rem 1.1rem; margin: 1rem 0 0; font-size: .92rem; color: var(--ink-2); line-height: 1.65; }
.protection-tip strong { color: var(--violet-2); }

/* footer */
.foot { max-width: 900px; margin: 0 auto; border-top: 1px solid var(--line); padding: 1.5rem clamp(1.5rem, 5vw, 3rem) 3rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
.foot .copy { font-size: .78rem; color: var(--faint); }
.foot .legal { display: flex; flex-wrap: wrap; gap: .5rem 1.3rem; }
.foot a { font-size: .78rem; color: var(--mut); text-decoration: none; transition: color .2s; }
.foot a:hover { color: var(--violet); }

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
    .category-nav { flex-direction: column; align-items: center; }
    .category-btn { width: 100%; max-width: 280px; text-align: center; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo"><img src="images/PatchPulseLogo.svg" alt="PatchPulse">PatchPulse</a>
        <div style="display:flex;align-items:center;gap:0.5rem"><button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button></div>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?></a>
        <a href="home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><?= t('nav.applications') ?></a>
        <a href="browser-scan.php" class="nav-item active"><span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.browser_scanner') ?></a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="account.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><?= t('nav.account') ?></a><?php else: ?><a href="log-reg.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span><?= t('nav.login') ?></a><?php endif; ?>
        <a href="settings.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?></a>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="browser-scan.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> <?= htmlspecialchars($page['back'], ENT_QUOTES, 'UTF-8') ?></a>
        <p class="page-header-eyebrow"><?= htmlspecialchars($page['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="page-header-title"><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="page-header-desc"><?= htmlspecialchars($page['desc'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="vuln-content">
        <div class="category-nav">
            <a href="#web-tracking" class="category-btn"><?= htmlspecialchars($page['cat_tracking'], ENT_QUOTES, 'UTF-8') ?></a>
            <a href="#functionality" class="category-btn"><?= htmlspecialchars($page['cat_features'], ENT_QUOTES, 'UTF-8') ?></a>
            <a href="#device-info" class="category-btn"><?= htmlspecialchars($page['cat_device'], ENT_QUOTES, 'UTF-8') ?></a>
        </div>

        <?php foreach ($data['sections'] as $sectionId => $cards): ?>
            <section id="<?= htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($cards as $card): ?>
                    <?php
                    $risk = is_string($card['risk'] ?? null) ? $card['risk'] : 'medium';
                    $rClass = $riskClass[$risk] ?? 'risk-medium';
                    $rLabel = $riskLabel[$risk] ?? $riskLabel['medium'];
                    ?>
                    <div id="<?= htmlspecialchars($card['id'], ENT_QUOTES, 'UTF-8') ?>" class="info-card">
                        <h2>
                            <?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>
                            <span class="risk-level <?= htmlspecialchars($rClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </h2>
                        <?php foreach ($card['sections'] ?? [] as $block): ?>
                            <?php if (!empty($block['heading'])): ?>
                                <h3><?= htmlspecialchars($block['heading'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($block['intro'])): ?>
                                <p><?= htmlspecialchars($block['intro'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <?php if (!empty($block['items']) && is_array($block['items'])): ?>
                                <ul>
                                    <?php foreach ($block['items'] as $item): ?>
                                        <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!empty($card['tip'])): ?>
                            <div class="protection-tip">
                                <strong><?= htmlspecialchars($card['tip']['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?= htmlspecialchars($card['tip']['body'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <footer class="foot" id="contatti">
        <span class="copy">&copy; <?= date('Y') ?> PatchPulse</span>
        <span class="legal">
            <a href="policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a>
            <a href="policy/terms&amp;condition.php" target="_blank"><?= t('footer.terms') ?></a>
            <a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
        </span>
    </footer>
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
