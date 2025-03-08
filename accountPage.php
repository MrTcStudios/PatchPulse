<?php
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: https://mrtc.cc");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    session_destroy();
    header("Location: https://mrtc.cc");
    exit();
}

$stmt->close();




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

// Dimensione approssimativa
$storage_used += $scan_count * 100; // ~100 bytes di overhead per record

// Converte da bytes a MB
$storage_used = round($storage_used / (1024 * 1024), 2); // Arrotonda a 2 decimali
$total_storage = 1000; // MB
$storage_percentage = ($storage_used / $total_storage) * 100;

// Arrotonda a 1 decimale e limita al 100%
$storage_percentage = min(100, round($storage_percentage, 1));




$message_password = isset($_SESSION['password_change_message']) ? $_SESSION['password_change_message'] : '';
$message_email = isset($_SESSION['email_change_message']) ? $_SESSION['email_change_message'] : '';

unset($_SESSION['password_change_message']);
unset($_SESSION['email_change_message']);
?>


<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx.css" />
        <title>PatchPulse - Account</title>
        <meta charset="UTF-8" />

	<script>
            document.addEventListener('DOMContentLoaded', function() {
                const storageBar = document.getElementById('storageBar');
                const usedStorage = document.getElementById('usedStorage');
                const totalStorage = document.getElementById('totalStorage');
                
                storageBar.style.width = '<?php echo $storage_percentage; ?>%';
                usedStorage.textContent = '<?php echo $storage_used; ?>';
                totalStorage.textContent = '<?php echo $total_storage; ?>';
                
                if (<?php echo $storage_percentage; ?> > 90) {
                    storageBar.style.backgroundColor = '#ff4d4d';
                } else if (<?php echo $storage_percentage; ?> > 70) {
                    storageBar.style.backgroundColor = '#ffa64d';
                } else {
                    storageBar.style.backgroundColor = '#4CAF50';
                }
            });
        </script>
    </head>
    <body>
        <section class="layout">
            <div class="header">
                <img
                    class="headerIcon"
                    src="images/PatchPulseLogo.svg"
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
                    ><a href="mailto:support@mrtc.cc" class="headerButton"
                        >Contact Us</a
                    ></span
                >
                <button class="inout-button" onclick="window.location.href='logout.php'">Log Out</button>
            </div>
        </section>

        <h1 class="logRegText">Account Management</h1>


		<div class="account-container">
            <div class="account-section">
                <h2>Account Overview</h2>
                <div class="storage-info">
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
            </div>

	    <div class="account-section">
    <h2>Security Settings</h2>
    <form class="settings-form" action="dataBase/change_password.php" method="post">
        <div class="form-group">
            <h3>Change Password</h3>
            <input
                type="password"
                class="form1"
                name="current_password"
                placeholder="Current Password"
                required
            />
            <input
                type="password"
                class="form1"
                name="new_password"
                placeholder="New Password"
                required
            />
            <input
                type="password"
                class="form1"
                name="confirm_new_password"
                placeholder="Confirm New Password"
                required
            />
            <button type="submit" class="action-button" name="change_password">
                Update Password
            </button>
        </div>
    </form>
    
    <?php if (!empty($message_password)): ?>
    <p style="text-align: center; font-weight: bold;">
        <?php echo htmlspecialchars($message_password); ?>
    </p>
    <?php endif; ?>


    <form class="settings-form" action="dataBase/change_email.php" method="post">
        <div class="form-group">
            <h3>Change Email</h3>
            <input
                type="email"
                class="form1"
                name="current_email"
                placeholder="Current Email"
                required
            />
            <input
                type="email"
                class="form1"
                name="new_email"
                placeholder="New Email"
                required
            />
            <button type="submit" class="action-button" name="change_email">
                Update Email
            </button>
        </div>
    </form>
    
    <?php if (!empty($message_email)): ?>
    <p style="text-align: center; font-weight: bold;">
        <?php echo htmlspecialchars($message_email); ?>
    </p>
    <?php endif; ?>
</div>



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

    <button type="submit" class="action-button warning" onclick="window.location.href='dataBase/clear_logs.php'">Clear Logs</button>
</div>


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



<div class="account-section danger-zone">
    <h2>Danger Zone</h2>
    <p class="warning-text">These actions cannot be undone!</p>
    <div class="danger-actions">
        <div class="action-button delete">Delete All Logs</div>
        <div class="action-button delete" onclick="confirmDelete()">Delete Account</div>
    </div>
    
    <form id="deleteAccountForm" action="dataBase/delete_account.php" method="post" style="display: none;">
        <input type="hidden" name="delete_account" value="1">
    </form>
    
    <?php if (isset($message_delete) && $message_delete): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message_delete); ?></p>
    <?php endif; ?>
</div>

<script>
function confirmDelete() {
    if (confirm('Sei sicuro di voler eliminare il tuo account? Questa azione è irreversibile.')) {
        document.getElementById('deleteAccountForm').submit();
    }
}
</script>


</div>

        <hr class="sectionLine" />
        <div class="fot1">
            <ul>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Github</a>
                </li>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Contact Us</a>
                </li>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton">Our Organization</a>
                </li>
            </ul>
        </div>
        <hr class="sectionLine" />
    </body>
</html>
