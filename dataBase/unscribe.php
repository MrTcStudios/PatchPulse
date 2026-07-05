<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . "/../lang/lang.php";

$unsubscribeSecret = getenv('UNSUBSCRIBE_SECRET');
if (empty($unsubscribeSecret)) {
    http_response_code(500);
    echo t('unsub.service_unavailable');
    exit();
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$email  = filter_var(($isPost ? ($_POST['email'] ?? '') : ($_GET['email'] ?? '')), FILTER_SANITIZE_EMAIL);
$token  = $isPost ? ($_POST['token'] ?? '') : ($_GET['token'] ?? '');

// Validazione formale
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($token)) {
    http_response_code(400);
    echo t('unsub.link_invalid');
    exit();
}

// Verifica firma HMAC — senza questa chiunque potrebbe disiscrivere altri.
$expectedToken = hash_hmac('sha256', $email, $unsubscribeSecret);
if (!hash_equals($expectedToken, $token)) {
    http_response_code(403);
    echo t('unsub.link_expired');
    exit();
}

function unsub_render(string $bodyHtml): void {
    $lang = htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= t('unsub.title_tag') ?></title>
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
        .wrap { position:relative; z-index:1; width:100%; max-width:520px; display:flex; flex-direction:column; align-items:center; gap:1.6rem; }
        .brand { display:flex; align-items:center; gap:.6rem; text-decoration:none; color:#fff; font-weight:600; font-size:1.15rem; letter-spacing:-0.02em; }
        .brand img { width:34px; height:34px; object-fit:contain; }
        .card { width:100%; background:rgba(255,255,255,0.025); border:1px solid var(--line); padding:clamp(1.8rem,4vw,2.6rem); text-align:center; }
        .card h1 { font-family:var(--disp); font-optical-sizing:auto; font-weight:500; font-size:clamp(1.8rem,4.5vw,2.4rem); line-height:1.05; letter-spacing:-.02em; color:var(--ink); margin-bottom:1rem; }
        .card p { color:var(--ink-2); font-size:.95rem; line-height:1.6; margin-bottom:1.4rem; }
        .btn { width:100%; padding:.85rem 1.5rem; background:var(--violet); color:#14121f; border:none; border-radius:0; font-family:var(--sans); font-size:.9rem; font-weight:700; cursor:pointer; transition:background .2s, transform .15s; }
        .btn:hover { background:var(--violet-2); }
        .btn:active { transform:translateY(1px); }
        .cancel { display:inline-block; margin-top:1.2rem; color:var(--mut); text-decoration:none; font-size:.88rem; transition:color .2s; }
        .cancel:hover { color:var(--violet); }
        .foot { position:relative; z-index:1; font-size:.76rem; color:var(--faint); }
    </style>
</head>
<body>
    <div id="vanta-bg"></div>
    <div id="scrim"></div>
    <main class="wrap">
        <a href="/home.php" class="brand">
            <img src="/images/PatchPulseLogo.svg" alt="PatchPulse">
            PatchPulse
        </a>
        <div class="card"><?= $bodyHtml ?></div>
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
}

// ── GET: nessuna modifica di stato. Pagina di conferma con form POST. ──
if (!$isPost) {
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    ob_start();
    ?>
    <h1><?= t('unsub.confirm_heading') ?></h1>
    <p><?= t('unsub.confirm_body') ?></p>
    <form method="post" action="unscribe.php">
        <input type="hidden" name="email" value="<?= $safeEmail ?>">
        <input type="hidden" name="token" value="<?= $safeToken ?>">
        <button type="submit" class="btn"><?= t('unsub.confirm_button') ?></button>
    </form>
    <a class="cancel" href="/home.php"><?= t('unsub.cancel') ?></a>
    <?php
    unsub_render(ob_get_clean());
    exit();
}

// ── POST: applica l'opt-out (idempotente, NON cancella l'account) ──
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo t('unsub.service_unavailable');
    exit();
}

$stmt = $conn->prepare("UPDATE users SET email_opt_out = 1 WHERE email = ?");
if (!$stmt) {
    // Colonna non ancora presente (migration email_optout.sql) o DB degradato.
    $conn->close();
    http_response_code(500);
    echo t('unsub.service_unavailable');
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();
$conn->close();

// Messaggio generico (non rivela se l'email esiste): il token prova comunque che
// il link è stato emesso dal server per questa email.
ob_start();
?>
<h1><?= t('unsub.done_heading') ?></h1>
<p><?= t('unsub.done_body') ?></p>
<a class="cancel" href="/home.php"><?= t('unsub.cancel') ?></a>
<?php
unsub_render(ob_get_clean());
exit();
