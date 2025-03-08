<?php
function sendDiscordWebhook($username, $email) {
    $webhookUrl = "";

    $webhookData = [
        'content' => 'Nuova registrazione ricevuta:',
        'embeds' => [
            [
                'title' => 'Dettagli Utente',
                'fields' => [
                    [
                        'name' => 'Username',
                        'value' => $username,
                        'inline' => true
                    ],
                    [
                        'name' => 'Email',
                        'value' => $email,
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
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($webhookUrl, false, $context);

    if ($result === FALSE) {
        echo "Errore nell'invio del webhook a Discord.";
    } 
}
