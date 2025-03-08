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

	<!-- Terms content -->
        <div class="terms-content">
            <h1>Terms and Conditions</h1>
            <p class="last-update">Last updated: March 7, 2025</p>
            
            <div class="terms-intro">
                <p>Welcome to PatchPulse, a web security scanning service. By using our service, you agree to comply with the following Terms and Conditions. Please read the information below carefully.</p>
            </div>

            <div class="terms-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using our service, you agree to comply with these Terms and Conditions. If you do not accept these terms, please do not use our site and service.</p>
            </div>

            <div class="terms-section">
                <h2>2. Service Usage</h2>
                <p>Our service allows users to perform security scans of websites by entering a URL. Users are responsible for ensuring they have explicit legal permission to scan the specified website.</p>
                <p>Using our service without website owner's permission may violate applicable laws regarding unauthorized access and computer tampering.</p>
            </div>

            <div class="terms-section">
                <h2>3. User Responsibility</h2>
                <p>The user declares and guarantees that:</p>
                <p>- They have obtained explicit permission from the website owner they intend to scan.</p>
                <p>- They will use the service only for legitimate purposes and not to damage, alter, or compromise the integrity of scanned websites.</p>
                <p>- They are responsible for any legal damage resulting from improper use of our service.</p>
            </div>

            <div class="terms-section">
                <h2>4. Limitation of Liability</h2>
                <p>PatchPulse is not responsible for:</p>
                <p>- Damage to computer systems or devices caused by using our service.</p>
                <p>- Any damages resulting from the use of information obtained through performed scans.</p>
                <p>- Claims, liabilities, or damages resulting from legal actions related to unauthorized or non-permitted scans.</p>
            </div>

            <div class="terms-section">
                <h2>5. Scan Data Storage and Management</h2>
                
                <h3>For Users Without Accounts</h3>
                <p>If you use our service without creating an account, no scan data will be saved in our system. All scan results are temporary and will be automatically deleted once your session ends. We do not store or process any data from these scans beyond what is necessary to deliver the immediate results to you.</p>
                
                <h3>For Users With Accounts</h3>
                <p>If you have created a PatchPulse account, you have the option to save your scan results for future reference. This data will only be saved if you explicitly choose to save it to your scan history. All saved scan data is treated with strict confidentiality:</p>
                <p>- Data is encrypted when stored in our database</p>
                <p>- Data is only decrypted when you access your scan history through your account</p>
                <p>- All saved scan data is automatically and irreversibly deleted after 7 days from the date it was saved</p>
                
                <p>The saved data includes all information detected during scans, which may include sensitive data or vulnerabilities identified during the scanning process. We take extensive security measures to protect this information through encryption and secure access controls.</p>
            </div>

            <div class="terms-section">
                <h2>6. Privacy and Data Protection</h2>
                <p>We collect data exclusively for service operation and to provide you with access to scan history (for registered users who choose to save their scans). The collected data is not used for marketing purposes and is not shared with third parties, except as required by law.</p>
                <p>For more details on how we manage your privacy, please consult our Privacy Policy.</p>
            </div>

            <div class="terms-section">
                <h2>7. Terms Modifications</h2>
                <p>We reserve the right to modify, update, or revise these Terms and Conditions at any time, without notice. Changes will be effective as soon as they are published on our site. We encourage you to regularly check this page to stay informed about any changes.</p>
            </div>

            <div class="terms-section">
                <h2>8. Applicable Law and Dispute Resolution</h2>
                <p>These Terms and Conditions are governed by the laws in force in Italy and any dispute will be resolved exclusively in the competent courts of Milan, Italy.</p>
            </div>

            <div class="terms-section">
                <h2>9. Contact</h2>
                <p>For any questions or clarifications about these Terms and Conditions, you can contact us at: support@mrtc.cc</p>
            </div>
            
            <div class="terms-section acceptance">
                <h2>Acceptance of Terms</h2>
                <p>By using our service, you acknowledge that you have read, understood, and accepted these Terms and Conditions. If you disagree with any of these terms, please do not use our service.</p>
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
