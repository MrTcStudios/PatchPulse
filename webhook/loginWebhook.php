<?php
function sendLoginWebhook($user_id, $name, $email) {

    $webhookUrl = "";

    // Validazione degli input
    if (!is_numeric($user_id) || $user_id <= 0 || 
        !is_string($name) || empty(trim($name)) || 
        !is_string($email) || empty(trim($email)) || 
        !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid input parameters for login webhook");
        return false;
    }

    // Sanificazione dei dati
    $safeUserId = (int)$user_id;
    $safeName = htmlspecialchars(trim($name), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeEmail = htmlspecialchars(trim($email), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $messageData = [
        'content' => "📥 Login:",
        "embeds" => [
            [
                "title" => "Dettagli Utente",
                "color" => 65280, // Verde
                "fields" => [
                    [
                        "name" => "ID Utente",
                        "value" => (string)$safeUserId,
                        "inline" => true,
                    ],
                    [
                        "name" => "Nome",
                        "value" => $safeName,
                        "inline" => true,
                    ],
                    [
                        "name" => "Email",
                        "value" => $safeEmail,
                        "inline" => true,
                    ],
                ],
                "footer" => [
                    "text" => "PatchPulse Register",
                ],
                "timestamp" => date("c"),
            ]
        ]
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($messageData),
            'timeout' => 5
        ],
    ];

    try {
        $context = stream_context_create($options);
        $result = @file_get_contents($webhookUrl, false, $context);

        if ($result === false) {
            error_log("Failed to send login webhook to Discord");
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Login webhook exception: " . $e->getMessage());
        return false;
    }
}
?>
