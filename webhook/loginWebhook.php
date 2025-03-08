<?php
function sendLoginWebhook($user_id, $name, $email) {

    $webhookUrl = "";

    $messageData = [
	'content' => "📥 Login Riuscito:",
        "embeds" => [
            [
                "title" => "Dettagli Utente",
                "color" => 65280,
                "fields" => [
                    [
                        "name" => "ID Utente",
                        "value" => $user_id,
                        "inline" => true,
                    ],
                    [
                        "name" => "Nome",
                        "value" => $name,
                        "inline" => true,
                    ],
                    [
                        "name" => "Email",
                        "value" => $email,
                        "inline" => true,
                    ],
                ],
                "footer" => [
                    "text" => "PatchPulse Login",
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
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($webhookUrl, false, $context);

    if ($result === false) {
        error_log("Errore durante l'invio del webhook Discord");
    }
}
?>
