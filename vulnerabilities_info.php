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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .vuln-content { max-width: 900px; margin: 0 auto; padding: 0 1rem 4rem; }
        .category-nav { display: flex; justify-content: center; gap: 0.7rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
        .category-btn {
            padding: 0.6rem 1.3rem; background: #fff; border: 1.5px solid rgba(0,0,0,0.1);
            border-radius: 50px; font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            font-weight: 500; color: #777; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .category-btn:hover, .category-btn.active { border-color: var(--purple); color: var(--purple); background: rgba(139,124,248,0.06); }
        .info-card {
            background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px;
            padding: 1.8rem; margin-bottom: 1.5rem; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .info-card:hover { border-color: rgba(139,124,248,0.25); box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
        .info-card h2 { color: #1a1a1a; font-size: 1.2rem; font-weight: 600; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .info-card h3 { color: #1a1a1a; font-size: 1rem; font-weight: 600; margin: 1.2rem 0 0.5rem; }
        .info-card p { color: #666; font-size: 0.92rem; line-height: 1.7; margin-bottom: 0.8rem; }
        .info-card ul { color: #666; margin-left: 1.2rem; margin-bottom: 0.8rem; font-size: 0.9rem; }
        .info-card li { margin-bottom: 0.4rem; line-height: 1.6; }
        .risk-level { padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; }
        .risk-low { background: rgba(34,160,107,0.1); color: #22a06b; }
        .risk-medium { background: rgba(217,119,6,0.1); color: #d97706; }
        .risk-high { background: rgba(220,38,38,0.1); color: #dc2626; }
        .protection-tip {
            background: rgba(139,124,248,0.06); border-left: 3px solid var(--purple);
            padding: 0.8rem 1rem; margin: 0.8rem 0; border-radius: 0 10px 10px 0;
            font-size: 0.9rem; color: #555; line-height: 1.6;
        }
        .protection-tip strong { color: var(--purple); }
        @media (max-width: 768px) { .category-nav { flex-direction: column; align-items: center; } .category-btn { width: 100%; max-width: 280px; text-align: center; } }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo"><img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">PatchPulse</a>
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

    <footer>
        <div class="footer-grid">
            <div class="footer-col"><h4>PatchPulse</h4><p><?= t('footer.tagline_short') ?></p></div>
            <div class="footer-col"><h4><?= t('footer.col.scanners') ?></h4><a href="browser-scan.php"><?= t('footer.scanner.browser') ?></a><a href="VulnerabilityScanner.php"><?= t('footer.scanner.vulnerability') ?></a></div>
            <div class="footer-col"><h4><?= t('footer.col.contacts') ?></h4><p>support@patchpulse.org</p></div>
        </div>
        <div class="footer-bottom"><p>&copy; <?= date("Y") ?> PatchPulse. | <a href="policy/privacy_policy.php"><?= t('footer.privacy') ?></a> | <a href="policy/terms&condition.php"><?= t('footer.terms') ?></a></p></div>
    </footer>
</main>
<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
</body>
</html>
