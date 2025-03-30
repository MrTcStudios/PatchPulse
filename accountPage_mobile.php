<!-- accountPage_mobile.html -->

<?php
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: https://mrtc.cc"); // Reindirizza se non loggato
    exit();
}

// Verifica se l'utente esiste ancora
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // L'utente    stato eliminato, effettua il logout
    session_destroy();
    header("Location: https://mrtc.cc");
    exit();
}

$stmt->close();


// Ottiene tutte le colonne dalla tabella scans per calcolare la dimensione dei dati reali
$stmt = $conn->prepare(" 
    SELECT 
        id, cookiesEnabled, doNotTrack, browserFingerprinting, 
        webrtcSupport, httpsOnly, blockedResources, adBlockEnabled, javascriptStatus, 
        webglFingerprinting, developerMode, webAssemblySupport, webWorkersSupported, 
        mediaQueriesSupported, webNotificationsSupported, permissionsAPISupported, 
        paymentRequestAPISupported, htmlCssSupport, geolocationInfo, sensorsSupported, 
        popupsEnabled, publicIpv4, publicIpv6, browserType, browserVersion, 
        browserLanguage, osVersion, incognitoMode, deviceMemory, cpuThreads, cpuCores, 
        gpuName, colorDepth, pixelDepth, touchSupport, screenResolution, mimeTypes, 
        referrerPolicy, batteryStatus, securityProtocols, created_at 
    FROM scans 
    WHERE user_id = ? 
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$storage_used = 0; // In KB inizialmente
$scan_count = 0;

while ($row = $result->fetch_assoc()) {
    $scan_count++;
    
    // Calcola la dimensione in memoria di ogni riga di dati
    // Per ogni campo, otteniamo la lunghezza in bytes
    foreach ($row as $key => $value) {
        if ($value !== null) {
            $storage_used += strlen($value);
        }
    }
}

// Aggiungi la dimensione approssimativa del record stesso nel database (overhead)
$storage_used += $scan_count * 100; // ~100 bytes di overhead per record

// Converti da bytes a MB
$storage_used = round($storage_used / (1024 * 1024), 2); // Arrotonda a 2 decimali
$total_storage = 1000; // MB
$storage_percentage = ($storage_used / $total_storage) * 100;

// Arrotonda a 1 decimale e limita al 100%
$storage_percentage = min(100, round($storage_percentage, 1));

// Prosegui normalmente se l'utente esiste ancora
$message_password = isset($_SESSION['password_change_message']) ? $_SESSION['password_change_message'] : '';
$message_email = isset($_SESSION['email_change_message']) ? $_SESSION['email_change_message'] : '';

unset($_SESSION['password_change_message']);
unset($_SESSION['email_change_message']);
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style_mobile.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx_mobile.css" />
        <title>PatchPulse - Account</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
                        <a href="logout.php" class="login-btn">Log Out</a>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <a href="loginPage_mobile.php" class="login-btn">Log In / Sign Up</a>
                    <?php endif; ?>
        </div>

        <div class="header">
            <img class="headerIcon" src="images/PatchPulseLogo.svg" alt="Logo" />
        </div>

        <div class="account-container">
            <h1 class="page-title">Account</h1>

            <!-- Storage Section -->
            <div class="account-section">
                <h2>Storage</h2>
                <div class="storage-meter">
                        <div
                            class="storage-bar"
                            id="storageBar"
                            style="width: <?php echo $storage_percentage; ?>%"
                        ></div>
                    </div>
                    <p class="storage-text">
                        Storage Used: <span id="usedStorage"><?php echo $storage_used; ?></span>MB /
                        <span id="totalStorage"><?php echo $total_storage; ?></span>MB
                    </p>
                    <p class="storage-text">
                        Total Scans: <?php echo $scan_count; ?>
                    </p>
            </div>

            <!-- Security Section -->
            <div class="account-section">
                <h2>Security</h2>

                <!-- Password Change -->
             <form class="form-group" action="dataBase/change_password.php" method="post">
                <h3>Change Password</h3>
                <input type="password" placeholder="Current Password" name="current_password"/>
                <input type="password" placeholder="New Password" name="new_password"/>
                <input type="password" placeholder="Confirm Password" name="confirm_new_password"/>
                <button type="submit" class="action-button" name="change_password">Update</button>
            </form>

            <?php if (!empty($message_password)): ?>
            <p style="text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($message_password); ?>
            </p>
            <?php endif; ?>

                <!-- Email Change -->
            <form class="form-group" action="dataBase/change_email.php" method="post">
                <h3>Change Email</h3>
                <input type="email" placeholder="Current Email" name="current_email"/>
                <input type="email" placeholder="New Email" name="new_email"/>
                <button type="submit" class="action-button" name="change_email">Update</button>
            </form>

                <?php if (!empty($message_email)): ?>
            <p style="text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($message_email); ?>
            </p>
            <?php endif; ?>
            </div>

            <!-- Activity Log -->

            <div class="account-section">
    <h2>Activity Logs</h2>
    <div class="logs-container">
        <?php
        $stmt = $conn->prepare("SELECT action, ip_address, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($action, $ip_address, $created_at);

        while ($stmt->fetch()):
        ?>
            <div class="log-entry">
                <span class="log-date"><?php echo htmlspecialchars($created_at); ?></span>
                <span class="log-action"><?php echo htmlspecialchars($action); ?></span>
                <span class="log-ip">IP: <?php echo htmlspecialchars($ip_address); ?></span>
            </div>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    </div>

    <!-- Pulsante per cancellare i log -->
        <button type="submit" class="action-button warning" onclick="window.location.href='dataBase/clear_logs.php'">Clear Logs</button>
</div>

            <!-- Recent Scans Section -->
            <div class="account-section">
                <h2>Recent Scans</h2>
                <div class="logs-container">
                    <?php
                    $scan_stmt = $conn->prepare("SELECT id, cookiesEnabled, doNotTrack, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                    $scan_stmt->bind_param("i", $_SESSION['user_id']);
                    $scan_stmt->execute();
                    $scan_result = $scan_stmt->get_result();
                    
                    if ($scan_result->num_rows > 0) {
                        while ($scan = $scan_result->fetch_assoc()):
                    ?>
                        <div class="log-entry">
                            <span class="scan-date"><?php echo htmlspecialchars($scan['created_at']); ?></span>
                            <a href="scans/scan_details.php?id=<?php echo $scan['id']; ?>" class="scan-details-link">View Details</a>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        echo "<p>No scans found.</p>";
                    }
                    $scan_stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>

            <!-- Danger Zone Section -->
            <div class="account-section danger">
                <h2>Danger Zone</h2>
                <p class="warning-text">These actions cannot be undone!</p>
                <div class="danger-actions">
                    <div class="delete-button">Delete All Logs</div>
                    <div class="delete-button">Delete Account</div>
                </div>
            </div>
        </div>

        <!-- Mobile Footer -->
        <div class="mobile-footer">
            <div class="footer-links">
                <a href="#">
                    <img
                        class="footer-arrow"
                        src="images/arrow1.png"
                        alt=""
                    />Github
                </a>
                <a href="#">
                    <img
                        class="footer-arrow"
                        src="images/arrow1.png"
                        alt=""
                    />Contact Us
                </a>
                <a href="#">
                    <img class="footer-arrow" src="images/arrow1.png" alt="" />Our
                    Organization
                </a>
            </div>
        </div>
    </section>

        <script src="script_mobile.js"></script>
    </body>
</html>
