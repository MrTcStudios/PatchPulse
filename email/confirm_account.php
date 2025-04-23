<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma la tua registrazione a PatchPulse</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; 
            color: #333; 
            background-color: #f4f4f4; 
            margin: 0;
            line-height: 1.6;
        }
        .container { 
            max-width: 600px; 
            margin: 40px auto; 
            padding: 30px; 
            border: 1px solid #e0e0e0; 
            border-radius: 12px; 
            background: #ffffff; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: black;
        }
        h1 { 
            color: black; 
            text-align: center;
            margin-bottom: 20px;
        }
        p { 
            font-size: 16px; 
            margin-bottom: 15px;
            text-align: center;
        }
        .button-container { 
            text-align: center; 
            margin: 25px 0; 
        }
        .button { 
            background-color: black;
            color: #fff; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 6px; 
            font-size: 16px;
            transition: background-color 0.3s ease;
            display: inline-block;
            border: 1px solid black;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .button:hover {
            background-color: #FDBE00;
        }
        .link {
            color: #1976D2;
            text-decoration: none;
            word-break: break-all;
        }
        .link:hover {
            text-decoration: underline;
        }
        .footer { 
            text-align: center; 
            font-size: 12px; 
            color: #757575; 
            margin-top: 30px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
        .footer-links {
            margin-top: 10px;
        }
        .footer-links a {
            color: #1976D2;
            margin: 0 10px;
            text-decoration: none;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">PatchPulse</div>
        <h1>Conferma la tua registrazione</h1>
        
        <p>Ciao,</p>
        <p>Grazie per esserti unito a noi! Clicca sul pulsante qui sotto per confermare la tua registrazione.</p>
        
        <div class="button-container">
            <a href="https://mrtc.cc/PatchPulse/dataBase/conferma-email.php?token=YOUR_TOKEN" class="button">Conferma il tuo account</a>
        </div>
        
        <p>Se il pulsante non funziona, copia e incolla il seguente link nel tuo browser:</p>
        <p><a href="https://mrtc.cc/PatchPulse/dataBase/conferma-email.php?token=YOUR_TOKEN" class="link">https://mrtc.cc/PatchPulse/dataBase/conferma-email.php?token=YOUR_TOKEN</a></p>
        
        <p>Se non hai effettuato questa registrazione, ti preghiamo di ignorare questa email.</p>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> PatchPulse. Tutti i diritti riservati.</p>
            <div class="footer-links">
                <a href="https://mrtc.cc/PatchPulse/dataBase/unscribe.php?email=YOUR_EMAIL">Disiscriviti</a>
                <a href="https://mrtc.cc/PatchPulse/privacy">Privacy Policy</a>
            </div>
            <p>PatchPulse - Udine, Italia</p>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            window.location.href = "https://mrtc.cc/PatchPulse/dataBase/conferma-email.php?token=" + token;
        }
    </script>
</body>
</html>
