<?php
function sendDiscordWebhook($username, $email) {
    $webhookUrl = getenv('DISCORD_REGISTER_WEBHOOK_URL');
    if (empty($webhookUrl)) {
        return false;
    }

    if (!is_string($username) || !is_string($email) || empty($username) || empty($email)) {
        error_log("Invalid input for register webhook");
        return false;
    }

    $safeUsername = str_replace(['`', '*', '_', '~', '|', '@', '#'], '', trim($username));
    $safeEmail = str_replace(['`', '*', '_', '~', '|', '@', '#'], '', trim($email));

    $webhookData = [
        'content' => 'Nuova registrazione:',
        'embeds' => [[
            'title' => 'Dettagli Utente',
            'fields' => [
                ['name' => 'Username', 'value' => $safeUsername, 'inline' => true],
                ['name' => 'Email', 'value' => $safeEmail, 'inline' => true],
            ],
            'color' => 3066993,
            'footer' => ['text' => 'PatchPulse'],
            'timestamp' => date('c'),
        ]]
    ];

    $context = stream_context_create([
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($webhookData),
            'timeout' => 5,
        ],
    ]);

    $result = @file_get_contents($webhookUrl, false, $context);
    if ($result === false) {
        error_log("Failed to send register webhook");
        return false;
    }
    return true;
}
