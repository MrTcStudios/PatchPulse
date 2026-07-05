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
    $_SESSION['registration_message'] = "flash.internal_error_retry";
    header("Location: ../log-reg.php");
    exit();
}

$token = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['token'] ?? '')
    : ($_GET['token'] ?? '');

// Validazione token (32 bytes = 64 hex chars)
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['registration_message'] = "flash.email.link_invalid";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

// Il DB conserva l'hash del token: confronta l'hash dell'input.
$tokenHash = hash('sha256', $token);

// Cerca token e verifica scadenza in un'unica query atomica
$stmt = $conn->prepare("SELECT id FROM users WHERE deletion_token = ? AND deletion_token_expires > NOW()");
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['registration_message'] = "flash.account.confirm_link_invalid";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conn->close();
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    $lang = htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= t('account.confirm_delete.title_tag') ?></title>
    <link rel="stylesheet" href="/css/fonts/primary.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --bg:#0d0d10; --ink:#ece9e1; --ink-2:#b4afa3; --mut:#87837a; --faint:#5e5b54;
            --line:rgba(255,255,255,0.11); --violet:#9d86ff; --violet-2:#b7a6ff; --red:#e8736b;
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
        .confirm-card { width:100%; background:rgba(232,115,107,0.04); border:1px solid rgba(232,115,107,0.28); padding:clamp(1.8rem,4vw,2.6rem); text-align:center; }
        .confirm-card h1 { font-family:var(--disp); font-optical-sizing:auto; font-weight:500; font-size:clamp(1.8rem,4.5vw,2.4rem); line-height:1.05; letter-spacing:-.02em; color:var(--ink); margin-bottom:1rem; }
        .confirm-card p { color:var(--ink-2); font-size:.95rem; line-height:1.6; margin-bottom:1rem; }
        .confirm-card p.warn { color:var(--red); font-weight:600; margin-bottom:1.4rem; }
        .btn-danger { width:100%; padding:.85rem 1.5rem; background:var(--red); color:#1a0f0e; border:none; border-radius:0; font-family:var(--sans); font-size:.9rem; font-weight:700; cursor:pointer; transition:background .2s, transform .15s; }
        .btn-danger:hover { background:#ef8b84; }
        .btn-danger:active { transform:translateY(1px); }
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
            <h1><?= t('account.confirm_delete.heading') ?></h1>
            <p><?= t('account.confirm_delete.body') ?></p>
            <p class="warn"><?= t('account.confirm_delete.warning') ?></p>
            <form method="post" action="confirm_deletion.php">
                <input type="hidden" name="token" value="<?= $safeToken ?>">
                <button type="submit" class="btn-danger"><?= t('account.confirm_delete.button') ?></button>
            </form>
            <a class="cancel" href="/account.php"><?= t('account.confirm_delete.cancel') ?></a>
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

// ── POST: esegui l'eliminazione dell'account (transazione invariata) ──
$conn->begin_transaction();
try {
    // Pulisci log
    $del_logs = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $del_logs->bind_param("i", $user_id);
    $del_logs->execute();
    $del_logs->close();

    // Pulisci scansioni (questa tabella non ha un ON DELETE CASCADE garantito)
    $del_scans = $conn->prepare("DELETE FROM scans WHERE user_id = ?");
    if ($del_scans) {
        $del_scans->bind_param("i", $user_id);
        $del_scans->execute();
        $del_scans->close();
    }

    // Pulisci i domini verificati: senza questo restano righe orfane con
    // dominio + token riconducibili all'utente cancellato (erasure completa).
    $del_dom = $conn->prepare("DELETE FROM verified_domains WHERE user_id = ?");
    if ($del_dom) {
        $del_dom->bind_param("i", $user_id);
        $del_dom->execute();
        $del_dom->close();
    }

    // Elimina utente
    $del_user = $conn->prepare("DELETE FROM users WHERE id = ? AND deletion_token = ?");
    $del_user->bind_param("is", $user_id, $tokenHash);
    $del_user->execute();

    if ($del_user->affected_rows === 0) {
        throw new \Exception("Delete failed");
    }
    $del_user->close();

    $conn->commit();
} catch (\Exception $e) {
    $conn->rollback();
    $_SESSION['registration_message'] = "flash.account.delete_failed";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$conn->close();

// Distruggi sessione
session_unset();
session_destroy();

// Nuova sessione per il messaggio
session_start();
$_SESSION['login_message'] = "flash.account.deleted";
header("Location: ../log-reg.php");
exit();
