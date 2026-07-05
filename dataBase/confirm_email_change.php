<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

require_once __DIR__ . "/../lang/lang.php";

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['email_change_message'] = "flash.internal_error_retry";
    header("Location: ../account.php");
    exit();
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$token  = $isPost ? ($_POST['token'] ?? '') : ($_GET['token'] ?? '');

// Validazione token (64 hex chars)
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['email_change_message'] = "flash.email.link_invalid";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Il DB conserva l'hash del token: confronta l'hash dell'input, con scadenza (1h).
$tokenHash = hash('sha256', $token);

// Cerca token e recupera la nuova email
$stmt = $conn->prepare("SELECT id, temp_email FROM users WHERE email_change_token = ? AND email_change_expires > NOW()");
if (!$stmt) {
    // Colonne non ancora presenti (migration email_change_columns.sql) o DB degradato.
    $_SESSION['email_change_message'] = "flash.email.link_expired";
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['email_change_message'] = "flash.email.link_expired";
    $stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$stmt->bind_result($user_id, $temp_email);
$stmt->fetch();
$stmt->close();

if (empty($temp_email)) {
    $_SESSION['email_change_message'] = "flash.email.no_pending";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// ── GET: nessuna modifica di stato. Mostra la pagina di conferma con un form POST. ──
if (!$isPost) {
    $conn->close();
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($temp_email, ENT_QUOTES, 'UTF-8');
    $lang = htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= t('account.confirm_email_change.title_tag') ?></title>
    <link rel="stylesheet" href="/css/fonts/primary.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --bg:#0d0d10; --ink:#ece9e1; --ink-2:#b4afa3; --mut:#87837a; --faint:#5e5b54;
            --line:rgba(255,255,255,0.11); --violet:#9d86ff; --violet-2:#b7a6ff;
            --disp:'Fraunces',Georgia,'Times New Roman',serif;
            --sans:'DM Sans',system-ui,-apple-system,sans-serif;
        }
        html { height:100%; }
        body { font-family:var(--sans); background:var(--bg); color:var(--ink); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2.5rem 1.2rem; -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
        a { color:inherit; }
        ::selection { background:var(--violet); color:#14121f; }
        :focus-visible { outline:2px solid var(--violet); outline-offset:3px; }
        #vanta-bg { position:fixed; inset:0; z-index:0; background:var(--bg); }
        #scrim { position:fixed; inset:0; z-index:0; pointer-events:none; background:linear-gradient(180deg, rgba(13,13,16,0.46) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }
        .confirm-wrap { position:relative; z-index:1; width:100%; max-width:520px; display:flex; flex-direction:column; align-items:center; gap:1.6rem; }
        .brand { display:flex; align-items:center; gap:.6rem; text-decoration:none; color:#fff; font-weight:600; font-size:1.15rem; letter-spacing:-0.02em; }
        .brand img { width:34px; height:34px; object-fit:contain; }
        .confirm-card { width:100%; background:rgba(157,134,255,0.04); border:1px solid rgba(157,134,255,0.28); padding:clamp(1.8rem,4vw,2.6rem); text-align:center; }
        .confirm-card h1 { font-family:var(--disp); font-optical-sizing:auto; font-weight:500; font-size:clamp(1.8rem,4.5vw,2.4rem); line-height:1.05; letter-spacing:-.02em; color:var(--ink); margin-bottom:1rem; }
        .confirm-card p { color:var(--ink-2); font-size:.95rem; line-height:1.6; margin-bottom:1.4rem; }
        .confirm-card p .new-email { color:var(--violet-2); font-weight:600; word-break:break-all; }
        .btn-confirm { width:100%; padding:.85rem 1.5rem; background:var(--violet); color:#14121f; border:none; border-radius:0; font-family:var(--sans); font-size:.9rem; font-weight:700; cursor:pointer; transition:background .2s, transform .15s; }
        .btn-confirm:hover { background:var(--violet-2); }
        .btn-confirm:active { transform:translateY(1px); }
        .cancel { display:inline-block; margin-top:1.2rem; color:var(--mut); text-decoration:none; font-size:.88rem; transition:color .2s; }
        .cancel:hover { color:var(--violet); }
        .foot { position:relative; z-index:1; font-size:.76rem; color:var(--faint); }
    </style>
</head>
<body>
    <div id="vanta-bg"></div>
    <div id="scrim"></div>

    <main class="confirm-wrap">
        <a href="/home.php" class="brand">
            <img src="/images/PatchPulseLogo.svg" alt="PatchPulse">
            PatchPulse
        </a>
        <div class="confirm-card">
            <h1><?= t('account.confirm_email_change.heading') ?></h1>
            <p><?php
                $bodyTpl = htmlspecialchars(t('account.confirm_email_change.body', false), ENT_QUOTES, 'UTF-8');
                echo str_replace('{0}', '<span class="new-email">' . $safeEmail . '</span>', $bodyTpl);
            ?></p>
            <form method="post" action="confirm_email_change.php">
                <input type="hidden" name="token" value="<?= $safeToken ?>">
                <button type="submit" class="btn-confirm"><?= t('account.confirm_email_change.button') ?></button>
            </form>
            <a class="cancel" href="/account.php"><?= t('account.confirm_email_change.cancel') ?></a>
        </div>
    </main>

    <footer class="foot">&copy; <?= date('Y') ?> PatchPulse</footer>

    <script src="/js/three.min.js"></script>
    <script src="/js/vanta.waves.min.js"></script>
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
    <?php
    exit();
}

// ── POST: applica il cambio email ──

// Verifica che la nuova email non sia nel frattempo stata presa
$check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check->bind_param("si", $temp_email, $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['email_change_message'] = "flash.email.taken_other";
    $check->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$check->close();

$update_stmt = $conn->prepare("UPDATE users SET email = ?, temp_email = NULL, email_change_token = NULL, email_change_expires = NULL WHERE id = ? AND email_change_token = ?");
$update_stmt->bind_param("sis", $temp_email, $user_id, $tokenHash);

if ($update_stmt->execute() && $update_stmt->affected_rows > 0) {
    // Aggiorna anche la sessione se è l'utente corrente
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        $_SESSION['email'] = $temp_email;
    }
    $_SESSION['email_change_message'] = "flash.email.changed";
} else {
    $_SESSION['email_change_message'] = "flash.email.update_failed";
}

$update_stmt->close();
$conn->close();

header("Location: ../account.php");
exit();
