<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("config.php");
require_once __DIR__ . "/lang/lang.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: log-reg.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$user_id = (int)$_SESSION['user_id'];

// Verifica utente esiste
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    session_destroy();
    header("Location: home.php");
    exit();
}

$stmt->bind_result($uid, $userName, $userEmail);
$stmt->fetch();
$stmt->close();

// Storage calc
$stmt = $conn->prepare("
    SELECT id, cookiesEnabled, doNotTrack, browserFingerprinting,
        webrtcSupport, httpsOnly, adBlockEnabled, javascriptStatus,
        webglFingerprinting, developerMode, webAssemblySupport, webWorkersSupported,
        mediaQueriesSupported, webNotificationsSupported, permissionsAPISupported,
        paymentRequestAPISupported, htmlCssSupport, geolocationInfo, sensorsSupported,
        publicIpv4, publicIpv6, browserType, browserVersion,
        browserLanguage, osVersion, incognitoMode, deviceMemory, cpuThreads, cpuCores,
        gpuName, colorDepth, pixelDepth, touchSupport, screenResolution, mimeTypes,
        referrerPolicy, batteryStatus, securityProtocols, created_at
    FROM scans WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$storage_used = 0;
$scan_count = 0;
while ($row = $result->fetch_assoc()) {
    $scan_count++;
    foreach ($row as $value) {
        if ($value !== null) $storage_used += strlen($value);
    }
}
$storage_used += $scan_count * 100;
$storage_used = round($storage_used / (1024 * 1024), 2);
$total_storage = 1000;
$storage_percentage = min(100, round(($storage_used / $total_storage) * 100, 1));

$stmt->close();

// Messages
$msg_password = $_SESSION['password_change_message'] ?? '';
$msg_email = $_SESSION['email_change_message'] ?? '';
$msg_delete = $_SESSION['account_delete_message'] ?? '';
$msg_general = $_SESSION['account_message'] ?? '';
unset($_SESSION['password_change_message'], $_SESSION['email_change_message'], $_SESSION['account_delete_message'], $_SESSION['account_message']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('account.title_tag') ?></title>
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
    --violet-2:  #b7a6ff;
    --red:       #e8736b;
    --amber:     #e3b457;

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
    background: linear-gradient(180deg, rgba(13,13,16,0.46) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

/* ─────────── SIDEBAR ─────────── */
.sidebar { width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border); z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans); }
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

/* ─────────── MAIN ─────────── */
.main-wrapper { flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden; background: transparent; color: var(--ink); position: relative; z-index: 1; scroll-behavior: smooth; }

.page-header { max-width: 920px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.2rem, 3vw, 2rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 0; }

.scanner-section { max-width: 920px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }

.acc-msg { padding: .8rem 1rem; border-radius: 0; font-size: .88rem; margin-bottom: 1rem; background: rgba(157,134,255,0.08); color: var(--violet-2); border: 1px solid rgba(157,134,255,0.22); }

.acc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4rem; }
.acc-card { background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; padding: 1.8rem; }
.acc-card.full { grid-column: 1 / -1; }
.acc-card h3 { font-family: var(--disp); font-size: 1.2rem; font-weight: 500; color: var(--ink); margin-bottom: 1.1rem; display: flex; align-items: center; gap: .5rem; letter-spacing: -.01em; }
.acc-card h3 .icon { display: inline-flex; color: var(--violet); line-height: 0; }
.acc-card h3 .icon svg { width: 18px; height: 18px; display: block; }
.danger-card h3 .icon { color: var(--red); }

.profile-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.profile-avatar { width: 52px; height: 52px; border-radius: 50%; background: var(--violet); color: #14121f; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.3rem; flex-shrink: 0; }
.profile-name { font-weight: 600; color: var(--ink); font-size: 1.05rem; }
.profile-email { color: var(--mut); font-size: .88rem; }

.storage-bar-bg { background: rgba(255,255,255,0.08); border-radius: 20px; height: 8px; overflow: hidden; margin: .8rem 0 .5rem; }
.storage-bar-fill { height: 100%; background: var(--violet); border-radius: 20px; transition: width .6s; }
.storage-text { font-size: .82rem; color: var(--mut); }

.acc-input { width: 100%; padding: .75rem 1rem; background: rgba(255,255,255,0.04); border: 1px solid var(--line); border-radius: 0; color: var(--ink); font-size: .92rem; font-family: var(--sans); margin-bottom: .7rem; transition: border-color .2s, box-shadow .2s; }
.acc-input:focus { outline: none; border-color: var(--violet); box-shadow: 0 0 0 3px rgba(157,134,255,0.18); }
.acc-input::placeholder { color: var(--mut); }

.acc-btn { padding: .75rem 1.5rem; background: var(--violet); color: #14121f; border: none; border-radius: 0; font-family: var(--sans); font-size: .88rem; font-weight: 600; cursor: pointer; transition: background .2s, transform .15s; width: 100%; margin-top: .3rem; }
.acc-btn:hover { background: var(--violet-2); }
.acc-btn:active { transform: translateY(1px); }
.acc-btn.danger { background: var(--red); color: #1a0f0e; }
.acc-btn.danger:hover { background: #ef8b84; }
.acc-btn.warning { background: var(--amber); color: #1f1707; }
.acc-btn.warning:hover { background: #ecc472; }

.acc-msg, .log-list, .scan-list { }
.log-list { max-height: 320px; overflow-y: auto; }
.log-row { display: flex; align-items: center; gap: .6rem; padding: .6rem .8rem; border-radius: 0; margin-bottom: .35rem; background: rgba(255,255,255,0.03); font-size: .85rem; }
.log-row .log-date { color: var(--violet-2); font-weight: 600; font-size: .78rem; white-space: nowrap; }
.log-row .log-action { color: var(--ink-2); flex: 1; }
.log-row .log-ip { color: var(--mut); font-family: var(--mono); font-size: .78rem; }

.scan-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem .8rem; border-radius: 0; margin-bottom: .35rem; background: rgba(255,255,255,0.03); }
.scan-row .scan-date { color: var(--ink-2); font-size: .85rem; }
.scan-row a { color: var(--violet-2); text-decoration: none; font-weight: 500; font-size: .85rem; }
.scan-row a:hover { text-decoration: underline; }

.danger-card { border-color: rgba(232,115,107,0.28) !important; background: rgba(232,115,107,0.03) !important; }
.danger-card h3 { color: var(--red) !important; }
.danger-warning { color: var(--red); font-size: .85rem; margin-bottom: 1rem; }
.danger-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }

.logout-link { display: inline-flex; align-items: center; gap: .4rem; color: var(--red); text-decoration: none; font-size: .88rem; font-weight: 500; padding: .5rem 0; transition: opacity .2s; }
.logout-link:hover { opacity: .7; }

/* footer */
.foot { max-width: 920px; margin: 0 auto; border-top: 1px solid var(--line); padding: 1.5rem clamp(1.5rem, 5vw, 3rem) 3rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
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
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 55vh; overflow-y: auto; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .acc-grid { grid-template-columns: 1fr; }
    .danger-grid { grid-template-columns: 1fr; }
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
        <div style="display:flex;align-items:center;gap:0.5rem;">
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
        <a href="home.php#home" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?></a>
        <a href="home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><?= t('nav.applications') ?></a>
    </div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span><?= t('nav.faq') ?></a>
        <a href="account.php" class="nav-item active"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><?= t('nav.account') ?></a>
        <a href="settings.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?></a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">

    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            <?= t('nav.back_home') ?>
        </a>
        <p class="page-header-eyebrow"><?= t('account.eyebrow') ?></p>
        <h1 class="page-header-title"><?= t('account.title') ?></h1>
    </div>

    <div class="scanner-section">

        <?php if ($msg_password): ?><div class="acc-msg"><?= t($msg_password) ?></div><?php endif; ?>
        <?php if ($msg_email): ?><div class="acc-msg"><?= t($msg_email) ?></div><?php endif; ?>
        <?php if ($msg_delete): ?><div class="acc-msg"><?= t($msg_delete) ?></div><?php endif; ?>
        <?php if ($msg_general): ?><div class="acc-msg"><?= t($msg_general) ?></div><?php endif; ?>

        <div class="acc-grid">

            <!-- Profile -->
            <div class="acc-card">
                <div class="profile-header">
                    <div class="profile-avatar"><?= strtoupper(mb_substr(htmlspecialchars($userName), 0, 1)) ?></div>
                    <div>
                        <div class="profile-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="profile-email"><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
                <div class="storage-bar-bg"><div class="storage-bar-fill" style="width:<?= $storage_percentage ?>%"></div></div>
                <div class="storage-text"><?= $storage_used ?> MB / <?= $total_storage ?> MB &middot; <?= $scan_count ?> <?= t('account.profile.storage_sep') ?></div>
                <br>
                <form action="logout.php" method="post" style="margin:0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="logout-link" style="background:none;border:none;cursor:pointer;font-family:inherit">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <?= t('account.profile.logout') ?>
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="acc-card">
                <h3><span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span> <?= t('account.password.title') ?></h3>
                <form action="dataBase/change_password.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="password" class="acc-input" name="current_password" placeholder="<?= t('account.password.current') ?>" autocomplete="current-password" required>
                    <input type="password" class="acc-input" name="new_password" placeholder="<?= t('account.password.new') ?>" autocomplete="new-password" required minlength="8">
                    <input type="password" class="acc-input" name="confirm_new_password" placeholder="<?= t('account.password.confirm') ?>" autocomplete="new-password" required>
                    <button type="submit" name="change_password" class="acc-btn"><?= t('account.password.update') ?></button>
                </form>
            </div>

            <!-- Change Email -->
            <div class="acc-card">
                <h3><span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></span> <?= t('account.email.title') ?></h3>
                <form action="dataBase/change_email.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="password" class="acc-input" name="current_password" placeholder="<?= t('account.password.current') ?>" autocomplete="current-password" required>
                    <input type="email" class="acc-input" name="new_email" placeholder="<?= t('account.email.new') ?>" autocomplete="email" required>
                    <button type="submit" name="change_email" class="acc-btn"><?= t('account.email.update') ?></button>
                </form>
            </div>

            <!-- Activity Logs -->
            <div class="acc-card">
                <h3><span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg></span> <?= t('account.logs.title') ?></h3>
                <div class="log-list">
                    <?php
                    $log_stmt = $conn->prepare("SELECT action, ip_address, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
                    $log_stmt->bind_param("i", $user_id);
                    $log_stmt->execute();
                    $log_stmt->bind_result($l_action, $l_ip, $l_date);
                    $has_logs = false;
                    while ($log_stmt->fetch()):
                        $has_logs = true;
                    ?>
                    <div class="log-row">
                        <span class="log-date"><?= htmlspecialchars($l_date) ?></span>
                        <span class="log-action"><?= htmlspecialchars($l_action) ?></span>
                        <span class="log-ip"><?= htmlspecialchars($l_ip) ?></span>
                    </div>
                    <?php endwhile; $log_stmt->close(); ?>
                    <?php if (!$has_logs): ?><p style="color:var(--faint);font-size:0.88rem"><?= t('account.logs.empty') ?></p><?php endif; ?>
                </div>
                <form action="dataBase/clear_logs.php" method="post" style="margin-top:0.8rem">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit" class="acc-btn warning"><?= t('account.logs.clear') ?></button>
                </form>
            </div>

            <!-- Scans -->
            <div class="acc-card">
                <h3><span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span> <?= t('account.scans.title') ?></h3>
                <div class="log-list">
                    <?php
                    $s_stmt = $conn->prepare("SELECT id, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                    $s_stmt->bind_param("i", $user_id);
                    $s_stmt->execute();
                    $s_result = $s_stmt->get_result();
                    $has_scans = false;
                    while ($scan = $s_result->fetch_assoc()):
                        $has_scans = true;
                    ?>
                    <div class="scan-row">
                        <span class="scan-date"><?= htmlspecialchars($scan['created_at']) ?></span>
                        <a href="scans/scan_details.php?id=<?= (int)$scan['id'] ?>"><?= t('account.scans.details') ?></a>
                    </div>
                    <?php endwhile; $s_stmt->close(); ?>
                    <?php if (!$has_scans): ?><p style="color:var(--faint);font-size:0.88rem"><?= t('account.scans.empty') ?></p><?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="acc-card full danger-card">
                <h3><span class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span> <?= t('account.danger.title') ?></h3>
                <p class="danger-warning"><?= t('account.danger.warning') ?></p>
                <div class="danger-grid">
                    <form action="dataBase/delete_all_logs.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="acc-btn danger" data-confirm="<?= t('account.danger.delete_all_confirm') ?>"><?= t('account.danger.delete_all') ?></button>
                    </form>
                    <form action="dataBase/delete_account.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="password" class="acc-input" name="current_password" placeholder="<?= t('account.danger.delete_pwd') ?>" autocomplete="current-password" required>
                        <button type="submit" class="acc-btn danger" data-confirm="<?= t('account.danger.delete_acc_confirm') ?>"><?= t('account.danger.delete_acc') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
