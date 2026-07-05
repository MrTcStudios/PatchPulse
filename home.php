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
<title><?= t('home.title_tag') ?></title>
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
    background: linear-gradient(180deg, rgba(13,13,16,0.42) 0%, rgba(13,13,16,0.60) 55%, rgba(13,13,16,0.78) 100%); }

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
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN — centrato ─────────── */
.main-wrapper {
    flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden;
    background: transparent; color: var(--ink); position: relative; z-index: 1;
    scroll-behavior: smooth;
}
.wrap { max-width: 740px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }

.eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }

.hero { position: relative; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; justify-content: center; padding: clamp(3rem, 10vh, 7rem) 0 clamp(5rem, 14vh, 9rem); }
.scroll-cue { position: absolute; left: 0; bottom: clamp(1.4rem, 4vh, 2.4rem); display: inline-flex; align-items: center; color: var(--mut); transition: color .2s; }
.scroll-cue:hover { color: var(--violet); }
.scroll-cue svg { width: 22px; height: 22px; animation: cue 1.9s ease-in-out infinite; }
@keyframes cue { 0%, 100% { transform: translateY(0); opacity: .5; } 50% { transform: translateY(4px); opacity: 1; } }
@media (prefers-reduced-motion: reduce) { .scroll-cue svg { animation: none; } }
.title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.9rem, 7.5vw, 5rem); line-height: .98; letter-spacing: -.025em; color: var(--ink); margin: 1.1rem 0 1.2rem; }
.title .dot { color: var(--violet); }
.sub { font-family: var(--disp); font-weight: 400; font-size: clamp(1.12rem, 2vw, 1.4rem); line-height: 1.5; color: var(--ink-2); max-width: 42ch; }

.sec { border-top: 1px solid var(--line); padding: clamp(2.8rem, 7vw, 4.2rem) 0; }
.sec-title { font-family: var(--disp); font-weight: 500; font-size: clamp(1.7rem, 3.6vw, 2.4rem); line-height: 1.12; letter-spacing: -.02em; color: var(--ink); margin-top: .7rem; }
.sec-desc { color: var(--ink-2); font-size: 1.02rem; line-height: 1.65; margin-top: .9rem; max-width: 60ch; }

/* strumenti */
.tools { margin-top: clamp(1.4rem, 4vw, 2.2rem); border-top: 1px solid var(--line); }
.tool { display: flex; align-items: baseline; justify-content: space-between; gap: 1.5rem; padding: 1.2rem .2rem; border-bottom: 1px solid var(--line); text-decoration: none; color: var(--ink); transition: color .2s; }
.tool .tname { font-family: var(--disp); font-weight: 500; font-size: clamp(1.25rem, 2.6vw, 1.72rem); line-height: 1.1; letter-spacing: -.01em; }
.tool .tarrow { font-size: 1.05rem; color: var(--faint); flex-shrink: 0; transition: transform .2s, color .2s; }
.tool:hover { color: var(--violet); }
.tool:hover .tarrow { transform: translateX(6px); color: var(--violet); }

/* FAQ */
.faq { margin-top: clamp(1.4rem, 4vw, 2.2rem); border-top: 1px solid var(--line); }
.faq-item { border-bottom: 1px solid var(--line); }
.faq-q { display: flex; align-items: flex-start; gap: 1.1rem; width: 100%; background: none; border: none; cursor: pointer; text-align: left; padding: 1.25rem .2rem; font-family: var(--disp); font-weight: 500; font-size: clamp(1.05rem, 2vw, 1.32rem); line-height: 1.3; color: var(--ink); transition: color .2s; }
.faq-q:hover { color: var(--violet); }
.faq-q .qt { flex: 1; }
.faq-q .sgn { margin-left: auto; font-size: 1.35rem; color: var(--mut); line-height: 1; transition: transform .25s, color .2s; flex-shrink: 0; }
.faq-item.open .sgn { transform: rotate(45deg); color: var(--violet); }
.faq-a { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
.faq-a-inner { padding: 0 .2rem 1.4rem; color: var(--ink-2); font-size: 1rem; line-height: 1.66; max-width: 66ch; }

/* chi siamo */
.about-p { color: var(--ink-2); font-size: 1.02rem; line-height: 1.72; max-width: 64ch; margin-top: 1.1rem; }
.about-notice { margin-top: 1.6rem; border-left: 2px solid var(--violet); border-radius: 0; padding: .1rem 0 .1rem 1.2rem; }
.about-notice h4 { font-size: .95rem; font-weight: 600; color: var(--ink); margin-bottom: .35rem; }
.about-notice p { color: var(--mut); font-size: .92rem; line-height: 1.62; max-width: 60ch; }

/* azioni (estensione + accesso) */
.actions { margin-top: clamp(1.3rem, 4vw, 1.8rem); display: flex; flex-wrap: wrap; align-items: center; gap: .9rem; }
.btn { font-family: var(--sans); font-size: .9rem; font-weight: 500; text-decoration: none; padding: .8rem 1.4rem; border: 1px solid var(--violet); color: var(--violet); border-radius: 0; transition: background .2s, color .2s, border-color .2s; }
.btn:hover { background: var(--violet); color: #14121f; }
.btn.alt { border-color: var(--line); color: var(--ink-2); }
.btn.alt:hover { border-color: var(--ink); color: var(--ink); background: none; }
.actions-note { width: 100%; color: var(--faint); font-size: .85rem; margin-top: .3rem; }

/* footer */
.foot { border-top: 1px solid var(--line); margin-top: clamp(2.5rem, 6vw, 3.5rem); padding-top: 1.5rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
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
    .search-bar, .nav-section, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .search-bar { display: flex; flex-direction: row; align-items: center; margin: .4rem 1.2rem .6rem; width: calc(100% - 2.4rem); }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 60vh; overflow-y: auto; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .hero { min-height: calc(100vh - 56px); min-height: calc(100dvh - 56px); }
}
</style>
</head>
<body>

    <div id="vanta-bg"></div>
    <div id="scrim"></div>

    <!-- ══════════ SIDEBAR (preservata dalla home attuale) ══════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <a href="home.php" class="logo">
                <img src="images/PatchPulseLogo.svg" alt="PatchPulse">
                PatchPulse
            </a>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <button class="bell-btn" title="<?= t('nav.notifications') ?>" aria-label="<?= t('nav.notifications') ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </button>
                <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <div class="search-bar">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="<?= t('nav.search_placeholder') ?>" aria-label="<?= t('nav.search_placeholder') ?>">
            <span class="search-shortcut">S</span>
        </div>

        <div class="nav-section">
            <a href="#home" class="nav-item active" data-section="home">
                <span class="nav-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </span>
                <?= t('nav.homepage') ?>
            </a>
            <a href="#servizi" class="nav-item" data-section="servizi">
                <span class="nav-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </span>
                <?= t('nav.applications') ?>
            </a>
            <a href="browser-scan.php" class="nav-item" target="_blank">
                <span class="nav-sub-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
                <?= t('nav.browser_scanner') ?>
            </a>
            <a href="VulnerabilityScanner.php" class="nav-item" target="_blank">
                <span class="nav-sub-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
                <?= t('nav.website_overview') ?>
            </a>
            <a href="vpn-checker.php" class="nav-item" target="_blank">
                <span class="nav-sub-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
                <?= t('nav.vpn_checker') ?>
            </a>
            <a href="data-breach-checker.php" class="nav-item" target="_blank">
                <span class="nav-sub-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </span>
                <?= t('nav.data_breach_monitor') ?>
            </a>
            <a href="extension.php" class="nav-item">
                <span class="nav-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M20.5 11H19V7a2 2 0 0 0-2-2h-4V3.5a2.5 2.5 0 0 0-5 0V5H4a2 2 0 0 0-2 2v3.8h1.5a2.2 2.2 0 0 1 0 4.4H2V19a2 2 0 0 0 2 2h3.8v-1.5a2.2 2.2 0 0 1 4.4 0V21H17a2 2 0 0 0 2-2v-4h1.5a2.5 2.5 0 0 0 0-5z"/>
                    </svg>
                </span>
                <?= t('ext.eyebrow') ?>
            </a>
        </div>

        <div class="sidebar-bottom">
            <a href="#faq" class="nav-item">
                <span class="nav-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </span>
                <?= t('nav.faq') ?>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
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
                <span class="nav-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
                    </svg>
                </span>
                <?= t('nav.settings') ?>
            </a>
        </div>
    </aside>

    <!-- ══════════ MAIN ══════════ -->
    <main class="main-wrapper" id="main">
        <div class="wrap">

            <!-- HERO -->
            <section class="hero" id="home">
                <p class="eyebrow"><?= t('home.hero.eyebrow') ?></p>
                <h1 class="title"><?= t('home.hero.title') ?><span class="dot">.</span></h1>
                <p class="sub"><?= t('home.hero.subtitle') ?></p>
                <a class="scroll-cue" href="#servizi" aria-label="<?= t('home.hero.cta') ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </a>
            </section>

            <!-- STRUMENTI -->
            <section class="sec" id="servizi">
                <p class="eyebrow"><?= t('home.tools.title') ?></p>
                <nav class="tools" aria-label="<?= t('home.tools.title') ?>">
                    <a class="tool" href="VulnerabilityScanner.php" target="_blank" rel="noopener">
                        <span class="tname"><?= t('home.tools.featured_title') ?></span><span class="tarrow">&rarr;</span>
                    </a>
                    <a class="tool" href="browser-scan.php" target="_blank" rel="noopener">
                        <span class="tname"><?= t('home.tools.browser_title') ?></span><span class="tarrow">&rarr;</span>
                    </a>
                    <a class="tool" href="vpn-checker.php" target="_blank" rel="noopener">
                        <span class="tname"><?= t('home.tools.vpn_title') ?></span><span class="tarrow">&rarr;</span>
                    </a>
                    <a class="tool" href="data-breach-checker.php" target="_blank" rel="noopener">
                        <span class="tname"><?= t('home.tools.breach_title') ?></span><span class="tarrow">&rarr;</span>
                    </a>
                </nav>
            </section>

            <!-- ESTENSIONE -->
            <section class="sec" id="extension">
                <p class="eyebrow"><?= t('home.ext.tag') ?></p>
                <h2 class="sec-title"><?= t('home.ext.title') ?></h2>
                <p class="sec-desc"><?= t('home.ext.desc') ?></p>
                <div class="actions">
                    <a class="btn" href="extension.php"><?= t('home.ext.cta') ?></a>
                </div>
            </section>

            <!-- FAQ -->
            <section class="sec" id="faq">
                <p class="eyebrow"><?= t('home.faq.label') ?></p>
                <h2 class="sec-title"><?= t('home.faq.title') ?></h2>
                <div class="faq">
                    <div class="faq-item">
                        <button class="faq-q"><span class="qt"><?= t('home.faq.q1') ?></span><span class="sgn">+</span></button>
                        <div class="faq-a"><div class="faq-a-inner"><?= t('home.faq.a1') ?></div></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-q"><span class="qt"><?= t('home.faq.q2') ?></span><span class="sgn">+</span></button>
                        <div class="faq-a"><div class="faq-a-inner"><?= t('home.faq.a2') ?></div></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-q"><span class="qt"><?= t('home.faq.q3') ?></span><span class="sgn">+</span></button>
                        <div class="faq-a"><div class="faq-a-inner"><?= t('home.faq.a3') ?></div></div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-q"><span class="qt"><?= t('home.faq.q4') ?></span><span class="sgn">+</span></button>
                        <div class="faq-a"><div class="faq-a-inner"><?= t('home.faq.a4') ?></div></div>
                    </div>
                </div>
            </section>

            <!-- CHI SIAMO -->
            <section class="sec" id="about">
                <p class="eyebrow"><?= t('home.about.label') ?></p>
                <h2 class="sec-title"><?= t('home.about.title') ?></h2>
                <p class="about-p"><?= t('home.about.p1') ?></p>
                <p class="about-p"><?= t('home.about.p2') ?></p>
                <div class="about-notice">
                    <h4><?= t('home.about.notice_title') ?></h4>
                    <p><?= t('home.about.notice_body') ?></p>
                </div>
            </section>

            <!-- ACCESSO -->
            <section class="sec" id="account">
                <p class="eyebrow"><?= t('home.account.label') ?></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <h2 class="sec-title"><?= t('home.account.welcome_back') ?></h2>
                    <p class="sec-desc"><?= t('home.account.welcome_desc') ?></p>
                    <div class="actions">
                        <a class="btn" href="account.php"><?= t('nav.account') ?></a>
                    </div>
                <?php else: ?>
                    <h2 class="sec-title"><?= t('home.account.access_title') ?></h2>
                    <p class="sec-desc"><?= t('home.account.access_desc') ?></p>
                    <div class="actions">
                        <a class="btn" href="log-reg.php#login"><?= t('home.account.cta_login') ?></a>
                        <a class="btn alt" href="log-reg.php#register"><?= t('home.account.cta_register') ?></a>
                        <span class="actions-note"><?= t('home.account.no_account') ?></span>
                    </div>
                <?php endif; ?>
            </section>

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
    <script src="js/three.min.js"></script>
    <script src="js/vanta.waves.min.js"></script>
    <script>
    (function () {
        var sb = document.getElementById('sidebar');
        var hb = document.getElementById('hamburger');

        if (hb && sb) {
            hb.addEventListener('click', function () {
                sb.classList.toggle('open');
                hb.classList.toggle('active');
            });
            sb.querySelectorAll('.nav-item').forEach(function (a) {
                a.addEventListener('click', function () {
                    if (window.innerWidth <= 768) { sb.classList.remove('open'); hb.classList.remove('active'); }
                });
            });
        }

        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var id = a.getAttribute('href');
                if (id.length > 1) {
                    var el = document.querySelector(id);
                    if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
            });
        });

        document.querySelectorAll('.faq-q').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.parentElement;
                var ans = btn.nextElementSibling;
                var open = item.classList.toggle('open');
                ans.style.maxHeight = open ? (ans.firstElementChild.scrollHeight + 28) + 'px' : null;
            });
        });

        var si = document.querySelector('.search-bar input');
        document.addEventListener('keydown', function (e) {
            if ((e.key === 's' || e.key === 'S') && !/^(input|textarea)$/i.test(document.activeElement.tagName)) {
                e.preventDefault(); if (si) si.focus();
            }
        });

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
