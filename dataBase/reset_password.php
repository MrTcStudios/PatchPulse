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
require_once __DIR__ . "/../security/session_guard.php";

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(t('flash.internal_error', false));
}

$error = '';
$success = '';
$showForm = false;

// Validazione token (64 hex chars)
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = t('reset.link_invalid');
} else {
    // Il DB conserva l'hash del token: confronta l'hash dell'input.
    $tokenHash = hash('sha256', $token);

    // Verifica token nel DB
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = t('reset.link_expired');
        $stmt->close();
    } else {
        $stmt->bind_result($userId, $userName, $userEmail);
        $stmt->fetch();
        $stmt->close();
        $showForm = true;
    }
}

// Gestione POST — nuovo password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    // CSRF
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = t('flash.invalid_request');
        $showForm = false;
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || empty($confirmPassword)) {
            $error = t('flash.fill_all_fields');
        } elseif ($newPassword !== $confirmPassword) {
            $error = t('flash.register.pwd_mismatch');
        } elseif (mb_strlen($newPassword) < 8) {
            $error = t('flash.register.weak_pwd');
        } elseif (mb_strlen($newPassword) > 128) {
            $error = t('flash.register.long_pwd');
        } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $error = t('flash.register.pwd_complexity');
        } else {
            // Aggiorna password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            // Il reset revoca anche ogni richiesta pendente creata con le vecchie
            // credenziali (cambio email / eliminazione account): dopo il recupero
            // non deve sopravvivere nessun token di controllo dell'account.
            // confirm_email_change.php applica il cambio col SOLO token, quindi
            // senza questa pulizia resterebbe applicabile fino a 1h dopo il reset.
            $update = @$conn->prepare(
                "UPDATE users SET password = ?,
                    reset_token = NULL,    reset_token_expires = NULL,
                    temp_email = NULL,     email_change_token = NULL, email_change_expires = NULL,
                    deletion_token = NULL, deletion_token_expires = NULL
                 WHERE id = ? AND reset_token = ?"
            );
            if (!$update) { // colonne email_change/deletion assenti (migration non applicata)
                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ? AND reset_token = ?");
            }
            $update->bind_param("sis", $hashedPassword, $userId, $tokenHash);
            $update->execute();

            if ($update->affected_rows > 0) {
                // Password cambiata → invalida TUTTE le sessioni attive
                // dell'utente (nessuna sessione qui: il reset avviene via link).
                pp_bump_auth_epoch($conn, (int)$userId);

                // Log
                $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                if (!filter_var($userIp, FILTER_VALIDATE_IP)) $userIp = 'unknown';
                $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'password_reset_completed', ?)");
                $log->bind_param("is", $userId, $userIp);
                $log->execute();
                $log->close();

                require_once __DIR__ . '/../PHPMailer/src/Exception.php';
                require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
                require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
                $notify = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $notify->isSMTP();
                    $notify->Host       = getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com';
                    $notify->SMTPAuth   = true;
                    $notify->Username   = getenv('SMTP_USER');
                    $notify->Password   = getenv('SMTP_PASS');
                    $notify->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $notify->Port       = 587;
                    $notify->Timeout    = 10;
                    $notify->setFrom(getenv('SMTP_FROM') ?: 'support@patchpulse.org', 'PatchPulse');
                    $notify->addAddress($userEmail);
                    $safeName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
                    $safeIp   = htmlspecialchars($userIp, ENT_QUOTES, 'UTF-8');
                    $notify->isHTML(true);
                    $notify->Subject = t('mail.pwd_changed.subject', false);
                    $notify->Body    = str_replace(['{0}', '{1}'], [$safeName, $safeIp], t('mail.pwd_changed.body', false));
                    $notify->send();
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    error_log("Reset password notification error: " . $notify->ErrorInfo);
                }

                $success = t('reset.success');
                $showForm = false;
            } else {
                $error = t('reset.failed');
                $showForm = false;
            }
            $update->close();
        }
    }
}

// CSRF token per il form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= t('reset.title_tag') ?></title>
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
        .reset-wrap { position:relative; z-index:1; width:100%; max-width:460px; display:flex; flex-direction:column; align-items:center; gap:1.6rem; }
        .brand { display:flex; align-items:center; gap:.6rem; text-decoration:none; color:#fff; font-weight:600; font-size:1.15rem; letter-spacing:-0.02em; }
        .brand img { width:34px; height:34px; object-fit:contain; }
        .reset-card { width:100%; background:rgba(157,134,255,0.04); border:1px solid rgba(157,134,255,0.28); padding:clamp(1.8rem,4vw,2.6rem); }
        .reset-card h1 { font-family:var(--disp); font-optical-sizing:auto; font-weight:500; font-size:clamp(1.7rem,4.5vw,2.2rem); line-height:1.08; letter-spacing:-.02em; color:var(--ink); margin-bottom:.5rem; }
        .reset-card .subtitle { color:var(--ink-2); font-size:.95rem; line-height:1.6; margin-bottom:1.6rem; }
        .form-group { margin-bottom:1.1rem; }
        .form-group label { display:block; margin-bottom:.4rem; color:var(--ink-2); font-size:.85rem; font-weight:500; }
        .form-group input { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--line); border-radius:0; color:var(--ink); font-family:var(--sans); font-size:.98rem; padding:.8rem 1rem; transition:border-color .2s, box-shadow .2s; }
        .form-group input::placeholder { color:var(--mut); }
        .form-group input:focus { outline:none; border-color:var(--violet); box-shadow:0 0 0 3px rgba(157,134,255,0.18); }
        .form-submit { width:100%; padding:.9rem; background:var(--violet); color:#14121f; border:none; border-radius:0; font-family:var(--sans); font-size:1rem; font-weight:600; cursor:pointer; transition:background .2s, transform .15s; margin-top:.3rem; }
        .form-submit:hover { background:var(--violet-2); }
        .form-submit:active { transform:translateY(1px); }
        .msg { padding:.8rem 1rem; border-radius:0; margin-bottom:1.4rem; font-size:.9rem; line-height:1.5; }
        .msg-err { background:rgba(224,90,90,0.08); color:#e8908f; border:1px solid rgba(224,90,90,0.28); }
        .msg-ok { background:rgba(157,134,255,0.08); color:var(--violet-2); border:1px solid rgba(157,134,255,0.22); }
        .back-link { text-align:center; margin-top:1.3rem; }
        .back-link a { color:var(--mut); text-decoration:none; font-size:.88rem; transition:color .2s; }
        .back-link a:hover { color:var(--violet); }
        .foot { position:relative; z-index:1; font-size:.76rem; color:var(--faint); }
    </style>
</head>
<body>
    <div id="vanta-bg"></div>
    <div id="scrim"></div>

    <main class="reset-wrap">
        <a href="/home.php" class="brand">
            <img src="/images/PatchPulseLogo.svg" alt="PatchPulse">
            PatchPulse
        </a>
        <div class="reset-card">
            <h1><?= t('reset.title') ?></h1>

            <?php if ($error): ?>
                <div class="msg msg-err"><?= $error /* already escaped by t() */ ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="msg msg-ok"><?= $success /* already escaped by t() */ ?></div>
                <div class="back-link"><a href="/log-reg.php#login"><?= t('reset.go_login') ?></a></div>
            <?php endif; ?>

            <?php if ($showForm): ?>
                <p class="subtitle"><?= t('reset.subtitle') ?></p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label for="new_password"><?= t('reset.new_password') ?></label>
                        <input type="password" id="new_password" name="new_password" placeholder="<?= t('reset.new_password_ph') ?>" autocomplete="new-password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><?= t('reset.confirm_password') ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="<?= t('reset.confirm_password_ph') ?>" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="form-submit"><?= t('reset.submit') ?></button>
                </form>
            <?php endif; ?>

            <?php if (!$showForm && !$success): ?>
                <div class="back-link"><a href="/log-reg.php"><?= t('reset.back_login') ?></a></div>
            <?php endif; ?>
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
