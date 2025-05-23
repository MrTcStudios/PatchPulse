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
        <title>PatchPulse - Privacy Policy</title>
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

            <!-- Privacy Content -->
            <div class="main-container terms-container">
                <h1>Privacy Policy</h1>
                <p class="last-update">Last updated: March 7, 2025</p>

                <div class="terms-intro">
                    <p class="no-bullet">
                        PatchPulse ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our web security scanning service.
                    </p>
                    <p class="no-bullet">
                        Please read this Privacy Policy carefully. By accessing or using PatchPulse, you acknowledge that you have read, understood, and agree to be bound by all the terms outlined in this Privacy Policy. If you do not agree with our policies and practices, please do not use our service.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>1. Information We Collect</h2>

                    <h3>1.1 Personal Information</h3>
                    <p class="no-bullet">When you create an account with PatchPulse, we may collect the following personal information:</p>
                    <ul class="bullet-list">
                        <li>Email address</li>
                        <li>Username</li>
                        <li>Password (stored in encrypted form)</li>
                        <li>IP address</li>
                    </ul>

                    <h3>1.2 Scan Data</h3>

                    <h3>1.2.1 Users Without Accounts</h3>
                    <p class="no-bullet">If you use our service without creating an account, we do not save any scan data in our system. All scan results are temporary and will be automatically deleted once your session ends.</p>

                    <h3>1.2.2 Users With Accounts</h3>
                    <p class="no-bullet">If you have a PatchPulse account and explicitly choose to save your scan results, we collect and store:</p>
                    <ul class="bullet-list">
                        <li>URLs of websites you scan</li>
                        <li>Timestamps of when scans are performed</li>
                        <li>Technical details discovered during scans, including but not limited to:</li>
                        <li>Identified vulnerabilities</li>
                        <li>Server configuration details</li>
                        <li>Open ports</li>
                        <li>Security headers</li>
                        <li>Other technical information related to the websites being scanned</li>
                    </ul>
                    <p class="no-bullet">Some of this information may include sensitive data or vulnerabilities identified during the scanning process.</p>

                    <h3>1.3 Technical Information</h3>
                    <p class="no-bullet">The following technical information is automatically collected and managed by our security provider Cloudflare when you visit our website:</p>
                    <ul class="bullet-list">
                        <li>Browser type and version</li>
                        <li>Operating system</li>
                        <li>Referral source</li>
                        <li>Time and date of access</li>
                        <li>Pages visited</li>
                    </ul>
                    <p class="no-bullet">We do not have direct access to or control over this information, which is collected and processed according to Cloudflare's own privacy policy.</p>
                </div>

                <div class="terms-section">
                    <h2>2. How We Use Your Information</h2>
                    <h3>2.1 To Provide and Maintain Our Service</h3>
                    <ul class="bullet-list">
                        <li>Create and manage your account</li>
                        <li>Process and deliver scan results</li>
                        <li>Allow you to access your scan history (if you choose to save it)</li>
                        <li>Improve our service functionality and user experience</li>
                    </ul>

                    <h3>2.2 For Security and Legal Purposes</h3>
                    <ul class="bullet-list">
                        <li>Prevent fraudulent activities and unauthorized access</li>
                        <li>Comply with legal obligations</li>
                        <li>Protect our rights, privacy, safety, or property</li>
                        <li>Respond to law enforcement requests or legal process</li>
                    </ul>

                    <h3>2.3 For Communication</h3>
                    <ul class="bullet-list">
                        <li>Respond to your inquiries and support requests</li>
                        <li>Send service-related notices and updates</li>
                    </ul>

                    <h3>2.4 Data Sharing Policy</h3>
                    <p class="no-bullet">We do not share, sell, rent, or trade your personal information or scan data with any third parties for any purpose. Your information is used exclusively to provide you with our service and for the purposes outlined above.</p>
                    <p class="no-bullet">The only exception is the technical information collected by Cloudflare as our security provider, which is handled according to their privacy policy.</p>
                </div>

                <div class="terms-section">
                    <h2>3. Data Storage and Security</h2>
                    <h3>3.1 Data Storage</h3>
                    <ul class="bullet-list">
                        <li>All personal information is stored on secure servers located within the European Union</li>
                        <li>Scan data for registered users who choose to save their scans is encrypted when stored in our database</li>
                        <li>Saved scan data is automatically and irreversibly deleted after 7 days from the date it was saved</li>
                    </ul>

                    <h3>3.2 Data Security</h3>
                    <p class="no-bullet">We have implemented appropriate technical and organizational measures to secure your personal information and scan data, including:</p>
                    <ul class="bullet-list">
                        <li>Encryption of sensitive data at rest and in transit</li>
                        <li>Access controls limiting data access to authorized personnel only</li>
                        <li>Regular security assessments and updates</li>
                        <li>Staff training on data protection</li>
                    </ul>
                    <p class="no-bullet">Despite our efforts, no method of electronic transmission or storage is 100% secure. While we strive to use commercially acceptable means to protect your information, we cannot guarantee its absolute security.</p>
                </div>

                <div class="terms-section">
                    <h2>4. Data Sharing and Disclosure</h2>
                    <p class="no-bullet">We do not sell, trade, or otherwise transfer your personal information or scan data to third parties.</p>
                    <p class="no-bullet">We may disclose your information only in the following limited circumstances:</p>
                    <ul class="bullet-list">
                        <li>To comply with legal obligations</li>
                        <li>To enforce our Terms and Conditions</li>
                        <li>To protect our rights, privacy, safety, or property</li>
                        <li>To respond to an emergency</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>5. Your Rights and Choices</h2>
                    <p class="no-bullet">Depending on your location, you may have certain rights regarding your personal information, including:</p>
                    <ul class="bullet-list">
                        <li>Right to access your personal information</li>
                        <li>Right to rectify inaccurate or incomplete information</li>
                        <li>Right to erasure of your personal information</li>
                        <li>Right to restrict or object to processing</li>
                        <li>Right to data portability</li>
                        <li>Right to withdraw consent</li>
                    </ul>
                    <p class="no-bullet">To exercise these rights, please contact us using the information provided in the "Contact Us" section below.</p>
                </div>

                <div class="terms-section">
                    <h2>6. Retention of Data</h2>
                    <p class="no-bullet">We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.</p>
                    <p class="no-bullet">For registered users who save scan results, the scan data is automatically and irreversibly deleted after 7 days from the date it was saved.</p>
                </div>

                <div class="terms-section">
                    <h2>7. International Data Transfers</h2>
                    <p class="no-bullet">Your information may be transferred to and processed in countries other than the one in which you reside. These countries may have data protection laws that differ from those in your country.</p>
                    <p class="no-bullet">By using our service, you consent to the transfer of your information to Italy and/or other countries for the purposes described in this Privacy Policy.</p>
                </div>

                <div class="terms-section">
                    <h2>8. Cookies and Similar Technologies</h2>
                    <p class="no-bullet">We use cookies and similar tracking technologies to track activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
                </div>

                <div class="terms-section">
                    <h2>9. Changes to This Privacy Policy</h2>
                    <p class="no-bullet">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                    <p class="no-bullet">You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
                </div>

                <div class="terms-section">
                    <h2>10. Contact Us</h2>
                    <p class="no-bullet">If you have any questions about this Privacy Policy, please contact us at: <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="89fafcf9f9e6fbfdc9e4fbfdeaa7eaea">[email&#160;protected]</a></p>
                </div>

                <div class="terms-section acceptance">
                    <h2>Acceptance of Privacy Policy</h2>
                    <p class="no-bullet">
                        By using PatchPulse, you acknowledge that you have read and understood this Privacy Policy
                        and agree to its terms. If you disagree with any aspect of this policy, please do not use our service.
                    </p>
                </div>
            </div>

            <!-- Scroll to top button -->
            <div class="scroll-top">↑</div>

            <!-- Footer -->
            <footer class="mobile-footer">
                <div class="footer-links">
                    <a href="#">
                        <img src="../images/arrow1.png" alt="" class="footer-arrow" />
                        Github
                    </a>
                    <a href="#">
                        <img src="../images/arrow1.png" alt="" class="footer-arrow" />
                        Contact Us
                    </a>
                    <a href="#">
                        <img src="../images/arrow1.png" alt="" class="footer-arrow" />
                        Our Organization
                    </a>
                </div>
            </footer>
        </div>

        <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
        <script src="../script_mobile.js"></script>
        <script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="a3a23de76c684abb01991795-|49" defer></script>

        <!-- Additional script for the scroll-to-top button -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const scrollTopButton = document.querySelector(".scroll-top");

                // Show/hide scroll button based on scroll position
                window.addEventListener("scroll", function () {
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