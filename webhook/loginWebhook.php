<?php
function sendLoginWebhook($user_id, $name, $email) {
    $webhookUrl = getenv('DISCORD_LOGIN_WEBHOOK_URL');
    if (empty($webhookUrl)) {
        return false;
    }

    if (!is_numeric($user_id) || $user_id <= 0 ||
        !is_string($name) || empty(trim($name)) ||
        !is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid input for login webhook");
        return false;
    }

    $safeUserId = (int)$user_id;
    $safeName = str_replace(['`', '*', '_', '~', '|', '@', '#'], '', trim($name));
    $safeEmail = str_replace(['`', '*', '_', '~', '|', '@', '#'], '', trim($email));

    $messageData = [
        'content' => "📥 Login:",
        'embeds' => [[
            'title' => 'Dettagli Utente',
            'color' => 65280,
            'fields' => [
                ['name' => 'ID', 'value' => (string)$safeUserId, 'inline' => true],
                ['name' => 'Nome', 'value' => $safeName, 'inline' => true],
                ['name' => 'Email', 'value' => $safeEmail, 'inline' => true],
            ],
            'footer' => ['text' => 'PatchPulse'],
            'timestamp' => date('c'),
        ]]
    ];

    $context = stream_context_create([
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($messageData),
            'timeout' => 5,
        ],
    ]);

    $result = @file_get_contents($webhookUrl, false, $context);
    if ($result === false) {
        error_log("Failed to send login webhook");
        return false;
    }
    return true;
}
