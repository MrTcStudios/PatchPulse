<?php
include("../config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: https://mrtc.cc");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../accountPage.php");
    exit();
}

$scan_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM scans WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $scan_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../accountPage.php");
    exit();
}

$scan = $result->fetch_assoc();
$stmt->close();

$details = [];
$stmt = $conn->prepare("SELECT parameter, value, risk_level FROM scan_details WHERE scan_id = ?");
$stmt->bind_param("i", $scan_id);
$stmt->execute();
$details_result = $stmt->get_result();
while($row = $details_result->fetch_assoc()) {
    $details[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/style.css" />
        <link rel="stylesheet" type="text/css" href="../css/cssfx.css" />
        <title>PatchPulse - Scan Details</title>
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
                <span
                    ><a href="https://mrtc.cc" class="headerButton">Home</a></span
                >
                <span
                    ><div class="dropdown">
                        <a href="#" class="headerButton">Tool</a>
                        <div class="dropdown-content">
                            <a href="#">Fast Scan</a>
                            <a href="#">Web Scan</a>
                            <a href="#">Port Scan</a>
                            <a href="#">Custom Scan</a>
                        </div>
                    </div></span
                >
                <span
                    ><a href="" class="headerButton"
                        >Contact Us</a
                    ></span
                >
                <button class="inout-button">Log Out</button>
            </div>
        </section>

        <h1 class="logRegText">Scan Details</h1>

        <div class="scan-details-container">
            <div class="scan-header">
                <div>
                    <div class="scan-id">Scan ID: #<?php echo htmlspecialchars($scan['id']); ?></div>
                    <div class="scan-date"><?php echo htmlspecialchars($scan['created_at']); ?></div>
                </div>
            </div>
            
            <div class="scan-summary">

    <div class="summary-item">
        <div class="item-label">Cookies</div>
        <div class="item-value <?php echo ($scan['cookiesEnabled'] == "Sì") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['cookiesEnabled']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Do Not Track</div>
        <div class="item-value <?php echo ($scan['doNotTrack'] == "Disattivato") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['doNotTrack']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Browser Fingerprinting</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['browserFingerprinting'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebRTC Support</div>
        <div class="item-value <?php echo ($scan['webrtcSupport'] == "Abilitato") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['webrtcSupport']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">HTTPS Only</div>
        <div class="item-value <?php echo ($scan['httpsOnly'] == "Attivo") ? 'success' : 'warning'; ?>">
            <?php echo htmlspecialchars($scan['httpsOnly']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Ad Block</div>
        <div class="item-value <?php echo ($scan['adBlockEnabled'] == "Sì") ? 'success' : 'warning'; ?>">
            <?php echo htmlspecialchars($scan['adBlockEnabled']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Blocked Resources</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['blockedResources'] ?: "None"); ?>
        </div>
    </div>

    <div class="summary-item">
        <div class="item-label">JavaScript Status</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['javascriptStatus'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebGL Fingerprinting</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['webglFingerprinting'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Developer Mode</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['developerMode'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebAssembly Support</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['webAssemblySupport'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Web Workers Supported</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['webWorkersSupported'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Media Queries Supported</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['mediaQueriesSupported'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Web Notifications Supported</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['webNotificationsSupported'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Permissions API Supported</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['permissionsAPISupported'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Payment Request API Supported</div>
        <div class="item-value <?php echo ($scan['paymentRequestAPISupported'] == "Sì") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['paymentRequestAPISupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">HTML/CSS Support</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['htmlCssSupport'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Geolocation Info</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['geolocationInfo'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Sensors Supported</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['sensorsSupported'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Popups Enabled</div>
        <div class="item-value <?php echo ($scan['popupsEnabled'] == "Sì") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['popupsEnabled']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Public IPv4</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['publicIpv4'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Public IPv6</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['publicIpv6'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Browser Type</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['browserType'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Browser Version</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['browserVersion'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Browser Language</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['browserLanguage'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">OS Version</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['osVersion'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Incognito Mode</div>
        <div class="item-value <?php echo ($scan['incognitoMode'] == "NON attiva (Brave)." || $scan['incognitoMode'] == "NON attiva (Chrome)." || $scan['incognitoMode'] == "NON attiva (Brave)." || $scan['incognitoMode'] == "NON attiva (Firefox)." || $scan['incognitoMode'] == "NON attiva (Safari).") ? 'warning' : 'success'; ?>">
            <?php echo htmlspecialchars($scan['incognitoMode']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Device Memory</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['deviceMemory'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">CPU Threads</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['cpuThreads'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">CPU Cores</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['cpuCores'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">GPU Name</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['gpuName'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Color Depth</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['colorDepth'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Pixel Depth</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['pixelDepth'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Touch Support</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['touchSupport'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Screen Resolution</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['screenResolution'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">MIME Types</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['mimeTypes'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Referrer Policy</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['referrerPolicy'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Battery Status</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['batteryStatus'] ?: "Unknown"); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Security Protocols</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['securityProtocols'] ?: "Unknown"); ?>
        </div>
    </div>
</div>
            
            <?php if (count($details) > 0): ?>
            <div class="details-section">
                <h3>Detailed Results</h3>
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Value</th>
                            <th>Risk Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detail['parameter']); ?></td>
                            <td><?php echo htmlspecialchars($detail['value']); ?></td>
                            <td class="risk-<?php echo strtolower(htmlspecialchars($detail['risk_level'])); ?>">
                                <?php echo htmlspecialchars($detail['risk_level']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="recommendations">
    <h3>Security recommendations</h3>
    
    <?php if ($scan['cookiesEnabled'] == "Sì"): ?>
    <div class="recommendation-item">
        <strong>🍪 Cookie Tracking Reduction:</strong>
        Consider disabling third-party cookies or using your browser's private/incognito mode. Cookies can track your online activities across different websites, potentially compromising your digital privacy.
    </div>
    <?php endif; ?>
    
    <?php if ($scan['doNotTrack'] == "Disattivato"): ?>
    <div class="recommendation-item">
        <strong>🚫 Activate Do Not Track:</strong>
        Enable the "Do Not Track" setting in your browser. This sends a signal to websites requesting them to stop tracking your browsing habits, providing an additional layer of privacy protection.
    </div>
    <?php endif; ?>
    
    <?php if ($scan['browserFingerprinting']): ?>
    <div class="recommendation-item">
        <strong>🕵️ Reduce Digital Fingerprinting:</strong>
        Install browser extensions like Privacy Badger or Canvas Blocker to minimize digital fingerprinting. These tools help prevent websites from uniquely identifying your browser based on its characteristics.
    </div>
    <?php endif; ?>
    
    <?php if ($scan['webrtcSupport'] == "Abilitato"): ?>
    <div class="recommendation-item">
        <strong>🌐 WebRTC Privacy Protection:</strong>
        Consider using a WebRTC blocking extension. WebRTC can potentially leak your real IP address even when using a VPN, compromising your online anonymity.
    </div>
    <?php endif; ?>
    
    <?php if ($scan['httpsOnly'] == "Non Attivo"): ?>
    <div class="recommendation-item">
        <strong>🔒 Secure Browsing Mode:</strong>
        Activate "HTTPS-Only Mode" in your browser. This ensures that all your web communications are encrypted, protecting your data from potential interceptors and man-in-the-middle attacks.
    </div>
    <?php endif; ?>
    
    <?php if ($scan['adBlockEnabled'] == "No"): ?>
    <div class="recommendation-item">
        <strong>🛡️ Ad Blocking for Enhanced Security:</strong>
        Install an ad blocker to reduce tracking, minimize potential malware risks, and improve your browsing experience. Ad blockers can prevent intrusive ads and reduce the risk of malicious content.
    </div>
    <?php endif; ?>
</div>
            
            <a href="../accountPage.php" class="back-button">Back to Account</a>
        </div>

        <hr class="sectionLine" />
        <!-- Footer section -->
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
        <?php $conn->close(); ?>
    </body>
</html>