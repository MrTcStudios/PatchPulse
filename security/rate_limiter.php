<?php
declare(strict_types=1);

function rl_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // IPv4-mapped (::ffff:1.2.3.4) → tratta come l'IPv4 sottostante.
    if (stripos($ip, '::ffff:') === 0) {
        $v4 = substr($ip, 7);
        if (filter_var($v4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $v4;
        }
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $ip;
    }

    // IPv6: raggruppa sul prefisso /64. Un singolo client/attaccante controlla
    // tipicamente un intero /64, quindi limitare sull'indirizzo /128 completo
    // sarebbe aggirabile ruotando gli ultimi 64 bit (stesso spirito del bug XFF).
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return rl_ipv6_network($ip, 64);
    }

    // Fail-closed: REMOTE_ADDR assente/malformato non accade dietro un web server
    // reale. Tutte queste richieste condividono UNA chiave, così restano soggette
    // al lockout (mai una chiave-bypass univoca per richiesta).
    return 'no-ip';
}

// Riduce un IPv6 al suo prefisso di rete (default /64) azzerando i bit host.
// Ritorna una stringa stabile "rete/prefisso" usata come chiave di rate limiting.
function rl_ipv6_network(string $ip, int $prefixBits = 64): string {
    $packed = @inet_pton($ip);
    if ($packed === false || strlen($packed) !== 16) {
        return $ip; // già validato come IPv6; fallback prudente
    }
    $prefixBits = max(0, min(128, $prefixBits));
    for ($i = 0; $i < 16; $i++) {
        $bitStart = $i * 8;
        if ($bitStart >= $prefixBits) {
            $packed[$i] = "\x00";                        // byte interamente host → azzera
        } elseif ($bitStart + 8 > $prefixBits) {
            $keep = $prefixBits - $bitStart;             // bit da tenere nel byte di confine
            $packed[$i] = chr(ord($packed[$i]) & (0xFF << (8 - $keep) & 0xFF));
        }
    }
    $network = @inet_ntop($packed);
    return $network !== false ? $network . '/' . $prefixBits : $ip;
}

function rl_identifier(string ...$parts): string {
    $secret = getenv('RATE_LIMIT_SECRET');
    if (!$secret) {
        // Fallback per non rompere gli identificatori esistenti, ma segnalalo:
        // un secret dedicato (non riusato dal DB) è la scelta corretta.
        static $warned = false;
        if (!$warned) {
            error_log('rate_limiter: RATE_LIMIT_SECRET non impostata — fallback su DB_PASS. Imposta un valore casuale dedicato nel .env.');
            $warned = true;
        }
        $secret = (string)(getenv('DB_PASS') ?: getenv('DB_NAME') ?: 'patchpulse-rl');
    }
    return hash_hmac('sha256', implode('|', $parts), $secret);
}

function rl_gc(mysqli $conn): void {
    if (random_int(1, 50) !== 1) {
        return;
    }
    $hitCutoff = time() - 3600;
    $lockCutoff = time() - 86400;
    @$conn->query("DELETE FROM rate_limit_hits WHERE hit_time < {$hitCutoff}");
    @$conn->query("DELETE FROM rate_limit_lockouts WHERE lockout_until > 0 AND lockout_until < {$lockCutoff}");
}

function rl_check_window(mysqli $conn, string $action, string $identifier, int $max, int $windowSeconds): array {
    rl_gc($conn);
    $now = time();
    $cutoff = $now - $windowSeconds;

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS c, COALESCE(MIN(hit_time), 0) AS oldest
         FROM rate_limit_hits
         WHERE action = ? AND identifier = ? AND hit_time >= ?"
    );
    if (!$stmt) {
        return ['allowed' => false, 'current' => $max, 'retry_after' => $windowSeconds];
    }
    $stmt->bind_param('ssi', $action, $identifier, $cutoff);
    $stmt->execute();
    $stmt->bind_result($current, $oldest);
    $stmt->fetch();
    $stmt->close();

    $current = (int)$current;
    if ($current >= $max) {
        $retry = $oldest > 0 ? max(1, ($oldest + $windowSeconds) - $now) : $windowSeconds;
        return ['allowed' => false, 'current' => $current, 'retry_after' => $retry];
    }
    return ['allowed' => true, 'current' => $current, 'retry_after' => 0];
}

function rl_record(mysqli $conn, string $action, string $identifier): void {
    $now = time();
    $stmt = $conn->prepare(
        "INSERT INTO rate_limit_hits (action, identifier, hit_time) VALUES (?, ?, ?)"
    );
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('ssi', $action, $identifier, $now);
    @$stmt->execute();
    $stmt->close();
}

function rl_consume(mysqli $conn, string $action, string $identifier, int $max, int $windowSeconds, ?int &$retryAfter = null): bool {
    $res = rl_check_window($conn, $action, $identifier, $max, $windowSeconds);
    if (!$res['allowed']) {
        $retryAfter = $res['retry_after'];
        return false;
    }
    rl_record($conn, $action, $identifier);
    $retryAfter = 0;
    return true;
}

function rl_lockout_remaining(mysqli $conn, string $action, string $identifier): int {
    $now = time();
    $stmt = $conn->prepare(
        "SELECT lockout_until FROM rate_limit_lockouts
         WHERE action = ? AND identifier = ? AND lockout_until > ?"
    );
    if (!$stmt) {
        // Fail-CLOSED: se non possiamo verificare il lockout (tabella assente o DB
        // degradato) blocchiamo brevemente invece di lasciar passare tutto. Loggato
        // rumorosamente così un guasto della tabella si nota subito.
        error_log('rate_limiter: prepare fallita in rl_lockout_remaining: ' . $conn->error);
        return 60;
    }
    $stmt->bind_param('ssi', $action, $identifier, $now);
    if (!@$stmt->execute()) {
        error_log('rate_limiter: execute fallita in rl_lockout_remaining');
        $stmt->close();
        return 60;
    }
    $stmt->bind_result($until);
    $found = $stmt->fetch();
    $stmt->close();
    return $found ? max(0, (int)$until - $now) : 0;
}

function rl_register_failure(mysqli $conn, string $action, string $identifier, int $maxAttempts, int $lockoutSeconds): int {
    $now = time();
    $stmt = $conn->prepare(
        "INSERT INTO rate_limit_lockouts (action, identifier, attempts, last_attempt)
         VALUES (?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE
            attempts = IF(lockout_until > VALUES(last_attempt), attempts, attempts + 1),
            last_attempt = VALUES(last_attempt)"
    );
    if (!$stmt) {
        error_log('rate_limiter: prepare fallita (INSERT) in rl_register_failure: ' . $conn->error);
        return 0;
    }
    $stmt->bind_param('ssi', $action, $identifier, $now);
    @$stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT attempts FROM rate_limit_lockouts WHERE action = ? AND identifier = ?"
    );
    if (!$stmt) {
        error_log('rate_limiter: prepare fallita (SELECT attempts) in rl_register_failure: ' . $conn->error);
        return 0;
    }
    $stmt->bind_param('ss', $action, $identifier);
    $stmt->execute();
    $stmt->bind_result($attempts);
    $stmt->fetch();
    $stmt->close();

    if ((int)$attempts >= $maxAttempts) {
        $until = $now + $lockoutSeconds;
        $up = $conn->prepare(
            "UPDATE rate_limit_lockouts
             SET attempts = 0, lockout_until = ?
             WHERE action = ? AND identifier = ?"
        );
        if ($up) {
            $up->bind_param('iss', $until, $action, $identifier);
            @$up->execute();
            $up->close();
        }
        return $lockoutSeconds;
    }
    return 0;
}

function rl_clear_failures(mysqli $conn, string $action, string $identifier): void {
    $stmt = $conn->prepare(
        "DELETE FROM rate_limit_lockouts WHERE action = ? AND identifier = ?"
    );
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('ss', $action, $identifier);
    @$stmt->execute();
    $stmt->close();
}
