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

// Redirect se già loggato (no open redirect via HTTP_REFERER)
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// Genera CSRF token se non esiste
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$currentLang = pp_lang_current();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('auth.title_tag') ?></title>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
    background: linear-gradient(180deg, rgba(13,13,16,0.5) 0%, rgba(13,13,16,0.66) 55%, rgba(13,13,16,0.82) 100%); }

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

/* ─────────── MAIN / AUTH ─────────── */
.main-wrapper {
    flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden;
    background: transparent; color: var(--ink); position: relative; z-index: 1;
}
.main-wrapper.auth-page { display: flex; align-items: flex-start; justify-content: center; padding: clamp(2rem, 6vh, 4.5rem) clamp(1rem, 4vw, 2rem); }

.auth-container {
    width: 100%; max-width: 430px; margin: auto;
    background: rgba(18,18,22,0.72); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--line); border-radius: 0; padding: clamp(1.8rem, 5vw, 2.8rem);
    animation: fadeUp .5s cubic-bezier(.16,1,.3,1) both;
}
@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }

.auth-header { margin-bottom: 1.8rem; }
.auth-header h1 { font-family: var(--disp); font-weight: 500; font-size: clamp(1.8rem, 4vw, 2.3rem); line-height: 1.1; letter-spacing: -.02em; color: var(--ink); margin-bottom: .45rem; }
.auth-header p { color: var(--mut); font-size: .95rem; line-height: 1.5; }

.message { background: rgba(157,134,255,0.08); border: 1px solid rgba(157,134,255,0.22); border-radius: 0; padding: .8rem 1rem; color: var(--violet-2); font-size: .9rem; margin-bottom: 1.4rem; line-height: 1.5; }

.auth-form { display: none; }
.auth-form.active { display: block; animation: fadeIn .3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.form-group { margin-bottom: 1.1rem; }
.form-group label { display: block; margin-bottom: .4rem; color: var(--ink-2); font-size: .85rem; font-weight: 500; }
.form-group input { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid var(--line); border-radius: 0; color: var(--ink); font-family: var(--sans); font-size: .98rem; padding: .8rem 1rem; transition: border-color .2s, box-shadow .2s; }
.form-group input::placeholder { color: var(--mut); }
.form-group input:focus { outline: none; border-color: var(--violet); box-shadow: 0 0 0 3px rgba(157,134,255,0.18); }

.password-toggle { position: relative; }
.password-toggle input { padding-right: 2.8rem; }
.password-toggle button { position: absolute; right: .7rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1rem; line-height: 1; color: var(--mut); padding: 0; }
.password-toggle button:hover { color: var(--violet); }
.password-toggle button svg { width: 18px; height: 18px; display: block; }

.remember-me { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.2rem; }
.remember-me input[type="checkbox"] { accent-color: var(--violet); width: 16px; height: 16px; cursor: pointer; }
.remember-me label { color: var(--ink-2); font-size: .88rem; cursor: pointer; }

.terms-container { display: flex; align-items: flex-start; gap: .75rem; margin-bottom: 1.2rem; padding: 1rem; background: rgba(255,255,255,0.03); border: 1px solid var(--line); border-radius: 0; }
.terms-text p { font-size: .85rem; color: var(--ink-2); line-height: 1.5; }
.terms-text a { color: var(--violet-2); text-decoration: none; font-weight: 500; }
.terms-text a:hover { text-decoration: underline; }
.terms-checkbox { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--violet); cursor: pointer; margin-top: 2px; }

.cf-turnstile { margin: 1rem 0; }
.cf-turnstile iframe { max-width: 100% !important; }

.form-submit { width: 100%; padding: .9rem; background: var(--violet); color: #14121f; border: none; border-radius: 0; font-family: var(--sans); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background .2s, transform .15s; margin-top: .3rem; }
.form-submit:hover { background: var(--violet-2); }
.form-submit:active { transform: translateY(1px); }

.forgot-password { text-align: center; margin-top: 1rem; }
.forgot-password a { color: var(--mut); font-size: .85rem; text-decoration: none; transition: color .2s; }
.forgot-password a:hover { color: var(--violet); }

.form-toggle { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--line); flex-wrap: wrap; }
.form-toggle p { color: var(--mut); font-size: .9rem; }
.form-toggle button { background: none; border: none; color: var(--violet-2); font-family: var(--sans); font-size: .9rem; font-weight: 600; cursor: pointer; transition: color .2s; padding: 0; }
.form-toggle button:hover { color: var(--violet); }

.back-to-home { text-align: center; margin-top: 1rem; }
.back-to-home a { color: var(--faint); font-size: .85rem; text-decoration: none; transition: color .2s; }
.back-to-home a:hover { color: var(--violet); }

/* ─────────── RESPONSIVE ─────────── */
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
        <a href="extension.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.5 11H19V7a2 2 0 0 0-2-2h-4V3.5a2.5 2.5 0 0 0-5 0V5H4a2 2 0 0 0-2 2v3.8h1.5a2.2 2.2 0 0 1 0 4.4H2V19a2 2 0 0 0 2 2h3.8v-1.5a2.2 2.2 0 0 1 4.4 0V21H17a2 2 0 0 0 2-2v-4h1.5a2.5 2.5 0 0 0 0-5z"/></svg></span><?= t('ext.eyebrow') ?>
        </a>
    </div>
    <div class="nav-divider"></div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span><?= t('nav.faq') ?>
        </a>
        <a href="log-reg.php" class="nav-item active">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span><?= t('nav.login_register') ?>
        </a>
        <a href="settings.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?>
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper auth-page" id="main">
    <div class="auth-container">

        <div class="auth-header">
            <h1 id="form-title" data-i18n-login="<?= t('auth.login.title') ?>" data-i18n-register="<?= t('auth.register.title') ?>" data-i18n-forgot="<?= t('auth.forgot.title') ?>"><?= t('auth.login.title') ?></h1>
            <p id="form-subtitle" data-i18n-login="<?= t('auth.login.subtitle') ?>" data-i18n-register="<?= t('auth.register.subtitle') ?>" data-i18n-forgot="<?= t('auth.forgot.subtitle') ?>"><?= t('auth.login.subtitle') ?></p>
        </div>

        <!-- Messages -->
        <?php if (!empty($_SESSION['login_message'])): ?>
            <div class="message"><?= t($_SESSION['login_message']) ?></div>
            <?php unset($_SESSION['login_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['registration_message'])): ?>
            <div class="message"><?= t($_SESSION['registration_message']) ?></div>
            <?php unset($_SESSION['registration_message']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="login-form" class="auth-form active" action="dataBase/login_user.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="EmailOfUser"><?= t('auth.email') ?></label>
                <input type="email" id="EmailOfUser" name="EmailOfUser" placeholder="<?= t('auth.email_placeholder') ?>" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="PasswordOfUserUnCrypt"><?= t('auth.password') ?></label>
                <div class="password-toggle">
                    <input type="password" id="PasswordOfUserUnCrypt" name="PasswordOfUserUnCrypt" placeholder="<?= t('auth.password_placeholder') ?>" autocomplete="current-password" required>
                    <button type="button" data-toggle-password="PasswordOfUserUnCrypt"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember-me" name="remember">
                <label for="remember-me"><?= t('auth.remember_me') ?></label>
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="dark"></div>
            <button type="submit" class="form-submit"><?= t('auth.login_btn') ?></button>
            <div class="forgot-password">
                <a href="#" data-action="show-forgot"><?= t('auth.forgot_password') ?></a>
            </div>
        </form>

        <!-- Registration Form -->
        <form id="register-form" class="auth-form" action="dataBase/save_user.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="NameOfUser"><?= t('auth.username') ?></label>
                <input type="text" id="NameOfUser" name="NameOfUser" placeholder="<?= t('auth.username_placeholder') ?>" required>
            </div>
            <div class="form-group">
                <label for="EmailOfUserReg"><?= t('auth.email') ?></label>
                <input type="email" id="EmailOfUserReg" name="EmailOfUser" placeholder="<?= t('auth.email_placeholder') ?>" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="PasswordOfUserUnCryptReg"><?= t('auth.password') ?></label>
                <div class="password-toggle">
                    <input type="password" id="PasswordOfUserUnCryptReg" name="PasswordOfUserUnCrypt" placeholder="<?= t('auth.password_create') ?>" autocomplete="new-password" required>
                    <button type="button" data-toggle-password="PasswordOfUserUnCryptReg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm-password"><?= t('auth.password_confirm') ?></label>
                <div class="password-toggle">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="<?= t('auth.password_confirm_ph') ?>" autocomplete="new-password" required>
                    <button type="button" data-toggle-password="confirm-password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <div class="terms-container">
                <div class="terms-text">
                    <p><?= t('auth.terms_pre') ?></p>
                    <p><a href="policy/terms&condition.php" id="termsLink"><?= t('auth.terms_link') ?></a> <?= t('auth.terms_and') ?> <a href="policy/privacy_policy.php"><?= t('auth.privacy_link') ?></a></p>
                </div>
                <input type="checkbox" name="AgreeTerms" id="AgreeTerms" required class="terms-checkbox">
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="dark"></div>
            <button type="submit" class="form-submit"><?= t('auth.register_btn') ?></button>
        </form>

        <!-- Forgot Password Form -->
        <form id="forgot-form" class="auth-form" action="dataBase/forgot_password.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="forgot-email"><?= t('auth.email') ?></label>
                <input type="email" id="forgot-email" name="EmailOfUser" placeholder="<?= t('auth.email_placeholder') ?>" autocomplete="email" required>
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="dark"></div>
            <button type="submit" class="form-submit"><?= t('auth.forgot_send') ?></button>
        </form>

        <!-- Form Toggle -->
        <div class="form-toggle">
            <p id="toggle-text" data-i18n-login="<?= t('auth.toggle_to_register') ?>" data-i18n-register="<?= t('auth.toggle_to_login') ?>"><?= t('auth.toggle_to_register') ?></p>
            <button type="button" id="toggle-btn" data-i18n-login="<?= t('auth.toggle_to_register_btn') ?>" data-i18n-register="<?= t('auth.toggle_to_login_btn') ?>"><?= t('auth.toggle_to_register_btn') ?></button>
        </div>

        <!-- Back to Home -->
        <div class="back-to-home">
            <a href="home.php"><?= t('nav.back_homepage') ?></a>
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
