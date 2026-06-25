<?php

declare(strict_types=1);

if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

function rl_client_ip(): string {
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        return $cf;
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
}

function rl_identifier(string ...$parts): string {
    $secret = getenv('RATE_LIMIT_SECRET');
    if (!$secret) {
        $secret = (string)(getenv('DB_PASS') ?: getenv('DB_NAME') ?: 'patchpulse-rl');
    }
    return hash_hmac('sha256', implode('|', $parts), $secret);
}

function rl_gc(mysqli $conn): void {
    if (random_int(1, 50) !== 1) {
        return;
    }
    try {
        $hitCutoff = time() - 3600;
        $lockCutoff = time() - 86400;
        $conn->query("DELETE FROM rate_limit_hits WHERE hit_time < {$hitCutoff}");
        $conn->query("DELETE FROM rate_limit_lockouts WHERE lockout_until > 0 AND lockout_until < {$lockCutoff}");
    } catch (\Throwable $e) {
        error_log('[rate_limiter] gc failed: ' . $e->getMessage());
    }
}

function rl_check_window(mysqli $conn, string $action, string $identifier, int $max, int $windowSeconds): array {
    try {
        rl_gc($conn);
        $now = time();
        $cutoff = $now - $windowSeconds;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS c, COALESCE(MIN(hit_time), 0) AS oldest
             FROM rate_limit_hits
             WHERE action = ? AND identifier = ? AND hit_time >= ?"
        );
        if (!$stmt) {
            return ['allowed' => true, 'current' => 0, 'retry_after' => 0];
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
    } catch (\Throwable $e) {
        error_log('[rate_limiter] check_window(' . $action . ') failed: ' . $e->getMessage());
        return ['allowed' => true, 'current' => 0, 'retry_after' => 0];
    }
}

function rl_record(mysqli $conn, string $action, string $identifier): void {
    try {
        $now = time();
        $stmt = $conn->prepare(
            "INSERT INTO rate_limit_hits (action, identifier, hit_time) VALUES (?, ?, ?)"
        );
        if (!$stmt) {
            return;
        }
        $stmt->bind_param('ssi', $action, $identifier, $now);
        $stmt->execute();
        $stmt->close();
    } catch (\Throwable $e) {
        error_log('[rate_limiter] record(' . $action . ') failed: ' . $e->getMessage());
    }
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
    try {
        $now = time();
        $stmt = $conn->prepare(
            "SELECT lockout_until FROM rate_limit_lockouts
             WHERE action = ? AND identifier = ? AND lockout_until > ?"
        );
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param('ssi', $action, $identifier, $now);
        $stmt->execute();
        $stmt->bind_result($until);
        $found = $stmt->fetch();
        $stmt->close();
        return $found ? max(0, (int)$until - $now) : 0;
    } catch (\Throwable $e) {
        error_log('[rate_limiter] lockout_remaining(' . $action . ') failed: ' . $e->getMessage());
        return 0;
    }
}

function rl_register_failure(mysqli $conn, string $action, string $identifier, int $maxAttempts, int $lockoutSeconds): int {
    try {
        $now = time();
        $stmt = $conn->prepare(
            "INSERT INTO rate_limit_lockouts (action, identifier, attempts, last_attempt)
             VALUES (?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE
                attempts = IF(lockout_until > VALUES(last_attempt), attempts, attempts + 1),
                last_attempt = VALUES(last_attempt)"
        );
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param('ssi', $action, $identifier, $now);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare(
            "SELECT attempts FROM rate_limit_lockouts WHERE action = ? AND identifier = ?"
        );
        if (!$stmt) {
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
                $up->execute();
                $up->close();
            }
            return $lockoutSeconds;
        }
        return 0;
    } catch (\Throwable $e) {
        error_log('[rate_limiter] register_failure(' . $action . ') failed: ' . $e->getMessage());
        return 0;
    }
}

function rl_clear_failures(mysqli $conn, string $action, string $identifier): void {
    try {
        $stmt = $conn->prepare(
            "DELETE FROM rate_limit_lockouts WHERE action = ? AND identifier = ?"
        );
        if (!$stmt) {
            return;
        }
        $stmt->bind_param('ss', $action, $identifier);
        $stmt->execute();
        $stmt->close();
    } catch (\Throwable $e) {
        error_log('[rate_limiter] clear_failures(' . $action . ') failed: ' . $e->getMessage());
    }
}
