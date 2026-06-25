<?php
include 'config.php';
require_once __DIR__ . "/lang/lang.php";
$currentLang = pp_lang_current();

// URL senza prefisso lingua: AMO reindirizza da solo alla lingua del visitatore.
$EXT_PUBLISHED   = true;
$EXT_FIREFOX_URL = 'https://addons.mozilla.org/firefox/addon/patchpulse-anti-phishing/';
$EXT_SUPPORT_EMAIL = 'support@patchpulse.org';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('ext.title_tag') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">
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
        <a href="home.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?>
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><?= t('nav.applications') ?>
        </a>
        <a href="browser-scan.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.browser_scanner') ?>
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.website_overview') ?>
        </a>
        <a href="vpn-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.vpn_checker') ?>
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.data_breach_monitor') ?>
        </a>
    </div>
    <div class="nav-divider"></div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span><?= t('nav.faq') ?>
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
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?>
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">

    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            <?= t('nav.back_home') ?>
        </a>
        <p class="page-header-eyebrow"><?= t('ext.eyebrow') ?></p>
        <h1 class="page-header-title"><?= t('ext.title') ?></h1>
        <p class="page-header-desc"><?= t('ext.desc') ?></p>
    </div>

    <style>
        .ext-page { max-width: 920px; margin: 0 auto; padding: 0 1.5rem 3rem; }

        .ext-hero {
            background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 20px;
            padding: 2.6rem 2.2rem; text-align: center; box-shadow: 0 12px 40px rgba(0,0,0,0.06);
        }
        .ext-hero-icon {
            width: 86px; height: 86px; margin: 0 auto 1.4rem; border-radius: 22px;
            background: rgba(139,124,248,0.12); border: 1px solid rgba(139,124,248,0.25);
            display: flex; align-items: center; justify-content: center;
        }
        .ext-hero-icon svg { width: 44px; height: 44px; stroke: #8b7cf8; }
        .ext-btn {
            display: inline-flex; align-items: center; gap: 0.55rem;
            background: #8b7cf8; color: #fff; text-decoration: none;
            padding: 0.85rem 1.7rem; border-radius: 12px; font-weight: 600; font-size: 1rem;
            transition: background 0.2s, transform 0.2s;
        }
        .ext-btn:hover { background: #6c5ce7; transform: translateY(-2px); }
        .ext-btn svg { width: 20px; height: 20px; stroke: #fff; }
        .ext-btn-soon {
            display: inline-flex; align-items: center; gap: 0.55rem;
            background: rgba(0,0,0,0.04); color: #999; cursor: default;
            border: 1px dashed rgba(0,0,0,0.18);
            padding: 0.85rem 1.7rem; border-radius: 12px; font-weight: 600; font-size: 1rem;
        }
        .ext-soon-note { font-size: 0.82rem; color: #aaa; margin-top: 0.8rem; }
        .ext-free { font-size: 0.82rem; color: #22a06b; margin-top: 1.1rem; font-weight: 600; }

        .ext-block-label {
            text-align: center; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em;
            text-transform: uppercase; color: #8b7cf8; margin-top: 3.2rem;
        }
        .ext-block-title {
            text-align: center; font-size: 1.5rem; font-weight: 600; color: #1a1a1a;
            margin: 0.5rem 0 1.8rem;
        }

        .ext-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
        .ext-card { background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; padding: 1.5rem; }
        .ext-card-ico {
            width: 42px; height: 42px; border-radius: 11px; background: rgba(139,124,248,0.1);
            display: flex; align-items: center; justify-content: center; margin-bottom: 0.9rem;
        }
        .ext-card-ico svg { width: 21px; height: 21px; stroke: #8b7cf8; }
        .ext-card h3 { font-size: 1rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.35rem; }
        .ext-card p { font-size: 0.86rem; color: #888; line-height: 1.55; }

        .ext-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem; }
        .ext-step { background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; padding: 1.5rem; }
        .ext-step-num {
            width: 30px; height: 30px; border-radius: 50%; background: #8b7cf8; color: #fff;
            font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;
            margin-bottom: 0.9rem;
        }
        .ext-step h3 { font-size: 0.95rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.3rem; }
        .ext-step p { font-size: 0.85rem; color: #888; line-height: 1.55; }

        .ext-why {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff;
            border-radius: 18px; padding: 2.1rem; margin-top: 2.4rem;
        }
        .ext-why h3 { font-size: 1.15rem; font-weight: 600; margin-bottom: 0.6rem; }
        .ext-why p { font-size: 0.92rem; line-height: 1.65; color: rgba(255,255,255,0.72); }

        .ext-support {
            background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 18px;
            padding: 2.1rem; margin-top: 1.2rem; text-align: center;
        }
        .ext-support h3 { font-size: 1.1rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem; }
        .ext-support p { font-size: 0.9rem; color: #888; line-height: 1.6; max-width: 52ch; margin: 0 auto 1.2rem; }
        .ext-support-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #8b7cf8; color: #fff; text-decoration: none;
            padding: 0.7rem 1.4rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem;
            transition: background 0.2s;
        }
        .ext-support-btn:hover { background: #6c5ce7; }
        .ext-support-mail { margin-top: 0.9rem !important; margin-bottom: 0 !important; font-size: 0.8rem !important; color: #aaa !important; }

        @media (max-width: 640px) {
            .ext-grid, .ext-steps { grid-template-columns: 1fr; }
        }
    </style>

    <div class="ext-page">

        <!-- INSTALL -->
        <div class="ext-hero">
            <div class="ext-hero-icon">
                <svg fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <?php if ($EXT_PUBLISHED): ?>
                <a class="ext-btn" href="<?= htmlspecialchars($EXT_FIREFOX_URL, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                    <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <?= t('ext.install.firefox') ?>
                </a>
            <?php else: ?>
                <span class="ext-btn-soon"><?= t('ext.install.soon') ?></span>
                <p class="ext-soon-note"><?= t('ext.install.soon_note') ?></p>
            <?php endif; ?>
            <p class="ext-free"><?= t('ext.install.free') ?></p>
        </div>

        <!-- FEATURES -->
        <p class="ext-block-label"><?= t('ext.features.label') ?></p>
        <h2 class="ext-block-title"><?= t('ext.features.title') ?></h2>
        <div class="ext-grid">
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
                <h3><?= t('ext.feat1.title') ?></h3>
                <p><?= t('ext.feat1.desc') ?></p>
            </div>
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg></div>
                <h3><?= t('ext.feat2.title') ?></h3>
                <p><?= t('ext.feat2.desc') ?></p>
            </div>
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                <h3><?= t('ext.feat3.title') ?></h3>
                <p><?= t('ext.feat3.desc') ?></p>
            </div>
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
                <h3><?= t('ext.feat4.title') ?></h3>
                <p><?= t('ext.feat4.desc') ?></p>
            </div>
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M13.73 4a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                <h3><?= t('ext.feat5.title') ?></h3>
                <p><?= t('ext.feat5.desc') ?></p>
            </div>
            <div class="ext-card">
                <div class="ext-card-ico"><svg fill="none" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div>
                <h3><?= t('ext.feat6.title') ?></h3>
                <p><?= t('ext.feat6.desc') ?></p>
            </div>
        </div>

        <!-- HOW IT WORKS -->
        <p class="ext-block-label"><?= t('ext.how.label') ?></p>
        <h2 class="ext-block-title"><?= t('ext.how.title') ?></h2>
        <div class="ext-steps">
            <div class="ext-step">
                <div class="ext-step-num">1</div>
                <h3><?= t('ext.how.s1.title') ?></h3>
                <p><?= t('ext.how.s1.desc') ?></p>
            </div>
            <div class="ext-step">
                <div class="ext-step-num">2</div>
                <h3><?= t('ext.how.s2.title') ?></h3>
                <p><?= t('ext.how.s2.desc') ?></p>
            </div>
            <div class="ext-step">
                <div class="ext-step-num">3</div>
                <h3><?= t('ext.how.s3.title') ?></h3>
                <p><?= t('ext.how.s3.desc') ?></p>
            </div>
        </div>

        <!-- WHY DIFFERENT -->
        <div class="ext-why">
            <h3><?= t('ext.why.title') ?></h3>
            <p><?= t('ext.why.body') ?></p>
        </div>

        <!-- SUPPORT -->
        <div class="ext-support">
            <h3><?= t('ext.support.title') ?></h3>
            <p><?= t('ext.support.body') ?></p>
            <a class="ext-support-btn" href="mailto:<?= htmlspecialchars($EXT_SUPPORT_EMAIL, ENT_QUOTES, 'UTF-8') ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <?= t('ext.support.cta') ?>
            </a>
            <p class="ext-support-mail"><?= htmlspecialchars($EXT_SUPPORT_EMAIL, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

    </div>

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
            </div>
            <div class="footer-col">
                <h4><?= t('footer.col.contacts') ?></h4>
                <p>Email: support@patchpulse.org</p>
                <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub MrTcStudios/PatchPulse</a>
            </div>
            <div class="footer-col">
                <h4><?= t('footer.col.resources') ?></h4>
                <a href="account.php"><?= t('footer.account_area') ?></a>
                <a href="home.php#faq"><?= t('nav.faq') ?></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PatchPulse. <?= t('footer.rights') ?>
                <a href="policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a> |
                <a href="policy/terms&condition.php" target="_blank"><?= t('footer.terms') ?></a> |
                <a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
            </p>
        </div>
    </footer>

</main>

<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
</body>
</html>
