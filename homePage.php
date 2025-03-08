<?php
include("config.php");

?>

<!DOCTYPE html>
<html>

<head>
        <link rel="stylesheet" type="text/css" href="PatchPulse/css/style.css" />
        <link rel="stylesheet" type="text/css" href="PatchPulse/css/cssfx.css" />
        <title>PatchPulse</title>
        <meta charset="UTF-8" />
</head>

    <body>


    <section class="layout">
            <div class="header">
                <img
                    class="headerIcon"
                    src="PatchPulse/images/PatchPulseLogo.svg"
                    alt="CentredLogo"
                />
                <span
                    ><a href="https://mrtc.cc" class="headerButton">Home</a></span
                >

		                <!-- Inizio V3.8-->
                        <span><div class="dropdown">
                            <a href="#" class="headerButton">Tool</a>
                            <div class="dropdown-content">
                                <a href="PatchPulse/fastScan.php">Fast Scan</a>
                                <a href="PatchPulse/VulnerabilityScanner.php">Web Scan</a>
                                <a href="#">Coming Soon</a>
                                <a href="#">Coming Soon</a>
                            </div>
                        </div></span>
                        <!-- Fine V3.8-->

                <span
                    ><a href="mailto:support@mrtc.cc" class="headerButton">Contact Us</a></span
                >

                
                <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Se l'utente è loggato, mostra "ACCOUNT" -->
                        <button class="inout-button" onclick="window.location.href='PatchPulse/accountPage.php'">Account</button>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <button class="inout-button" onclick="window.location.href='PatchPulse/loginPage.php'">Log In / Sign Up</button>
                    <?php endif; ?>
            </div>
            <div class="body">
                <div class="main-container">
                    <img
                        class="mainLogo"
                        src="PatchPulse/images/whitegrid.png"
                        alt="CentredLogo"
                    />
                    <div class="scansZone">
                        <button class="scans-button-left" onclick="window.location.href='PatchPulse/fastScan.php'">Fast Scan</button>
                        <button class="scans-button-right" onclick="window.location.href='PatchPulse/VulnerabilityScanner.php'">Web Scan</button>
                    </div>
                </div>
            </div>
            <div class="faqZone">
                <p class="faqText">FAQ</p>
                <section class="layout2">
                    <div>
                        <ul>
                            <hr />
                            <li>
                                <a href="" class="faqButton" data-faq="faq1"
                                    >How it works?</a
                                >
                                <div class="plusSym active">
                                    <span class="horizontal"></span>
                                    <span class="vertical"></span>
                                </div>
                            </li>
                            <hr />
                            <li>
                                <a href="" class="faqButton" data-faq="faq2"
                                    >About my data?</a
                                >
                                <div class="plusSym">
                                    <span class="horizontal"></span>
                                    <span class="vertical"></span>
                                </div>
                            </li>
                            <hr />
                            <li>
                                <a href="" class="faqButton" data-faq="faq3"
                                    >Why should i sign up?</a
                                >
                                <div class="plusSym">
                                    <span class="horizontal"></span>
                                    <span class="vertical"></span>
                                </div>
                            </li>
                            <hr />
                            <li>
                                <a href="" class="faqButton" data-faq="faq4"
                                    >What the tools do?</a
                                >
                                <div class="plusSym">
                                    <span class="horizontal"></span>
                                    <span class="vertical"></span>
                                </div>
                            </li>
                            <hr />
                        </ul>
                    </div>
                    <div class="faq-answers">
                        <div id="faq1" class="faq-answer active">
                            <h3>How it works?</h3>
                            <p>
                                Simple steps to protect your privacy:<br><br>
                                1. Choose between Fast Scan or Web Scan<br>
                                2. Get instant results about security risks<br>
                                3. Review detailed analysis and recommendations<br>
                                4. Optional: Create an account to save results
                                
                            </p>
                        </div>
                        <div id="faq2" class="faq-answer">
                            <h3>About my data?</h3>
                            <p>
                                Your data security matters to us:<br><br>
                                • Scan results are stored securely in your personal account<br>
                                • Your information is never shared with third parties<br>
                                • We use minimal analytics for service improvement only<br>
                                • Access to your data is protected by your credentials
                            </p>
                        </div>
                        <div id="faq3" class="faq-answer">
                            <h3>Why should i sign up?</h3>
                                <p>
                                Benefits of creating an account:<br><br>
                                • Save all your scan results<br>
                                • Access your complete scan history<br>
                                • Track changes over time<br>
                                Note: You can still use the tools without an account, but results won't be saved
                            </p>
                        </div>
                        <div id="faq4" class="faq-answer">
                            <h3>What the tools do?</h3>
                            <p>
                                PatchPulse offers two security scanning tools:<br><br>
                                • Fast Scan: Reveals what information your browser exposes to websites.<br>
                                • Web Scan: Scans any website URL for security vulnerabilities. 
                                
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </section>
        <hr class="sectionLine"/>
        <div class="fot1">
            <ul>
                <li>
                    <img
                        class="arrowsFot"
                        src="PatchPulse/images/arrow1.png"
                        alt=""
                    />
                    <a href="" class="faqButton" data-faq="faq1"""
                        >Github</a
                    >
                </li>
                <li>
                    <img
                        class="arrowsFot"
                        src="PatchPulse/images/arrow1.png"
                        alt=""
                    />
                    <a href="" class="faqButton" data-faq="faq1"""
                        >Contact Us</a
                    >
                </li>
                <li>
                    <img
                        class="arrowsFot"
                        src="PatchPulse/images/arrow1.png"
                        alt=""
                    />
                    <a href="https://github.com/MrTcStudios" class="faqButton" data-faq="faq1"""
                        >Our Organization</a
                    >
                </li>
            </ul>


        </div>
        <hr class="sectionLine"/>
        <script src="PatchPulse/script.js"></script>
    </body>
</html>
