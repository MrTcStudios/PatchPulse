<?php
include 'config.php';
require_once __DIR__ . "/lang/lang.php";
$currentLang = pp_lang_current();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('breach.title_tag') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>.cf-turnstile iframe{max-width:100%!important}@media(max-width:400px){.cf-turnstile{transform:scale(0.85);transform-origin:left top;margin-bottom:-8px}}</style>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
        <a href="data-breach-checker.php" class="nav-item active">
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
<div class="main-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            <?= t('nav.back_home') ?>
        </a>
        <div class="page-header-eyebrow"><?= t('breach.eyebrow') ?></div>
        <h1 class="page-header-title"><?= t('breach.title') ?></h1>
        <p class="page-header-desc"><?= t('breach.desc') ?></p>
    </div>

    <!-- SCANNER -->
    <div class="scanner-section">
        <div class="scanner-card">

            <form id="checkForm">
                <div class="input-group">
                    <input type="email" id="emailInput" placeholder="<?= t('breach.email_placeholder') ?>" required>
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="light" style="margin-top:12px"></div>
                </div>
                <button type="submit" class="check-button" id="checkButton"><?= t('breach.check_btn') ?></button>
            </form>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span><?= t('breach.loading') ?></span>
            </div>

            <div id="results" class="results"></div>

        </div>

        <!-- Info section -->
        <div class="info-section">
            <h3><?= t('breach.info.title') ?></h3>
            <p><?= t('breach.info.p1') ?></p>
            <p><?= t('breach.info.p2') ?></p>
            <ul>
                <li><?= t('breach.info.li1') ?></li>
                <li><?= t('breach.info.li2') ?></li>
                <li><?= t('breach.info.li3') ?></li>
            </ul>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h4>PatchPulse</h4>
                <p><?= t('footer.tagline_short') ?></p>
            </div>
            <div class="footer-col">
                <h4><?= t('footer.col.scanners') ?></h4>
                <a href="browser-scan.php"><?= t('footer.scanner.browser') ?></a>
                <a href="VulnerabilityScanner.php"><?= t('footer.scanner.overview') ?></a>
                <a href="vpn-checker.php"><?= t('nav.vpn_checker') ?></a>
                <a href="data-breach-checker.php"><?= t('nav.data_breach_monitor') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= t('footer.col.contacts') ?></h4>
                <p>Email: support@patchpulse.org</p>
                <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub</a>
            </div>
            <div class="footer-col">
                <h4><?= t('footer.col.resources') ?></h4>
                <a href="home.php#account"><?= t('footer.account_area') ?></a>
                <a href="home.php#about"><?= t('footer.documentation') ?></a>
                <a href="home.php#faq"><?= t('nav.faq') ?></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PatchPulse. <?= t('footer.rights') ?> |
                <a href="policy/privacy_policy.php"><?= t('footer.privacy') ?></a> |
                <a href="policy/terms&condition.php"><?= t('footer.terms') ?></a> |
            	<a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
	    </p>
        </div>
    </footer>

</div>

<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
</body>
</html>
