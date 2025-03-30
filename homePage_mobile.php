<?php
include("config.php");
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style_mobile.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx_mobile.css" />
        <title>PatchPulse</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <section class="layout">
            <!-- Mobile Menu Button -->
            <div class="menu-button">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <!-- Mobile Navigation Menu -->
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
                <img
                    class="headerIcon"
                    src="images/PatchPulseLogo.svg"
                    alt="CentredLogo"
                />
            </div>

            <div class="body">
                <div class="-buttmain-container">
                    <img
                        class="mainLogo"
                        src="images/whitegrid.png"
                        alt="CentredLogo"
                    />
                    <div class="scansZone">
                        <a href="fastScan_mobile.php"
                            ><button class="scans-button">Fast Scan</button></a
                        >
                        <a href="VulnerabilityScanner_mobile.php"
                            ><button class="scans-button">Web Scan</button></a
                        >
                    </div>
                </div>
            </div>

            <div class="faqZone">
                <h2 class="faqText">FAQ</h2>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question">
                            How it works?
                            <span class="arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>
                                Simple steps to protect your privacy:<br><br>
                                1. Choose between Fast Scan or Web Scan<br>
                                2. Get instant results about security risks<br>
                                3. Review detailed analysis and recommendations<br>
                                4. Optional: Create an account to save results
                                
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            About my data?
                            <span class="arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                           <p>
                                Your data security matters to us:<br><br>
                                • Scan results are stored securely in your personal account<br>
                                • Your information is never shared with third parties<br>
                                • We use minimal analytics for service improvement only<br>
                                • Access to your data is protected by your credentials
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Why should I sign up?
                            <span class="arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>
                                Benefits of creating an account:<br><br>
                                • Save all your scan results<br>
                                • Access your complete scan history<br>
                                • Track changes over time<br>
                                Note: You can still use the tools without an account, but results won't be saved
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            What do the tools do?
                            <span class="arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>
                                PatchPulse offers two security scanning tools:<br><br>
                                • Fast Scan: Reveals what information your browser exposes to websites.<br>
                                • Web Scan: Scans any website URL for security vulnerabilities. 
                                
                            </p>
                        </div>
                    </div>
                </div>
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
        </section>

        <script src="script_mobile.js"></script>
    </body>
</html>
