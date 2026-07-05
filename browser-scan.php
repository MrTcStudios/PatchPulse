<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include 'config.php';
require_once __DIR__ . "/lang/lang.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('bs.title_tag') ?></title>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<link rel="stylesheet" href="/css/fonts/primary.css">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --bg:        #0d0d10;
    --ink:       #ece9e1;
    --ink-2:     #b4afa3;
    --mut:       #87837a;
    --faint:     #5e5b54;
    --line:      rgba(255,255,255,0.11);
    --violet:    #9d86ff;

    --purple:        #8b7cf8;
    --sidebar-bg:    #161616;
    --sidebar-border:#2a2a2a;
    --sidebar-text:  #c8c8c8;
    --sidebar-width: 280px;

    --disp: 'Fraunces', Georgia, 'Times New Roman', serif;
    --sans: 'DM Sans', system-ui, -apple-system, sans-serif;
    --mono: ui-monospace, 'SFMono-Regular', 'Cascadia Code', Menlo, Consolas, monospace;
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
    background: linear-gradient(180deg, rgba(13,13,16,0.45) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

/* ─────────── SIDEBAR — preservata dalla home attuale ─────────── */
.sidebar {
    width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh;
    background: var(--sidebar-bg); display: flex; flex-direction: column;
    padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border);
    z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans);
}
.sidebar-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: -0.02em; }
.logo img { width: 35px; height: 35px; object-fit: contain; }
.bell-btn { background: none; border: none; cursor: pointer; color: var(--sidebar-text); padding: 0.4rem; border-radius: 8px; transition: color .2s, background .2s; display: flex; align-items: center; }
.bell-btn:hover { color: #fff; background: rgba(255,255,255,.07); }
.search-bar { display: flex; align-items: center; background: #222; border: 1px solid #333; border-radius: 10px; padding: .55rem .8rem; margin-bottom: 1.8rem; gap: .5rem; transition: border-color .2s; }
.search-bar:focus-within { border-color: var(--purple); }
.search-bar input { background: none; border: none; outline: none; color: var(--sidebar-text); font-family: var(--sans); font-size: .9rem; flex: 1; min-width: 0; }
.search-bar input::placeholder { color: #666; }
.search-shortcut { background: #333; color: #888; font-size: .7rem; font-weight: 600; padding: .2rem .45rem; border-radius: 5px; letter-spacing: .05em; }
.nav-section { margin-bottom: .5rem; }
.nav-item { display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 10px; text-decoration: none; color: var(--sidebar-text); font-size: .9rem; font-weight: 400; transition: background .18s, color .18s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(139,124,248,.15); color: var(--purple); font-weight: 500; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .8; }
.nav-item.active .nav-icon { opacity: 1; }
.nav-sub-icon { width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .5; }
.nav-divider { height: 1px; background: var(--sidebar-border); margin: .8rem 0; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper {
    flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden;
    background: transparent; color: var(--ink); position: relative; z-index: 1;
    scroll-behavior: smooth;
}
.wrap { max-width: 920px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }

/* intestazione */
.scan-hero { padding: clamp(2.5rem, 7vh, 4.5rem) 0 clamp(1.5rem, 4vw, 2.5rem); }
.back-link { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.back-link:hover { color: var(--violet); }
.back-link svg { width: 14px; height: 14px; }
.eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.scan-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 1rem; }
.scan-desc { font-family: var(--disp); font-weight: 400; font-size: clamp(1.05rem, 1.8vw, 1.3rem); line-height: 1.5; color: var(--ink-2); max-width: 54ch; }

/* tab */
.tab-bar { display: flex; gap: clamp(1.2rem, 3vw, 2.2rem); border-bottom: 1px solid var(--line); margin-bottom: clamp(1.4rem, 4vw, 2.2rem); flex-wrap: wrap; }
.tab-btn { background: none; border: none; color: var(--mut); font-family: var(--sans); font-size: .92rem; font-weight: 500; padding: .7rem 0; cursor: pointer; position: relative; transition: color .2s; }
.tab-btn:hover { color: var(--ink-2); }
.tab-btn.active { color: var(--ink); }
.tab-btn::after { content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px; background: var(--violet); transform: scaleX(0); transform-origin: left; transition: transform .25s; }
.tab-btn.active::after { transform: scaleX(1); }

/* salva */
.save-bar { display: flex; justify-content: flex-end; margin-bottom: 1.2rem; }
.btn-save { display: inline-flex; align-items: center; gap: .5rem; font-family: var(--sans); font-size: .85rem; font-weight: 500; color: var(--violet); background: none; border: 1px solid var(--violet); border-radius: 0; padding: .6rem 1.1rem; cursor: pointer; transition: background .2s, color .2s; }
.btn-save:hover { background: var(--violet); color: #14121f; }
.btn-save:disabled { opacity: .55; cursor: default; }
.btn-save svg { width: 15px; height: 15px; }

/* risultati */
.scanResultZone { display: none; }
.scanResultZone.active { display: grid; grid-template-columns: 1fr 1fr; gap: 0 clamp(1.5rem, 4vw, 3rem); }
.scan-item { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding: .95rem .1rem; border-bottom: 1px solid var(--line); text-decoration: none; color: var(--ink); opacity: 0; transform: translateY(8px); transition: opacity .4s ease, transform .4s ease, color .2s; }
.scan-item.visible { opacity: 1; transform: none; }
.scan-item:hover { color: var(--violet); }
.scan-item:hover .scan-item-label { color: var(--violet); }
.scan-item-label { font-size: .9rem; color: var(--ink-2); transition: color .2s; flex-shrink: 0; }
.scan-item-value { font-family: var(--mono); font-size: .84rem; color: var(--ink); text-align: right; word-break: break-word; min-width: 0; }
.scan-item-value.loading { color: var(--faint); animation: ld 1.2s ease-in-out infinite; }
@keyframes ld { 0%, 100% { opacity: .5; } 50% { opacity: .85; } }

#mapFrame { grid-column: 1 / -1; width: 100%; height: 220px; border: 1px solid var(--line); border-radius: 0; margin-top: 1.2rem; }
#mapFrame[src=""], #mapFrame:not([src]) { display: none; }

/* footer */
.foot { border-top: 1px solid var(--line); margin-top: clamp(2.5rem, 6vw, 3.5rem); padding-top: 1.5rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
.foot .copy { font-size: .78rem; color: var(--faint); }
.foot .legal { display: flex; flex-wrap: wrap; gap: .5rem 1.3rem; }
.foot a { font-size: .78rem; color: var(--mut); text-decoration: none; transition: color .2s; }
.foot a:hover { color: var(--violet); }

/* ─────────── RESPONSIVE ─────────── */
@media (prefers-reduced-motion: reduce) {
    .scan-item { opacity: 1; transform: none; }
}
@media (max-width: 720px) {
    .scanResultZone.active { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    html, body { height: 100%; overflow: hidden; }
    .sidebar { position: fixed; top: 0; left: 0; right: 0; width: 100%; height: auto; min-width: 0; border-right: none; border-bottom: 1px solid var(--sidebar-border); padding: 0; flex-direction: column; overflow: hidden; z-index: 200; }
    .sidebar-top { margin-bottom: 0; padding: 0 1.2rem; width: 100%; height: 56px; flex-shrink: 0; }
    .hamburger { display: flex; }
    .search-bar, .nav-section, .nav-divider, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .search-bar { display: flex; flex-direction: row; align-items: center; margin: .4rem 1.2rem .6rem; width: calc(100% - 2.4rem); }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 55vh; overflow-y: auto; }
    .sidebar.open .nav-divider { display: block; margin: .4rem 1.2rem; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse">
            PatchPulse
        </a>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <button class="bell-btn" title="<?= t('nav.notifications') ?>" aria-label="<?= t('nav.notifications') ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button>
        </div>
    </div>

    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="<?= t('nav.search_placeholder') ?>" aria-label="<?= t('nav.search_placeholder') ?>">
        <span class="search-shortcut">S</span>
    </div>

    <div class="nav-section">
        <a href="home.php" class="nav-item" data-section="home">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
            <?= t('nav.homepage') ?>
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
            <?= t('nav.applications') ?>
        </a>
        <a href="browser-scan.php" class="nav-item active">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            <?= t('nav.browser_scanner') ?>
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            <?= t('nav.website_overview') ?>
        </a>
        <a href="vpn-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            <?= t('nav.vpn_checker') ?>
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            <?= t('nav.data_breach_monitor') ?>
        </a>
        <a href="extension.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.5 11H19V7a2 2 0 0 0-2-2h-4V3.5a2.5 2.5 0 0 0-5 0V5H4a2 2 0 0 0-2 2v3.8h1.5a2.2 2.2 0 0 1 0 4.4H2V19a2 2 0 0 0 2 2h3.8v-1.5a2.2 2.2 0 0 1 4.4 0V21H17a2 2 0 0 0 2-2v-4h1.5a2.5 2.5 0 0 0 0-5z"/></svg></span>
            <?= t('ext.eyebrow') ?>
        </a>
    </div>

    <div class="nav-divider"></div>

    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
            <?= t('nav.faq') ?>
        </a>
        <?php if ($isLoggedIn): ?>
        <a href="account.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <?= t('nav.account') ?>
        </a>
        <?php else: ?>
        <a href="log-reg.php#login" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>
            <?= t('nav.login') ?>
        </a>
        <?php endif; ?>
        <a href="settings.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>
            <?= t('nav.settings') ?>
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">
    <div class="wrap">

        <!-- intestazione -->
        <section class="scan-hero">
            <a href="home.php" class="back-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                <?= t('nav.back_home') ?>
            </a>
            <p class="eyebrow"><?= t('bs.eyebrow') ?></p>
            <h1 class="scan-title"><?= t('bs.title') ?></h1>
            <p class="scan-desc"><?= t('bs.desc') ?></p>
        </section>

        <!-- tab -->
        <div class="tab-bar">
            <button class="tab-btn active" data-scan="webTracking"><?= t('bs.tab.tracking') ?></button>
            <button class="tab-btn" data-scan="functionality"><?= t('bs.tab.functionality') ?></button>
            <button class="tab-btn" data-scan="deviceInfo"><?= t('bs.tab.device') ?></button>
        </div>

        <?php if ($isLoggedIn): ?>
        <div class="save-bar">
            <button id="saveScansButton" class="btn-save">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?= t('bs.save_all') ?>
            </button>
        </div>
        <?php endif; ?>

        <!-- risultati -->
        <div class="scan-results-container">

            <!-- Web Tracking -->
            <div id="webTracking" class="scanResultZone active">
                <a href="vulnerabilities_info.php#cookies" class="scan-item">
                    <span class="scan-item-label">Cookies</span>
                    <span class="scan-item-value loading" id="cookiesEnabled">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#dnt" class="scan-item">
                    <span class="scan-item-label">Do Not Track</span>
                    <span class="scan-item-value loading" id="doNotTrack">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#fingerprinting" class="scan-item">
                    <span class="scan-item-label">Browser Fingerprinting</span>
                    <span class="scan-item-value loading" id="browserFingerprinting">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webrtc" class="scan-item">
                    <span class="scan-item-label">WebRTC Support</span>
                    <span class="scan-item-value loading" id="webrtcSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#https" class="scan-item">
                    <span class="scan-item-label">HTTPS Only</span>
                    <span class="scan-item-value loading" id="httpsOnly">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#adblock" class="scan-item">
                    <span class="scan-item-label">AdBlocker</span>
                    <span class="scan-item-value loading" id="adBlockEnabled">Loading...</span>
                </a>
            </div>

            <!-- Functionality -->
            <div id="functionality" class="scanResultZone">
                <a href="vulnerabilities_info.php#javascript" class="scan-item">
                    <span class="scan-item-label">JavaScript</span>
                    <span class="scan-item-value loading" id="javascriptStatus">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webgl" class="scan-item">
                    <span class="scan-item-label">WebGL</span>
                    <span class="scan-item-value loading" id="webglFingerprinting">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#developer-mode" class="scan-item">
                    <span class="scan-item-label">Developer Mode</span>
                    <span class="scan-item-value loading" id="developerMode">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webassembly" class="scan-item">
                    <span class="scan-item-label">WebAssembly</span>
                    <span class="scan-item-value loading" id="webAssemblySupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#web-workers" class="scan-item">
                    <span class="scan-item-label">Web Workers</span>
                    <span class="scan-item-value loading" id="webWorkersSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#media-queries" class="scan-item">
                    <span class="scan-item-label">Media Queries</span>
                    <span class="scan-item-value loading" id="mediaQueriesSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#web-notifications" class="scan-item">
                    <span class="scan-item-label">Web Notifications</span>
                    <span class="scan-item-value loading" id="webNotificationsSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#permissions-api" class="scan-item">
                    <span class="scan-item-label">Permissions API</span>
                    <span class="scan-item-value loading" id="permissionsAPISupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#payment-api" class="scan-item">
                    <span class="scan-item-label">Payment Request API</span>
                    <span class="scan-item-value loading" id="paymentRequestAPISupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#html-css" class="scan-item">
                    <span class="scan-item-label">HTML5 / CSS3</span>
                    <span class="scan-item-value loading" id="htmlCssSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#geolocation" class="scan-item">
                    <span class="scan-item-label">Geolocation</span>
                    <span class="scan-item-value loading" id="geolocationInfo">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#sensors" class="scan-item">
                    <span class="scan-item-label">Sensors Support</span>
                    <span class="scan-item-value loading" id="sensorsSupported">Loading...</span>
                </a>
                <iframe id="mapFrame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
            </div>

            <!-- Device Info -->
            <div id="deviceInfo" class="scanResultZone">
                <a href="vulnerabilities_info.php#ipv4" class="scan-item">
                    <span class="scan-item-label">Public IPv4</span>
                    <span class="scan-item-value loading" id="publicIpv4">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#ipv6" class="scan-item">
                    <span class="scan-item-label">Public IPv6</span>
                    <span class="scan-item-value loading" id="publicIpv6">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-type" class="scan-item">
                    <span class="scan-item-label">Browser Type</span>
                    <span class="scan-item-value loading" id="browserType">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-version" class="scan-item">
                    <span class="scan-item-label">Browser Version</span>
                    <span class="scan-item-value loading" id="browserVersion">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-language" class="scan-item">
                    <span class="scan-item-label">Browser Language</span>
                    <span class="scan-item-value loading" id="browserLanguage">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#operating-system" class="scan-item">
                    <span class="scan-item-label">Operating System</span>
                    <span class="scan-item-value loading" id="osVersion">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#incognito" class="scan-item">
                    <span class="scan-item-label">Incognito Mode</span>
                    <span class="scan-item-value loading" id="incognitoMode">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">Device Memory</span>
                    <span class="scan-item-value loading" id="deviceMemory">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">CPU Threads</span>
                    <span class="scan-item-value loading" id="cpuThreads">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">CPU Cores</span>
                    <span class="scan-item-value loading" id="cpuCores">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">GPU Info</span>
                    <span class="scan-item-value loading" id="gpuName">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Color Depth</span>
                    <span class="scan-item-value loading" id="colorDepth">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Pixel Depth</span>
                    <span class="scan-item-value loading" id="pixelDepth">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#touch-support" class="scan-item">
                    <span class="scan-item-label">Touch Support</span>
                    <span class="scan-item-value loading" id="touchSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Screen Resolution</span>
                    <span class="scan-item-value loading" id="screenResolutionDisplay">Loading...</span>
                    <span id="width" style="display:none">Loading...</span>
                    <span id="height" style="display:none">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#mime-types" class="scan-item">
                    <span class="scan-item-label">MIME Types</span>
                    <span class="scan-item-value loading" id="mimeTypes">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#referrer-policy" class="scan-item">
                    <span class="scan-item-label">Referrer Policy</span>
                    <span class="scan-item-value loading" id="referrerPolicy">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#battery-status" class="scan-item">
                    <span class="scan-item-label">Battery Status</span>
                    <span class="scan-item-value loading" id="batteryStatus">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#security-protocols" class="scan-item">
                    <span class="scan-item-label">Security Protocols</span>
                    <span class="scan-item-value loading" id="securityProtocols">Loading...</span>
                </a>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="foot" id="contatti">
            <span class="copy">&copy; <?= date('Y') ?> PatchPulse</span>
            <span class="legal">
                <a href="policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a>
                <a href="policy/terms&amp;condition.php" target="_blank"><?= t('footer.terms') ?></a>
                <a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
            </span>
        </footer>

    </div>
</main>

<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
<script type="module" src="javascript/fastScan.js"></script>
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
