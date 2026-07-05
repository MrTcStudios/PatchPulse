<?php
declare(strict_types=1);

function pp_unsubscribe_url(string $email): string {
    $secret = getenv('UNSUBSCRIBE_SECRET');
    if (empty($secret)) {
        return '';
    }
    $domain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
    $token  = hash_hmac('sha256', $email, $secret);
    return "https://{$domain}/dataBase/unscribe.php?email=" . urlencode($email) . "&token=" . $token;
}

function pp_apply_list_unsubscribe(\PHPMailer\PHPMailer\PHPMailer $mail, string $email): void {
    $url = pp_unsubscribe_url($email);
    if ($url === '') {
        return;
    }
    $mail->addCustomHeader('List-Unsubscribe', '<' . $url . '>');
    $mail->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
}

function pp_email_opted_out(mysqli $conn, string $email): bool {
    $stmt = @$conn->prepare("SELECT email_opt_out FROM users WHERE email = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $email);
    if (!@$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->bind_result($optOut);
    $found = $stmt->fetch();
    $stmt->close();
    return $found && (int)$optOut === 1;
}
