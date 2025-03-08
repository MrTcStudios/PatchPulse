<?php
include("../config.php");
?>
<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/style.css" />
        <link rel="stylesheet" type="text/css" href="../css/cssfx.css" />
        <title>PatchPulse - Terms & Conditions</title>
        <meta charset="UTF-8" />
    </head>
    <body>
        <section class="layout">
            <div class="header">
                <img
                    class="headerIcon"
                    src="../images/PatchPulseLogo.svg"
                    alt="CentredLogo"
                />
                <span><a href="https://mrtc.cc" class="headerButton">Home</a></span>
                <span><div class="dropdown">
                    <a href="#" class="headerButton">Tool</a>
                    <div class="dropdown-content">
                        <a href="#">Fast Scan</a>
                        <a href="#">Web Scan</a>
                        <a href="#">Port Scan</a>
                        <a href="#">Custom Scan</a>
                    </div>
                </div></span>
                <span><a href="" class="headerButton">Contact Us</a></span>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="inout-button" onclick="window.location.href='../accountPage.php'">Account</button>
                <?php else: ?>
                    <button class="inout-button" onclick="window.location.href='../loginPage.php'">Log In / Sign Up</button>
                <?php endif; ?>
            </div>

            <div class="terms-content">
    <h1>Privacy Policy</h1>
    <p class="last-update">Last updated: March 7, 2025</p>
    
    <div class="terms-intro">
        <p>PatchPulse ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our web security scanning service.</p>
        <p>Please read this Privacy Policy carefully. By accessing or using PatchPulse, you acknowledge that you have read, understood, and agree to be bound by all the terms outlined in this Privacy Policy. If you do not agree with our policies and practices, please do not use our service.</p>
    </div>

    <div class="terms-section">
        <h2>1. Information We Collect</h2>
        
        <h3>1.1 Personal Information</h3>
        <p>When you create an account with PatchPulse, we may collect the following personal information:</p>
        <p>- Email address</p>
        <p>- Username</p>
        <p>- Password (stored in encrypted form)</p>
        <p>- IP address</p>
        
        <h3>1.2 Scan Data</h3>
        
        <h4>1.2.1 Users Without Accounts</h4>
        <p>If you use our service without creating an account, we do not save any scan data in our system. All scan results are temporary and will be automatically deleted once your session ends.</p>
        
        <h4>1.2.2 Users With Accounts</h4>
        <p>If you have a PatchPulse account and explicitly choose to save your scan results, we collect and store:</p>
        <p>- URLs of websites you scan</p>
        <p>- Timestamps of when scans are performed</p>
        <p>- Technical details discovered during scans, including but not limited to:</p>
        <p>&nbsp;&nbsp;- Identified vulnerabilities</p>
        <p>&nbsp;&nbsp;- Server configuration details</p>
        <p>&nbsp;&nbsp;- Open ports</p>
        <p>&nbsp;&nbsp;- Security headers</p>
        <p>&nbsp;&nbsp;- Other technical information related to the websites being scanned</p>
        <p>Some of this information may include sensitive data or vulnerabilities identified during the scanning process.</p>
        
        <h3>1.3 Technical Information</h3>
        <p>The following technical information is automatically collected and managed by our security provider Cloudflare when you visit our website:</p>
        <p>- Browser type and version</p>
        <p>- Operating system</p>
        <p>- Referral source</p>
        <p>- Time and date of access</p>
        <p>- Pages visited</p>
        <p>We do not have direct access to or control over this information, which is collected and processed according to Cloudflare's own privacy policy.</p>
    </div>

    <div class="terms-section">
        <h2>2. How We Use Your Information</h2>
        
        <h3>2.1 To Provide and Maintain Our Service</h3>
        <p>- Create and manage your account</p>
        <p>- Process and deliver scan results</p>
        <p>- Allow you to access your scan history (if you choose to save it)</p>
        <p>- Improve our service functionality and user experience</p>
        
        <h3>2.2 For Security and Legal Purposes</h3>
        <p>- Prevent fraudulent activities and unauthorized access</p>
        <p>- Comply with legal obligations</p>
        <p>- Protect our rights, privacy, safety, or property</p>
        <p>- Respond to law enforcement requests or legal process</p>
        
        <h3>2.3 For Communication</h3>
        <p>- Respond to your inquiries and support requests</p>
        <p>- Send service-related notices and updates</p>
        
        <h3>2.4 Data Sharing Policy</h3>
        <p>We do not share, sell, rent, or trade your personal information or scan data with any third parties for any purpose. Your information is used exclusively to provide you with our service and for the purposes outlined above.</p>
        <p>The only exception is the technical information collected by Cloudflare as our security provider, which is handled according to their privacy policy.</p>
    </div>

    <div class="terms-section">
        <h2>3. Data Storage and Security</h2>
        
        <h3>3.1 Data Storage</h3>
        <p>- All personal information is stored on secure servers located within the European Union</p>
        <p>- Scan data for registered users who choose to save their scans is encrypted when stored in our database</p>
        <p>- Saved scan data is automatically and irreversibly deleted after 7 days from the date it was saved</p>
        
        <h3>3.2 Data Security</h3>
        <p>We have implemented appropriate technical and organizational measures to secure your personal information and scan data, including:</p>
        <p>- Encryption of sensitive data at rest and in transit</p>
        <p>- Access controls limiting data access to authorized personnel only</p>
        <p>- Regular security assessments and updates</p>
        <p>- Staff training on data protection</p>
        <p>Despite our efforts, no method of electronic transmission or storage is 100% secure. While we strive to use commercially acceptable means to protect your information, we cannot guarantee its absolute security.</p>
    </div>

    <div class="terms-section">
        <h2>4. Data Sharing and Disclosure</h2>
        <p>We do not sell, trade, or otherwise transfer your personal information or scan data to third parties.</p>
        <p>We may disclose your information only in the following limited circumstances:</p>
        <p>- To comply with legal obligations</p>
        <p>- To enforce our Terms and Conditions</p>
        <p>- To protect our rights, privacy, safety, or property</p>
        <p>- To respond to an emergency</p>
    </div>

    <div class="terms-section">
        <h2>5. Your Rights and Choices</h2>
        <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
        <p>- Right to access your personal information</p>
        <p>- Right to rectify inaccurate or incomplete information</p>
        <p>- Right to erasure of your personal information</p>
        <p>- Right to restrict or object to processing</p>
        <p>- Right to data portability</p>
        <p>- Right to withdraw consent</p>
        <p>To exercise these rights, please contact us using the information provided in the "Contact Us" section below.</p>
    </div>

    <div class="terms-section">
        <h2>6. Retention of Data</h2>
        <p>We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.</p>
        <p>For registered users who save scan results, the scan data is automatically and irreversibly deleted after 7 days from the date it was saved.</p>
    </div>

    <div class="terms-section">
        <h2>7. International Data Transfers</h2>
        <p>Your information may be transferred to and processed in countries other than the one in which you reside. These countries may have data protection laws that differ from those in your country.</p>
        <p>By using our service, you consent to the transfer of your information to Italy and/or other countries for the purposes described in this Privacy Policy.</p>
    </div>

    <div class="terms-section">
        <h2>8. Cookies and Similar Technologies</h2>
        <p>We use cookies and similar tracking technologies to track activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
    </div>

    <div class="terms-section">
        <h2>9. Changes to This Privacy Policy</h2>
        <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
        <p>You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
    </div>

    <div class="terms-section">
        <h2>10. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at: support@mrtc.cc</p>
    </div>
</div>
    
        </section>
        <hr class="sectionLine" />
        <div class="fot1">
            <ul>
                <li>
                    <img class="arrowsFot" src="../images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Github</a>
                </li>
                <li>
                    <img class="arrowsFot" src="../images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Contact Us</a>
                </li>
                <li>
                    <img class="arrowsFot" src="../images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Our Organization</a>
                </li>
            </ul>
        </div>
        <hr class="sectionLine" />
        <script src="../script.js"></script>
    </body>
</html>
