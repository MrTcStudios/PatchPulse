<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Conferma la tua registrazione a PatchPulse</title>
    <style>
        :root { color-scheme: light only; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333333 !important;
            background-color: #f4f4f4 !important;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background-color: #ffffff !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #8b7cf8 !important;
        }
        h1 {
            color: #1a1a1a !important;
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }
        p {
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
            color: #555555 !important;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #8b7cf8 !important;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none !important;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            mso-padding-alt: 0;
            text-underline-color: #ffffff;
        }
        .button:visited,
        .button:hover,
        .button:active {
            color: #ffffff !important;
            background-color: #6c5ce7 !important;
        }
        .link {
            color: #8b7cf8 !important;
            text-decoration: none;
            word-break: break-all;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999 !important;
            margin-top: 30px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
        .footer a {
            color: #8b7cf8 !important;
            text-decoration: none;
        }
    </style>
</head>
<body style="background-color:#f4f4f4;margin:0;padding:0;">
    <div class="container" style="background-color:#ffffff;">
        <div class="logo" style="color:#8b7cf8;">PatchPulse</div>
        <h1 style="color:#1a1a1a;">Conferma la tua registrazione</h1>

        <p style="color:#555555;">Ciao,</p>
        <p style="color:#555555;">Grazie per esserti unito a noi! Clicca sul pulsante qui sotto per confermare la tua registrazione.</p>

        <div class="button-container">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="YOUR_LINK" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="50%" fill="t">
                <v:fill type="tile" color="#8b7cf8" />
                <w:anchorlock/>
                <center style="color:#ffffff;font-family:sans-serif;font-size:16px;font-weight:bold;">Conferma Account</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-->
            <a href="YOUR_LINK" class="button" style="background-color:#8b7cf8;color:#ffffff;display:inline-block;padding:14px 28px;border-radius:50px;text-decoration:none;font-size:16px;font-weight:600;">Conferma il tuo account</a>
            <!--<![endif]-->
        </div>

        <p style="color:#999999;font-size:14px;">Se il pulsante non funziona, copia e incolla questo link nel browser:</p>
        <p><a href="YOUR_LINK" class="link" style="color:#8b7cf8;">YOUR_LINK</a></p>

        <p style="color:#999999;font-size:14px;">Se non hai effettuato questa registrazione, ignora questa email.</p>

        <div class="footer" style="color:#999999;">
            <p>&copy; <?php echo date("Y"); ?> PatchPulse. Tutti i diritti riservati.</p>
            <p>
                <a href="https://patchpulse.org/policy/privacy_policy.php" style="color:#8b7cf8;">Privacy Policy</a>
            </p>
            <p>PatchPulse</p>
        </div>
    </div>
</body>
</html>
