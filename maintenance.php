<?php
http_response_code(503);
header('Content-Type: text/html; charset=utf-8');
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Manutenzione</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <style>
        :root { --purple: #8b7cf8; --purple-dark: #6c5ce7; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #faf9ff;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }
        .bg-orb-1 { width: 400px; height: 400px; background: var(--purple); top: -100px; right: -100px; }
        .bg-orb-2 { width: 300px; height: 300px; background: #6c5ce7; bottom: -80px; left: -80px; animation-delay: -4s; }
        .bg-orb-3 { width: 200px; height: 200px; background: #a29bfe; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-delay: -2s; }

        .container {
            text-align: center;
            position: relative;
            z-index: 1;
            max-width: 500px;
            padding: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            margin-bottom: 2rem;
        }
        .logo-icon {
            width: 48px; height: 48px;
            background: var(--purple);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-icon svg { width: 28px; height: 28px; }
        .logo-text {
            font-family: 'DM Serif Display', serif;
            font-size: 1.8rem;
            color: #1a1a1a;
        }

        h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            color: #1a1a1a;
            margin-bottom: 0.8rem;
        }

        p {
            font-size: 1.05rem;
            color: #777;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .loader {
            width: 160px;
            height: 4px;
            background: rgba(139,124,248,0.15);
            margin: 0 auto 2rem;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        .loader::after {
            content: '';
            position: absolute;
            left: -40%;
            height: 100%;
            width: 40%;
            background: linear-gradient(90deg, var(--purple), var(--purple-dark));
            border-radius: 4px;
            animation: loading 1.5s ease-in-out infinite;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            background: rgba(139,124,248,0.08);
            border: 1px solid rgba(139,124,248,0.15);
            border-radius: 50px;
            font-size: 0.85rem;
            color: var(--purple);
            font-weight: 500;
        }
        .status-dot {
            width: 8px; height: 8px;
            background: var(--purple);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        @keyframes loading {
            0% { left: -40%; }
            100% { left: 100%; }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        @media (max-width: 480px) {
            h1 { font-size: 1.5rem; }
            p { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <div class="container">
        <div class="logo">
            <div class="logo-icon">
                <svg fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <span class="logo-text">PatchPulse</span>
        </div>

        <h1>In Manutenzione</h1>
        <p>Stiamo lavorando per migliorare il servizio.<br>Torneremo online a breve.</p>

        <div class="loader"></div>

        <div class="status">
            <span class="status-dot"></span>
            Aggiornamento in corso
        </div>
    </div>
</body>
</html>
<?php exit; ?>

