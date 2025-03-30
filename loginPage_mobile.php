<?php
include("config.php");

// Se l'utente è già loggato, reindirizza alla pagina corrente
if (isset($_SESSION['user_id'])) {
    if(isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: homePage_mobile.php");
    }
    exit();
}
?>


<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style_mobile.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx_mobile.css" />
        <title>PatchPulse - Login</title>
        <meta charset="UTF-8" />
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            /* Fix for auth links */
            .auth-links {
                display: flex;
                flex-direction: column;
                gap: 15px;
                margin-top: 20px;
                align-items: center;
            }

            .auth-links a {
                color: white;
                text-decoration: none;
                padding: 10px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                width: 80%;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <!-- Mobile Navigation -->
        <div class="menu-button">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="mobile-nav">
            <div class="nav-header">
                <img class="nav-logo" src="images/PatchPulseLogo.svg" alt="Logo" />
            </div>
            <a href="homePage_mobile.php">Home</a>
            <div class="mobile-dropdown">
                <div class="dropdown-header">Tools</div>
                <div class="dropdown-items">
                        <a href="fastScan_mobile.php">Fast Scan</a>
                        <a href="VulnerabilityScanner_mobile.php">Web Scan</a>
                        <a href="#">Coming Soon</a>
                    </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Se l'utente è loggato, mostra "ACCOUNT" -->
                        <a href="accountPage_mobile.php" class="login-btn">Account</a>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <a href="loginPage_mobile.php" class="login-btn">Log In / Sign Up</a>
                    <?php endif; ?>
        </div>

        <div class="header">
            <img class="headerIcon" src="images/PatchPulseLogo.svg" alt="Logo" />
        </div>

        <div class="auth-container">
            <h1 class="faqText">Login</h1>

            <form class="auth-form" action="dataBase/login_user.php" method="post">
                <input type="email" placeholder="Email" id="" name="EmailOfUser" required/>

                <div class="password-field">
                    <input type="password" placeholder="Password" name="PasswordOfUserUnCrypt" id="passwordField" required />
                    <span class="toggle-password">👁</span>
                </div>

              <!--  <div class="cf-turnstile" data-sitekey="0x4AAAAAAA45DIVnAjfWbKkG">
                </div>  -->

                <button type="submit" class="scans-button">Log In</button>
            </form>

            <div class="auth-links">
                <a href="#">Forgot Password?</a>
                <a href="registerPage_mobile.php">Create Account</a>
            </div>

            <?php if (!empty($_SESSION['login_message'])): ?>
                <p style="text-align: center; font-weight: bold; color:white;">
            <?php echo htmlspecialchars($_SESSION['login_message']); ?>
                </p>
            <?php unset($_SESSION['login_message']); ?>
            <?php endif; ?>
        </div>

        <footer class="mobile-footer">
            <div class="footer-links">
                <a href="#">
                    <img src="images/arrow1.png" alt="" class="footer-arrow" />
                    Github
                </a>
                <a href="#">
                    <img src="images/arrow1.png" alt="" class="footer-arrow" />
                    Contact Us
                </a>
                <a href="#">
                    <img src="images/arrow1.png" alt="" class="footer-arrow" />
                    Our Organization
                </a>
            </div>
        </footer>

        <script src="script_mobile.js"></script>
    </body>
</html>
