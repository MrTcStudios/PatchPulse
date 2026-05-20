<?php
//home.php
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- ══════════ SIDEBAR ══════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <a href="home.php" class="logo">
                <img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">
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

        <!-- Search -->
        <div class="search-bar">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="<?= t('nav.search_placeholder') ?>" aria-label="<?= t('nav.search_placeholder') ?>">
            <span class="search-shortcut">S</span>
        </div>

        <!-- Main nav -->
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
        </div>

        <!-- Bottom nav -->
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

        <!-- HERO -->
        <section class="hero" id="home">
            <div class="dot-grid"></div>
            <div class="hero-content">
                <div class="hero-text">
                    <p class="hero-eyebrow"><?= t('home.hero.eyebrow') ?></p>
                    <h1 class="hero-title"><?= t('home.hero.title') ?></h1>
                    <p class="hero-subtitle"><?= t('home.hero.subtitle') ?></p>
                    <a href="#servizi" class="hero-cta">
                        <?= t('home.hero.cta') ?>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </div>
            <!-- Wave bottom -->
            <div class="wave-bottom">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,60 C240,120 480,0 720,60 C960,120 1200,0 1440,60 L1440,120 L0,120 Z" fill="#8b7cf8"/>
                </svg>
            </div>
        </section>

        <!-- SCANNER SECTION -->
        <section class="section" id="servizi">
            <p class="section-label"><?= t('home.tools.label') ?></p>
            <h2 class="section-title"><?= t('home.tools.title') ?></h2>
            <p class="section-desc"><?= t('home.tools.desc') ?></p>

            <style>
                .tools-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
                .tool-card {
                    border-radius: 18px; padding: 1.6rem; cursor: pointer; position: relative; overflow: hidden;
                    transition: transform 0.22s cubic-bezier(.16,1,.3,1), box-shadow 0.22s;
                    text-decoration: none; display: block; color: inherit;
                }
                .tool-card:hover { transform: translateY(-3px); box-shadow: 0 12px 36px rgba(0,0,0,0.12); }

                /* Featured — full width */
                .tool-featured {
                    grid-column: 1 / -1;
                    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                    color: #fff;
                    padding: 2.2rem;
                    display: flex;
                    align-items: center;
                    gap: 2rem;
                }
                .tool-featured .tool-visual {
                    width: 120px; height: 120px; flex-shrink: 0;
                    background: rgba(139,124,248,0.15);
                    border-radius: 24px; border: 1px solid rgba(139,124,248,0.25);
                    display: flex; align-items: center; justify-content: center;
                }
                .tool-featured .tool-visual svg { width: 52px; height: 52px; stroke: #8b7cf8; }
                .tool-featured h3 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.4rem; }
                .tool-featured p { font-size: 0.9rem; color: rgba(255,255,255,0.65); line-height: 1.6; margin-bottom: 0.8rem; }
                .tool-featured .tool-tag { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; color: #8b7cf8; text-transform: uppercase; }
                .tool-featured .tool-arrow { position: absolute; bottom: 1.5rem; right: 1.8rem; opacity: 0.3; transition: opacity 0.2s; }
                .tool-featured:hover .tool-arrow { opacity: 0.7; }

                /* Regular cards */
                .tool-regular { background: #fff; border: 1px solid rgba(0,0,0,0.07); }
                .tool-regular .tool-ico {
                    width: 44px; height: 44px; border-radius: 12px; margin-bottom: 1rem;
                    display: flex; align-items: center; justify-content: center;
                }
                .tool-regular .tool-ico svg { width: 22px; height: 22px; }
                .tool-regular h3 { font-size: 1rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.3rem; }
                .tool-regular p { font-size: 0.84rem; color: #888; line-height: 1.5; margin-bottom: 0.8rem; }
                .tool-regular .tool-status { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em; }
                .tool-status-live { color: #22a06b; }
                .tool-status-live::before { content: ''; display: inline-block; width: 6px; height: 6px; background: #22a06b; border-radius: 50%; margin-right: 5px; vertical-align: middle; }
                .tool-status-wip { color: #bbb; }

                /* Color accents per card */
                .tool-ico-teal { background: rgba(20,184,166,0.1); }
                .tool-ico-teal svg { stroke: #14b8a6; }
                .tool-ico-amber { background: rgba(245,158,11,0.1); }
                .tool-ico-amber svg { stroke: #f59e0b; }
                .tool-ico-rose { background: rgba(244,63,94,0.1); }
                .tool-ico-rose svg { stroke: #f43f5e; }

                /* Coming soon */
                .tool-ghost {
                    background: rgba(0,0,0,0.02); border: 1.5px dashed rgba(0,0,0,0.1);
                    cursor: default; display: flex; align-items: center; justify-content: center;
                    min-height: 140px;
                }
                .tool-ghost:hover { transform: none; box-shadow: none; }
                .tool-ghost span { color: #ccc; font-size: 0.88rem; font-weight: 500; }

                @media (max-width: 640px) {
                    .tools-layout { grid-template-columns: 1fr; }
                    .tool-featured { flex-direction: column; text-align: center; gap: 1.2rem; padding: 1.8rem; }
                    .tool-featured .tool-arrow { display: none; }
                }
            </style>

            <div class="tools-layout">

                <!-- Featured: Vulnerability Scanner -->
                <a href="VulnerabilityScanner.php" class="tool-card tool-featured" target="_blank">
                    <div class="tool-visual">
                        <svg fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <span class="tool-tag"><?= t('home.tools.featured_tag') ?></span>
                        <h3><?= t('home.tools.featured_title') ?></h3>
                        <p><?= t('home.tools.featured_desc') ?></p>
                    </div>
                    <span class="tool-arrow"><svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                </a>

                <!-- Browser Scanner -->
                <a href="browser-scan.php" class="tool-card tool-regular" target="_blank">
                    <div class="tool-ico tool-ico-teal">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <h3><?= t('home.tools.browser_title') ?></h3>
                    <p><?= t('home.tools.browser_desc') ?></p>
                    <span class="tool-status tool-status-live"><?= t('home.tools.status_active') ?></span>
                </a>

                <!-- VPN Checker -->
                <a href="vpn-checker.php" class="tool-card tool-regular" target="_blank">
                    <div class="tool-ico tool-ico-amber">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3><?= t('home.tools.vpn_title') ?></h3>
                    <p><?= t('home.tools.vpn_desc') ?></p>
                    <span class="tool-status tool-status-live"><?= t('home.tools.status_active') ?></span>
                </a>

                <!-- Data Breach -->
                <a href="data-breach-checker.php" class="tool-card tool-regular" target="_blank">
                    <div class="tool-ico tool-ico-rose">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <h3><?= t('home.tools.breach_title') ?></h3>
                    <p><?= t('home.tools.breach_desc') ?></p>
                    <span class="tool-status tool-status-live"><?= t('home.tools.status_active') ?></span>
                </a>

                <!-- Coming soon -->
                <div class="tool-card tool-ghost">
                    <span><?= t('home.tools.coming_soon') ?></span>
                </div>

            </div>
        </section>

        <!-- STATS BAR -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label"><?= t('home.stats.scans') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">99.9%</div>
                <div class="stat-label"><?= t('home.stats.uptime') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label"><?= t('home.stats.availability') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4</div>
                <div class="stat-label"><?= t('home.stats.tools') ?></div>
            </div>
        </div>

        <hr class="section-sep">

        <!-- FAQ -->
        <section class="section centered" id="faq">
            <p class="section-label"><?= t('home.faq.label') ?></p>
            <h2 class="section-title"><?= t('home.faq.title') ?></h2>
            <p class="section-desc"><?= t('home.faq.desc') ?></p>

            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= t('home.faq.q1') ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <?= t('home.faq.a1') ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= t('home.faq.q2') ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <?= t('home.faq.a2') ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= t('home.faq.q3') ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <?= t('home.faq.a3') ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= t('home.faq.q4') ?></h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <?= t('home.faq.a4') ?>
                    </div>
                </div>
            </div>
        </section>

        <hr class="section-sep">

        <!-- ABOUT -->
        <section class="section centered" id="about">
            <p class="section-label"><?= t('home.about.label') ?></p>
            <h2 class="section-title"><?= t('home.about.title') ?></h2>

            <div class="about-box">
                <p><?= t('home.about.p1') ?></p>
                <p><?= t('home.about.p2') ?></p>
                <div class="about-notice">
                    <h4><?= t('home.about.notice_title') ?></h4>
                    <p><?= t('home.about.notice_body') ?></p>
                </div>
            </div>
        </section>

        <hr class="section-sep">

        <!-- ACCOUNT -->
        <section class="section centered" id="account">
            <p class="section-label"><?= t('home.account.label') ?></p>
            <h2 class="section-title"><?= t('home.account.title') ?></h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="account-card">
                    <h3><?= t('home.account.welcome_back') ?></h3>
                    <p><?= t('home.account.welcome_desc') ?></p>
                    <div class="btn-group">
                        <a href="account.php" class="btn-primary"><?= t('nav.account') ?></a>
                    </div>
                </div>
            <?php else: ?>
                <div class="account-card">
                    <h3><?= t('home.account.access_title') ?></h3>
                    <p><?= t('home.account.access_desc') ?></p>
                    <div class="btn-group">
                        <a href="log-reg.php#login" class="btn-primary"><?= t('home.account.cta_login') ?></a>
                        <a href="log-reg.php#register" class="btn-outline"><?= t('home.account.cta_register') ?></a>
                    </div>
                    <p class="account-note"><?= t('home.account.no_account') ?></p>
                </div>
            <?php endif; ?>
        </section>

        <!-- FOOTER -->
        <footer id="contatti">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>PatchPulse</h4>
                    <p><?= t('footer.tagline') ?></p>
                </div>
                <div class="footer-col">
                    <h4><?= t('footer.col.scanners') ?></h4>
                    <a href="browser-scan.php"><?= t('footer.scanner.browser') ?></a>
                    <a href="VulnerabilityScanner.php"><?= t('footer.scanner.vulnerability') ?></a>
                    <a href="vpn-checker.php"><?= t('footer.scanner.vpn') ?></a>
                    <a href="data-breach-checker.php"><?= t('footer.scanner.breach') ?></a>
                    <a href="#"><?= t('footer.scanner.coming') ?></a>
                </div>
                <div class="footer-col">
                    <h4><?= t('footer.col.contacts') ?></h4>
                    <p>Email: support@patchpulse.org</p>
                    <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub: MrTcStudios/PatchPulse</a>
                </div>
                <div class="footer-col">
                    <h4><?= t('footer.col.resources') ?></h4>
                    <a href="#account"><?= t('footer.account_area') ?></a>
                    <a href="#about"><?= t('footer.documentation') ?></a>
                    <a href="#faq"><?= t('nav.faq') ?></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 PatchPulse. <?= t('footer.rights') ?> |
                   <a href="policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a> |
                   <a href="policy/terms&condition.php" target="_blank"><?= t('footer.terms') ?></a> |
                   <a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
                </p>
            </div>
        </footer>

    </main><!-- end main-wrapper -->

    <?php pp_lang_emit_js(); ?>
    <script src="script.js"></script>
</body>
</html>
