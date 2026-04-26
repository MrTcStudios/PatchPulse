<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("config.php");

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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Login & Registrazione</title>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
            <button class="bell-btn" title="Notifiche" aria-label="Notifiche">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search" aria-label="Search">
        <span class="search-shortcut">S</span>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Applications
        </a>
        <a href="browser-scan.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Browser Scanner
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Website Overview
        </a>
        <a href="vpn-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>VPN Checker
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Data Breach Monitor
        </a>
    </div>
    <div class="nav-divider"></div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>FAQ
        </a>
        <a href="log-reg.php" class="nav-item active">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>Login / Register
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>Settings
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper auth-page" id="main">
    <div class="auth-container">

        <div class="auth-header">
            <h1 id="form-title">Accedi</h1>
            <p id="form-subtitle">Benvenuto di nuovo su PatchPulse</p>
        </div>

        <!-- Messages -->
        <?php if (!empty($_SESSION['login_message'])): ?>
            <div class="message"><?php echo htmlspecialchars($_SESSION['login_message']); ?></div>
            <?php unset($_SESSION['login_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['registration_message'])): ?>
            <div class="message"><?php echo htmlspecialchars($_SESSION['registration_message']); ?></div>
            <?php unset($_SESSION['registration_message']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="login-form" class="auth-form active" action="dataBase/login_user.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="EmailOfUser">Email</label>
                <input type="email" id="EmailOfUser" name="EmailOfUser" placeholder="Inserisci la tua email" required>
            </div>
            <div class="form-group">
                <label for="PasswordOfUserUnCrypt">Password</label>
                <div class="password-toggle">
                    <input type="password" id="PasswordOfUserUnCrypt" name="PasswordOfUserUnCrypt" placeholder="Inserisci la tua password" required>
                    <button type="button" data-toggle-password="PasswordOfUserUnCrypt">👁️</button>
                </div>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember-me" name="remember">
                <label for="remember-me">Ricordami</label>
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="light"></div>
            <button type="submit" class="form-submit">Accedi</button>
            <div class="forgot-password">
                <a href="#" data-action="show-forgot">Hai dimenticato la password?</a>
            </div>
        </form>

        <!-- Registration Form -->
        <form id="register-form" class="auth-form" action="dataBase/save_user.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="NameOfUser">Nome Utente</label>
                <input type="text" id="NameOfUser" name="NameOfUser" placeholder="Inserisci il tuo username" required>
            </div>
            <div class="form-group">
                <label for="EmailOfUserReg">Email</label>
                <input type="email" id="EmailOfUserReg" name="EmailOfUser" placeholder="Inserisci la tua email" required>
            </div>
            <div class="form-group">
                <label for="PasswordOfUserUnCryptReg">Password</label>
                <div class="password-toggle">
                    <input type="password" id="PasswordOfUserUnCryptReg" name="PasswordOfUserUnCrypt" placeholder="Crea una password" required>
                    <button type="button" data-toggle-password="PasswordOfUserUnCryptReg">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm-password">Conferma Password</label>
                <div class="password-toggle">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Conferma la password" required>
                    <button type="button" data-toggle-password="confirm-password">👁️</button>
                </div>
            </div>
            <div class="terms-container">
                <div class="terms-text">
                    <p>Ho letto e accetto i</p>
                    <p><a href="policy/terms&condition.php" id="termsLink">Termini di Servizio</a> e la <a href="policy/privacy_policy.php">Privacy Policy</a></p>
                </div>
                <input type="checkbox" name="AgreeTerms" id="AgreeTerms" required class="terms-checkbox">
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="light"></div>
            <button type="submit" class="form-submit">Registrati</button>
        </form>

        <!-- Forgot Password Form -->
        <form id="forgot-form" class="auth-form" action="dataBase/forgot_password.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="forgot-email">Email</label>
                <input type="email" id="forgot-email" name="EmailOfUser" placeholder="Inserisci la tua email" required>
            </div>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="light"></div>
            <button type="submit" class="form-submit">Invia Link di Reset</button>
        </form>

        <!-- Form Toggle -->
        <div class="form-toggle">
            <p id="toggle-text">Non hai un account?</p>
            <button type="button" id="toggle-btn">Registrati qui</button>
        </div>

        <!-- Back to Home -->
        <div class="back-to-home">
            <a href="home.php">← Torna alla Homepage</a>
        </div>

    </div>
</main>

<script src="script.js"></script>
</body>
</html>
