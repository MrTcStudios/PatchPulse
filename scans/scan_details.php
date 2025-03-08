<?php
// NON ULTIMATO, DA RIVEDERE

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

// Recupera i dettagli della scansione assicurandosi che appartenga all'utente corrente
$stmt = $conn->prepare("
    SELECT * FROM scans WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $scan_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Se la scansione non esiste o non appartiene all'utente, reindirizza alla pagina account
if ($result->num_rows === 0) {
    header("Location: ../accountPage.php");
    exit();
}

$scan = $result->fetch_assoc();
$stmt->close();

// Recupera i dettagli aggiuntivi dalla tabella scan_details
$details = [];
$stmt = $conn->prepare("SELECT parameter, value, risk_level FROM scan_details WHERE scan_id = ?");
$stmt->bind_param("i", $scan_id);
$stmt->execute();
$details_result = $stmt->get_result();
while($row = $details_result->fetch_assoc()) {
    $details[] = $row;
}
$stmt->close();

// Converti i valori booleani in testo per la visualizzazione
function boolToText($value) {
    if ($value === "1" || $value === true || $value === "true") return "Enabled";
    if ($value === "0" || $value === false || $value === "false") return "Disabled";
    return $value ?: "Unknown";
}

// Funzione per determinare il livello di rischio basato sui risultati della scansione (DA RIVEDERE MOLTO)
function calculateRiskLevel($scan) {
    $risk_factors = 0;
    
    if ($scan['cookiesEnabled'] == "1") $risk_factors++;
    if ($scan['doNotTrack'] == "0") $risk_factors++;
    if ($scan['browserFingerprinting'] == "1") $risk_factors += 2;
    if ($scan['webrtcSupport'] == "1") $risk_factors++;
    if ($scan['httpsOnly'] == "0") $risk_factors += 2;
    if ($scan['adBlockEnabled'] == "0") $risk_factors++;

    // Aggiunge i nuovi campi al calcolo del rischio (DA RIVEDERE COMPLETAMENTE)
    if ($scan['javascriptStatus'] == "1") $risk_factors++;
    if ($scan['webglFingerprinting'] == "1") $risk_factors += 2;
    if ($scan['developerMode'] == "1") $risk_factors++;
    if ($scan['webAssemblySupport'] == "1") $risk_factors++;
    if ($scan['webWorkersSupported'] == "1") $risk_factors++;
    if ($scan['mediaQueriesSupported'] == "1") $risk_factors++;
    if ($scan['webNotificationsSupported'] == "1") $risk_factors++;
    if ($scan['permissionsAPISupported'] == "1") $risk_factors++;
    if ($scan['paymentRequestAPISupported'] == "1") $risk_factors++;
    if ($scan['htmlCssSupport'] == "0") $risk_factors++;
    if ($scan['geolocationInfo'] == "1") $risk_factors++;
    if ($scan['sensorsSupported'] == "1") $risk_factors++;
    if ($scan['popupsEnabled'] == "1") $risk_factors++;
    if ($scan['publicIpv4'] == "1") $risk_factors++;
    if ($scan['publicIpv6'] == "1") $risk_factors++;
    if ($scan['browserType'] == "Chrome") $risk_factors++;
    if ($scan['browserVersion'] < 90) $risk_factors++;
    if ($scan['osVersion'] < 10) $risk_factors++;
    if ($scan['incognitoMode'] == "0") $risk_factors++;
    if ($scan['deviceMemory'] < 4) $risk_factors++;
    if ($scan['cpuThreads'] < 4) $risk_factors++;
    if ($scan['cpuCores'] < 2) $risk_factors++;
    if ($scan['gpuName'] == "Unknown") $risk_factors++;
    if ($scan['colorDepth'] < 24) $risk_factors++;
    if ($scan['pixelDepth'] < 24) $risk_factors++;
    if ($scan['touchSupport'] == "1") $risk_factors++;
    if ($scan['screenResolution'] < 1080) $risk_factors++;
    if ($scan['mimeTypes'] == "Unknown") $risk_factors++;
    if ($scan['referrerPolicy'] == "no-referrer") $risk_factors++;
    if ($scan['batteryStatus'] == "Low") $risk_factors++;
    if ($scan['securityProtocols'] == "Weak") $risk_factors++;
    
    if ($risk_factors <= 2) return ["Low", "#4CAF50"];
    if ($risk_factors <= 5) return ["Medium", "#FFC107"];
    return ["High", "#F44336"];
}

$risk_info = calculateRiskLevel($scan);
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
                <div class="scan-risk" style="background-color: <?php echo $risk_info[1]; ?>">
                    Risk Level: <?php echo $risk_info[0]; ?>
                </div>
            </div>
            
            <div class="scan-summary">
    <div class="summary-item">
        <div class="item-label">Cookies</div>
        <div class="item-value <?php echo ($scan['cookiesEnabled'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['cookiesEnabled']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Do Not Track</div>
        <div class="item-value <?php echo ($scan['doNotTrack'] == "1") ? 'success' : 'warning'; ?>">
            <?php echo boolToText($scan['doNotTrack']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Browser Fingerprinting</div>
        <div class="item-value <?php echo ($scan['browserFingerprinting'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['browserFingerprinting']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebRTC Support</div>
        <div class="item-value <?php echo ($scan['webrtcSupport'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['webrtcSupport']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">HTTPS Only</div>
        <div class="item-value <?php echo ($scan['httpsOnly'] == "1") ? 'success' : 'warning'; ?>">
            <?php echo boolToText($scan['httpsOnly']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Ad Block</div>
        <div class="item-value <?php echo ($scan['adBlockEnabled'] == "1") ? 'success' : 'warning'; ?>">
            <?php echo boolToText($scan['adBlockEnabled']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Blocked Resources</div>
        <div class="item-value neutral">
            <?php echo htmlspecialchars($scan['blockedResources'] ?: "None"); ?>
        </div>
    </div>

    <!-- Nuovi campi aggiunti -->
    <div class="summary-item">
        <div class="item-label">JavaScript Status</div>
        <div class="item-value <?php echo ($scan['javascriptStatus'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['javascriptStatus']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebGL Fingerprinting</div>
        <div class="item-value <?php echo ($scan['webglFingerprinting'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['webglFingerprinting']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Developer Mode</div>
        <div class="item-value <?php echo ($scan['developerMode'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['developerMode']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">WebAssembly Support</div>
        <div class="item-value <?php echo ($scan['webAssemblySupport'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['webAssemblySupport']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Web Workers Supported</div>
        <div class="item-value <?php echo ($scan['webWorkersSupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['webWorkersSupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Media Queries Supported</div>
        <div class="item-value <?php echo ($scan['mediaQueriesSupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['mediaQueriesSupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Web Notifications Supported</div>
        <div class="item-value <?php echo ($scan['webNotificationsSupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['webNotificationsSupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Permissions API Supported</div>
        <div class="item-value <?php echo ($scan['permissionsAPISupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['permissionsAPISupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Payment Request API Supported</div>
        <div class="item-value <?php echo ($scan['paymentRequestAPISupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['paymentRequestAPISupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">HTML/CSS Support</div>
        <div class="item-value <?php echo ($scan['htmlCssSupport'] == "1") ? 'success' : 'warning'; ?>">
            <?php echo boolToText($scan['htmlCssSupport']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Geolocation Info</div>
        <div class="item-value <?php echo ($scan['geolocationInfo'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['geolocationInfo']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Sensors Supported</div>
        <div class="item-value <?php echo ($scan['sensorsSupported'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['sensorsSupported']); ?>
        </div>
    </div>
    
    <div class="summary-item">
        <div class="item-label">Popups Enabled</div>
        <div class="item-value <?php echo ($scan['popupsEnabled'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['popupsEnabled']); ?>
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
        <div class="item-value <?php echo ($scan['incognitoMode'] == "1") ? 'success' : 'warning'; ?>">
            <?php echo boolToText($scan['incognitoMode']); ?>
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
        <div class="item-value <?php echo ($scan['touchSupport'] == "1") ? 'warning' : 'success'; ?>">
            <?php echo boolToText($scan['touchSupport']); ?>
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
                <h3>Security Recommendations</h3>
                <?php if ($scan['cookiesEnabled'] == "1"): ?>
                <div class="recommendation-item">
                    Consider disabling cookies or using browser's privacy mode to reduce tracking.
                </div>
                <?php endif; ?>
                
                <?php if ($scan['doNotTrack'] == "0"): ?>
                <div class="recommendation-item">
                    Enable "Do Not Track" in your browser settings to request websites not to track your browsing.
                </div>
                <?php endif; ?>
                
                <?php if ($scan['browserFingerprinting'] == "1"): ?>
                <div class="recommendation-item">
                    Use a browser extension that reduces fingerprinting, such as Privacy Badger or Canvas Blocker.
                </div>
                <?php endif; ?>
                
                <?php if ($scan['webrtcSupport'] == "1"): ?>
                <div class="recommendation-item">
                    Consider using a WebRTC blocking extension if privacy is a concern.
                </div>
                <?php endif; ?>
                
                <?php if ($scan['httpsOnly'] == "0"): ?>
                <div class="recommendation-item">
                    Enable "HTTPS-Only Mode" in your browser for enhanced security.
                </div>
                <?php endif; ?>
                
                <?php if ($scan['adBlockEnabled'] == "0"): ?>
                <div class="recommendation-item">
                    Consider using an ad blocker to reduce tracking and improve security.
                </div>
                <?php endif; ?>
            </div>
            
            <a href="../accountPage.php" class="back-button">Back to Account</a>
        </div>

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
        <?php $conn->close(); ?>
    </body>
</html>
