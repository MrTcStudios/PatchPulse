<?php
include("../config.php");
require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/_policy_render.php";

define('PP_LANG_INTERNAL', 1);
$POLICY = require __DIR__ . "/../lang/policy_data.php";

$lang = pp_lang_current();
$bundle = $POLICY[$lang] ?? $POLICY['en'];
$common = $bundle['common'];
$page = $bundle['privacy'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title_tag'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .legal-content { max-width: 800px; margin: 0 auto; padding: 2rem 3rem 4rem; }
        .legal-content h2 { font-family: 'DM Serif Display', serif; font-size: 1.5rem; color: #1a1a1a; margin: 2.5rem 0 0.8rem; }
        .legal-content h3 { font-size: 1rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 0.5rem; }
        .legal-content p, .legal-content li { color: #555; font-size: 0.92rem; line-height: 1.75; margin-bottom: 0.6rem; }
        .legal-content ul { padding-left: 1.5rem; margin-bottom: 1rem; }
        .legal-content .updated { color: #999; font-size: 0.82rem; margin-bottom: 2rem; }
        .legal-content a { color: var(--purple); }
        @media (max-width: 768px) { .legal-content { padding: 1.5rem; } }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="../home.php" class="logo"><img src="../images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">PatchPulse</a>
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
        <p class="updated"><?= htmlspecialchars($common['updated'], ENT_QUOTES, 'UTF-8') ?> <?= date('d/m/Y') ?></p>
        <?php pp_policy_render_blocks($page['blocks']); ?>
    </div>
</main>
<?php pp_lang_emit_js(); ?>
<script src="../script.js"></script>
</body>
</html>
