<?php
function sendDiscordWebhook($username, $email) {
    $webhookUrl = "";

    // Validazione input
    if (!is_string($username) || !is_string($email) || empty($username) || empty($email)) {
        error_log("Invalid input parameters for Discord webhook");
        return false;
    }
    
    // Sanificazione
    $safeUsername = htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    
    $webhookData = [
        'content' => 'Nuova registrazione ricevuta:',
        'embeds' => [
            [
                'title' => 'Dettagli Utente',
                'fields' => [
                    [
                        'name' => 'Username',
                        'value' => $safeUsername,
                        'inline' => true
                    ],
                    [
                        'name' => 'Email',
                        'value' => $safeEmail,
                        'inline' => true
                    ]
                ],
                'color' => 3066993,
                'footer' => [
                    'text' => 'PatchPulse Register',
                ],
                'timestamp' => date("c"),
            ]
        ]
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($webhookData),
            'timeout' => 5
        ]
    ];

    try {
        $context = stream_context_create($options);
        $result = @file_get_contents($webhookUrl, false, $context);
        
        if ($result === FALSE) {
            error_log("Failed to send Discord webhook");
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Discord webhook exception: " . $e->getMessage());
        return false;
    }
}
