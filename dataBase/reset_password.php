<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Errore interno.');
}

$error = '';
$success = '';
$showForm = false;

$token = $_GET['token'] ?? $_POST['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = "Link non valido.";
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = "Link non valido o scaduto. Richiedi un nuovo reset.";
        $stmt->close();
    } else {
        $stmt->bind_result($userId);
        $stmt->fetch();
        $stmt->close();
        $showForm = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Richiesta non valida.";
        $showForm = false;
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || empty($confirmPassword)) {
            $error = "Compila tutti i campi.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Le password non coincidono.";
        } elseif (mb_strlen($newPassword) < 8) {
            $error = "La password deve avere almeno 8 caratteri.";
        } elseif (mb_strlen($newPassword) > 128) {
            $error = "Password troppo lunga (max 128 caratteri).";
        } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $error = "La password deve contenere almeno una maiuscola, una minuscola e un numero.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ? AND reset_token = ?");
            $update->bind_param("sis", $hashedPassword, $userId, $token);
            $update->execute();

            if ($update->affected_rows > 0) {
                $userIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
                if (!filter_var($userIp, FILTER_VALIDATE_IP)) $userIp = 'unknown';
                $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'password_reset_completed', ?)");
                $log->bind_param("is", $userId, $userIp);
                $log->execute();
                $log->close();

                $success = "Password reimpostata con successo!";
                $showForm = false;
            } else {
                $error = "Errore durante il reset. Il link potrebbe essere scaduto.";
                $showForm = false;
            }
            $update->close();
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Reimposta Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reset-page { display:flex; align-items:flex-start; justify-content:center; min-height:100vh; padding:3rem 1rem; height:auto; }
        .reset-box { background:#fff; border:1px solid rgba(0,0,0,0.07); border-radius:24px; padding:3rem; width:100%; max-width:440px; margin:auto; box-shadow:0 8px 40px rgba(0,0,0,0.08); }
        .reset-box h1 { font-family:'DM Serif Display',serif; font-size:1.8rem; color:#1a1a1a; text-align:center; margin-bottom:0.5rem; }
        .reset-box .subtitle { color:#888; text-align:center; margin-bottom:1.5rem; font-size:0.95rem; }
        .reset-box label { display:block; margin-bottom:0.3rem; color:#555; font-size:0.85rem; font-weight:500; }
        .reset-box input[type=password] { width:100%; padding:0.85rem 1rem; background:#f8f7ff; border:1.5px solid rgba(139,124,248,0.2); border-radius:10px; color:#1a1a1a; font-size:0.95rem; font-family:'DM Sans',sans-serif; margin-bottom:1rem; }
        .reset-box input:focus { outline:none; border-color:var(--purple); box-shadow:0 0 0 3px rgba(139,124,248,0.12); }
        .reset-box button { width:100%; padding:0.85rem; background:var(--purple); color:#fff; border:none; border-radius:50px; font-size:0.95rem; font-weight:500; cursor:pointer; font-family:'DM Sans',sans-serif; }
        .reset-box button:hover { background:var(--purple-dark); }
        .msg { padding:0.8rem 1rem; border-radius:10px; margin-bottom:1rem; text-align:center; font-size:0.9rem; }
        .msg-err { background:rgba(220,38,38,0.08); color:#dc2626; border:1px solid rgba(220,38,38,0.15); }
        .msg-ok { background:rgba(34,160,107,0.08); color:#22a06b; border:1px solid rgba(34,160,107,0.15); }
        .back-link { text-align:center; margin-top:1.2rem; }
        .back-link a { color:var(--purple); text-decoration:none; font-size:0.9rem; }
    </style>
</head>
<body>
<main class="main-wrapper reset-page" style="margin-left:0;border-radius:0;">
    <div class="reset-box">
        <h1>Reimposta Password</h1>

        <?php if ($error): ?>
            <div class="msg msg-err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="msg msg-ok"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-link"><a href="../log-reg.php#login">Vai al Login</a></div>
        <?php endif; ?>

        <?php if ($showForm): ?>
            <p class="subtitle">Inserisci la tua nuova password</p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label for="new_password">Nuova Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Minimo 8 caratteri, maiuscola, minuscola, numero" required minlength="8">
                <label for="confirm_password">Conferma Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ripeti la password" required>
                <button type="submit">Reimposta Password</button>
            </form>
        <?php endif; ?>

        <?php if (!$showForm && !$success): ?>
            <div class="back-link"><a href="../log-reg.php">Torna al Login</a></div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
