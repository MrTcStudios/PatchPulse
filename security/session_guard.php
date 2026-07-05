<?php
declare(strict_types=1);

function pp_session_logout(): void {
    unset(
        $_SESSION['user_id'],
        $_SESSION['email'],
        $_SESSION['name'],
        $_SESSION['auth_epoch']
    );
}

// Limiti del timer di sessione (tunabili).
const PP_SESSION_IDLE_LIMIT = 3600;    // 1h di inattività
const PP_SESSION_ABS_LIMIT  = 43200;   // 12h di durata massima assoluta

function pp_session_within_timeout(): bool {
    $now = time();

    if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity']) > PP_SESSION_IDLE_LIMIT) {
        return false;
    }
    if (isset($_SESSION['created_at']) && ($now - (int)$_SESSION['created_at']) > PP_SESSION_ABS_LIMIT) {
        return false;
    }

    $_SESSION['last_activity'] = $now;
    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = $now;
    }
    return true;
}

function pp_session_is_valid(mysqli $conn): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Timeout applicativo (idle + assoluto) prima di ogni query.
    if (!pp_session_within_timeout()) {
        pp_session_logout();
        return false;
    }

    $uid = (int)$_SESSION['user_id'];

    $stmt = @$conn->prepare("SELECT auth_epoch FROM users WHERE id = ?");
    if ($stmt === false) {
        // Colonna assente (migration non applicata) o DB degradato: non
        // peggiorare il comportamento attuale, lascia la sessione invariata.
        error_log('session_guard: auth_epoch non disponibile (migration applicata?): ' . $conn->error);
        return true;
    }

    $stmt->bind_param('i', $uid);
    if (!@$stmt->execute()) {
        $stmt->close();
        error_log('session_guard: execute fallita, sessione lasciata invariata');
        return true; // fail-open su errore transitorio
    }

    $stmt->bind_result($dbEpoch);
    $found = $stmt->fetch();
    $stmt->close();

    // Sessioni pre-esistenti senza epoch → 0 (= DEFAULT del DB).
    $sessionEpoch = (int)($_SESSION['auth_epoch'] ?? 0);

    if ($found !== true || (int)$dbEpoch !== $sessionEpoch) {
        pp_session_logout();
        return false;
    }

    return true;
}

/**
 * Legge l'auth_epoch corrente di un utente. Usata al login e dopo un bump per
 * riallineare la sessione. Ritorna 0 se la colonna non è ancora disponibile
 */
function pp_fetch_auth_epoch(mysqli $conn, int $userId): int {
    $stmt = @$conn->prepare("SELECT auth_epoch FROM users WHERE id = ?");
    if ($stmt === false) {
        return 0;
    }
    $stmt->bind_param('i', $userId);
    if (!@$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $stmt->bind_result($epoch);
    $stmt->fetch();
    $stmt->close();
    return (int)$epoch;
}

/**
 * Incrementa l'auth_epoch di un utente → invalida tutte le sue sessioni.
 * Fail-open: se la colonna non esiste ancora, no-op (nessun errore propagato,
 * l'operazione di cambio password prosegue normalmente).
 */
function pp_bump_auth_epoch(mysqli $conn, int $userId): void {
    $stmt = @$conn->prepare("UPDATE users SET auth_epoch = auth_epoch + 1 WHERE id = ?");
    if ($stmt === false) {
        error_log('session_guard: bump auth_epoch non disponibile (migration applicata?): ' . $conn->error);
        return;
    }
    $stmt->bind_param('i', $userId);
    @$stmt->execute();
    $stmt->close();
}
