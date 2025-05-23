<?php
include("../config.php");
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
        />
        <title>PatchPulse - Terms & Conditions</title>
        <link rel="stylesheet" type="text/css" href="../css/style_mobile.css" />
        <link rel="stylesheet" type="text/css" href="../css/cssfx_mobile.css" />
    </head>
    <body>
        <div class="layout">
            <!-- Mobile Menu Button -->
            <div class="menu-button">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <!-- Mobile Navigation Menu -->
            <div class="mobile-nav">
                <div class="nav-header">
                    <img class="nav-logo" src="../images/PatchPulseLogo.svg" alt="Logo" />
                </div>
                <a href="../homePage_mobile.php">Home</a>
            <div class="mobile-dropdown">
                <div class="dropdown-header">Tools</div>
                <div class="dropdown-items">
                        <a href="../fastScan_mobile.php">Fast Scan</a>
                        <a href="../VulnerabilityScanner_mobile.php">Web Scan</a>
                        <a href="#">Coming Soon</a>
                    </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Se l'utente è loggato, mostra "ACCOUNT" -->
                        <a href="../accountPage_mobile.php" class="login-btn">Account</a>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <a href="../loginPage_mobile.php" class="login-btn">Log In / Sign Up</a>
                    <?php endif; ?>
        </div>

            <!-- Header -->
            <div class="header">
                <img
                    class="headerIcon"
                    src="../images/PatchPulseLogo.svg"
                    alt="CentredLogo"
                />
            </div>

            <!-- Terms Content -->
            <div class="main-container terms-container">
                <h1>Terms and Conditions</h1>
                <p class="last-update">Last updated: March 7, 2025</p>

                <div class="terms-intro">
                    <p class="no-bullet">
                        Welcome to PatchPulse, a web security scanning service.
                        By using our service, you agree to comply with the
                        following Terms and Conditions. Please read the
                        information below carefully.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>1. Acceptance of Terms</h2>
                    <p class="no-bullet">
                        By accessing and using our service, you agree to comply
                        with these Terms and Conditions. If you do not accept
                        these terms, please do not use our site and service.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>2. Service Usage</h2>
                    <p class="no-bullet">
                        Our service allows users to perform security scans of
                        websites by entering a URL. Users are responsible for
                        ensuring they have explicit legal permission to scan the
                        specified website.
                    </p>
                    <p class="no-bullet">
                        Using our service without website owner's permission may
                        violate applicable laws regarding unauthorized access
                        and computer tampering.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>3. User Responsibility</h2>
                    <p class="no-bullet">
                        The user declares and guarantees that:
                    </p>
                    <ul class="bullet-list">
                        <li>
                            They have obtained explicit permission from the
                            website owner they intend to scan.
                        </li>
                        <li>
                            They will use the service only for legitimate
                            purposes and not to damage, alter, or compromise the
                            integrity of scanned websites.
                        </li>
                        <li>
                            They are responsible for any legal damage resulting
                            from improper use of our service.
                        </li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>4. Limitation of Liability</h2>
                    <p class="no-bullet">PatchPulse is not responsible for:</p>
                    <ul class="bullet-list">
                        <li>
                            Damage to computer systems or devices caused by
                            using our service.
                        </li>
                        <li>
                            Any damages resulting from the use of information
                            obtained through performed scans.
                        </li>
                        <li>
                            Claims, liabilities, or damages resulting from legal
                            actions related to unauthorized or non-permitted
                            scans.
                        </li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>5. Scan Data Storage and Management</h2>

                    <h3>For Users Without Accounts</h3>
                    <p class="no-bullet">
                        If you use our service without creating an account, no
                        scan data will be saved in our system. All scan results
                        are temporary and will be automatically deleted once
                        your session ends. We do not store or process any data
                        from these scans beyond what is necessary to deliver the
                        immediate results to you.
                    </p>

                    <h3>For Users With Accounts</h3>
                    <p class="no-bullet">
                        If you have created a PatchPulse account, you have the
                        option to save your scan results for future reference.
                        This data will only be saved if you explicitly choose to
                        save it to your scan history. All saved scan data is
                        treated with strict confidentiality:
                    </p>
                    <ul class="bullet-list">
                        <li>Data is encrypted when stored in our database</li>
                        <li>
                            Data is only decrypted when you access your scan
                            history through your account
                        </li>
                        <li>
                            All saved scan data is automatically and
                            irreversibly deleted after 7 days from the date it
                            was saved
                        </li>
                    </ul>

                    <p class="no-bullet">
                        The saved data includes all information detected during
                        scans, which may include sensitive data or
                        vulnerabilities identified during the scanning process.
                        We take extensive security measures to protect this
                        information through encryption and secure access
                        controls.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>6. Privacy and Data Protection</h2>
                    <p class="no-bullet">
                        We collect data exclusively for service operation and to
                        provide you with access to scan history (for registered
                        users who choose to save their scans). The collected
                        data is not used for marketing purposes and is not
                        shared with third parties, except as required by law.
                    </p>
                    <p class="no-bullet">
                        For more details on how we manage your privacy, please
                        consult our Privacy Policy.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>7. Terms Modifications</h2>
                    <p class="no-bullet">
                        We reserve the right to modify, update, or revise these
                        Terms and Conditions at any time, without notice.
                        Changes will be effective as soon as they are published
                        on our site. We encourage you to regularly check this
                        page to stay informed about any changes.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>8. Applicable Law and Dispute Resolution</h2>
                    <p class="no-bullet">
                        These Terms and Conditions are governed by the laws in
                        force in Italy and any dispute will be resolved
                        exclusively in the competent courts of Milan, Italy.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>9. Contact</h2>
                    <p class="no-bullet">
                        For any questions or clarifications about these Terms
                        and Conditions, you can contact us at:
                        <a
                            href="/cdn-cgi/l/email-protection"
                            class="__cf_email__"
                            data-cfemail="89fafcf9f9e6fbfdc9e4fbfdeaa7eaea"
                            >[email&#160;protected]</a
                        >
                    </p>
                </div>

                <div class="terms-section acceptance">
                    <h2>Acceptance of Terms</h2>
                    <p class="no-bullet">
                        By using our service, you acknowledge that you have
                        read, understood, and accepted these Terms and
                        Conditions. If you disagree with any of these terms,
                        please do not use our service.
                    </p>
                </div>
            </div>

            <!-- Scroll to top button -->
            <div class="scroll-top">↑</div>

            <!-- Footer -->
            <div class="mobile-footer">
                <div class="footer-links">
                    <a href="#">
                        <img
                            class="footer-arrow"
                            src="../images/arrow1.png"
                            alt=""
                        />
                        Github
                    </a>
                    <a href="#">
                        <img
                            class="footer-arrow"
                            src="../images/arrow1.png"
                            alt=""
                        />
                        Contact Us
                    </a>
                    <a href="#">
                        <img
                            class="footer-arrow"
                            src="../images/arrow1.png"
                            alt=""
                        />
                        Our Organization
                    </a>
                </div>
            </div>
        </div>

        <script
            data-cfasync="false"
            src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"
        ></script>
        <script src="../script_mobile.js"></script>
        <script
            src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js"
            data-cf-settings="a3a23de76c684abb01991795-|49"
            defer
        ></script>

        <!-- Additional script for the scroll-to-top button -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const scrollTopButton = document.querySelector(".scroll-top");

                // Show/hide scroll button based on scroll position
                window.addEventListe ner("scroll", function () {
                    if (window.scrollY > 300) {
                        scrollTopButton.classList.add("visible");
                    } else {
                        scrollTopButton.classList.remove("visible");
                    }
                });

                // Scroll to top when button is clicked
                scrollTopButton.addEventListener("click", function () {
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth",
                    });
                });
            });
        </script>
    </body>
</html>