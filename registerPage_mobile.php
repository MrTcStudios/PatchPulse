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

            /* Custom checkbox styling - Mobile first approach */
            .scan-legal-container {
                padding: 15px;
                margin: 10px 0;
                text-align: left;
            }

            .scan-legal-line {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin: 0;
                font-size: 14px;
                position: relative;
            }

            .scan-terms-link {
                color: white;
                text-decoration: underline;
            }

            /* Custom checkbox styling that works across all browsers and screen sizes */
            .terms-checkbox {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                width: 22px;
                height: 22px;
                border: 2px solid white;
                background-color: black;
                margin: 0;
                cursor: pointer;
                position: relative;
                border-radius: 3px;
                flex-shrink: 0;
                vertical-align: middle; /* Helps with alignment inside flex container */
                align-self: center; /* Further ensures vertical centering */
            }

            /* Firefox specific styling */
            @-moz-document url-prefix() {
                .terms-checkbox {
                    background-color: black;
                }
            }

            /* Checked state styling */
            .terms-checkbox:checked::after {
                content: "✓";
                position: absolute;
                color: white;
                font-size: 16px;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                line-height: 1;
            }

            /* Focus state for accessibility */
            .terms-checkbox:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
            }

            /* Make the text and link wrap properly */
            .p2 {
                flex: 1;
                min-width: 200px;
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
            <h1 class="faqText">Register</h1>

            <form class="auth-form" action="dataBase/save_user.php" method="post">
                    <input
                        type="text"
                        name="NameOfUser"
                        id=""
                        placeholder="username"
                        requirede
                        class="form1"
                    />
                    <input
                        type="email"
                        name="EmailOfUser"
                        id=""
                        placeholder="email"
                        required
                        class="form1"
                    />
                    <div class="password-field">
                        <input
                            type="password"
                            name="PasswordOfUserUnCrypt"
                            id="passwordField"
                            placeholder="password"
                            required
                            class="form1"
                        />
                        <span class="toggle-password">👁</span>
                    </div>
                    <!-- Legal Consent Section -->
                    <div class="scan-legal-container">
                        <label class="scan-legal-line" for="legalConsent">
                            <input
                                type="checkbox"
                                name="AgreeTerms"
                                id="legalConsent"
                                required
                                class="terms-checkbox"
                            />
                            <span class="p2">
                                I have legal permission to scan this website and
                                I accept the
                                <a
                                    href="policy/privacy_mobile.php"
                                    target="_blank"
                                    class="scan-terms-link"
                                    >Privacy Policy</a
                                >
                            </span>
                        </label>
                    </div>
                    <!-- 
                    <div
                        class="cf-turnstile"
                        data-sitekey="0x4AAAAAAA45DIVnAjfWbKkG"
                    ></div>
                    -->
                    <input
                        type="submit"
                        name=""
                        id=""
                        value="Register"
                        class=""
                    />
                </form>
                <div class="auth-links">
                <a href="loginPage_mobile.php">I already have an account</a>
                </div>

            <?php if (!empty($_SESSION['registration_message'])): ?>
                <p style="text-align: center; font-weight: bold; color:white;">
            <?php echo htmlspecialchars($_SESSION['registration_message']); ?>
                </p>
            <?php unset($_SESSION['registration_message']); ?>
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
