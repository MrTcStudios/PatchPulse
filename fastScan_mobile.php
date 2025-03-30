<?php
include("config.php");
?>

<!doctype html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style_mobile.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx_mobile.css" />
        <title>PatchPulse - Fast Scan</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
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
                        <a href="accountPage_mobile.php" class="login-btn">Account</a>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <a href="loginPage_mobile.php" class="login-btn">Log In / Sign Up</a>
                    <?php endif; ?>
        </div>

        <div class="header">
            <img class="headerIcon" src="images/PatchPulseLogo.svg" alt="Logo" />
        </div>
        <!-- Mobile -->
         
    <div class="scan-mode-selector">
        <button class="mode-button active" data-mode="webTracking">
            Web Tracking
        </button>
        <button class="mode-button" data-mode="functionality">
            Functionality
        </button>
        <button class="mode-button" data-mode="deviceInfo">
            Device Info
        </button>
    </div>
    
    <div class="scan-results">
        <div id="webTracking" class="result-section active">
            <div class="pro-mode-toggle">
                <button class="pro-button">PRO MODE</button>
            </div>
            <div class="result-items">
                <div class="result-item">
                    <div class="item-name">Cookies Tracking</div>
                    <div id="cookiesEnabled" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Do Not Track</div>
                    <div id="doNotTrack" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Browser Fingerprinting</div>
                    <div id="browserFingerprinting" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">WebRTC Support</div>
                    <div id="webrtcSupport" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">HTTPS Only</div>
                    <div id="httpsOnly" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Blocked Resources</div>
                    <div id="blockedResources" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">AdBlocker</div>
                    <div id="adBlockEnabled" class="status">Loading...</div>
                </div>
            </div>
        </div>

        <div id="functionality" class="result-section">
            <div class="pro-mode-toggle">
                <button class="pro-button">PRO MODE</button>
            </div>
            <div class="result-items">
                <div class="result-item">
                    <div class="item-name">JavaScript</div>
                    <div id="javascriptStatus" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">WebGL</div>
                    <div id="webglFingerprinting" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Developer Mode</div>
                    <div id="developerMode" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">WebAssembly</div>
                    <div id="webAssemblySupport" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Web Workers</div>
                    <div id="webWorkersSupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Media Queries</div>
                    <div id="mediaQueriesSupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Web Notifications</div>
                    <div id="webNotificationsSupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Permissions API</div>
                    <div id="permissionsAPISupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Payment Request API</div>
                    <div id="paymentRequestAPISupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">HTML5/CSS3</div>
                    <div id="htmlCssSupport" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Geolocation</div>
                    <div id="geolocationInfo" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Sensors Support</div>
                    <div id="sensorsSupported" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Pop-ups</div>
                    <div id="popupsEnabled" class="status">Loading...</div>
                </div>
            </div>
        </div>

        <div id="deviceInfo" class="result-section">
            <div class="pro-mode-toggle">
                <button class="pro-button">PRO MODE</button>
            </div>
            <div class="result-items">
                <div class="result-item">
                    <div class="item-name">Public IPv4</div>
                    <div id="publicIpv4" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Public IPv6</div>
                    <div id="publicIpv6" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Browser Type</div>
                    <div id="browserType" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Browser Version</div>
                    <div id="browserVersion" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Browser Language</div>
                    <div id="browserLanguage" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Operating System</div>
                    <div id="osVersion" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Incognito Mode</div>
                    <div id="incognitoMode" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Device Memory</div>
                    <div id="deviceMemory" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">CPU Threads</div>
                    <div id="cpuThreads" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">CPU Cores</div>
                    <div id="cpuCores" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">GPU Info</div>
                    <div id="gpuName" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Color Depth</div>
                    <div id="colorDepth" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Pixel Depth</div>
                    <div id="pixelDepth" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Touch Support</div>
                    <div id="touchSupport" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Screen Resolution</div>
                    <div class="status">
                        <span id="width">Loading...</span> x <span id="height">Loading...</span>
                    </div>
                </div>
                <div class="result-item">
                    <div class="item-name">MIME Types</div>
                    <div id="mimeTypes" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Referrer Policy</div>
                    <div id="referrerPolicy" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Battery Status</div>
                    <div id="batteryStatus" class="status">Loading...</div>
                </div>
                <div class="result-item">
                    <div class="item-name">Security Protocols</div>
                    <div id="securityProtocols" class="status">Loading...</div>
                </div>
            </div>
        </div>
    </div>


    <script>
          function saveScans() {
    console.log("Pulsante SAVE SCANS cliccato!");

    const data = new FormData();
    data.append("cookiesEnabled", document.getElementById("cookiesEnabled").innerText);
    data.append("doNotTrack", document.getElementById("doNotTrack").innerText);
    data.append("browserFingerprinting", document.getElementById("browserFingerprinting").innerText);
    data.append("webrtcSupport", document.getElementById("webrtcSupport").innerText);
    data.append("httpsOnly", document.getElementById("httpsOnly").innerText);
    data.append("blockedResources", document.getElementById("blockedResources").innerText);
    data.append("adBlockEnabled", document.getElementById("adBlockEnabled").innerText);

    data.append("javascriptStatus", document.getElementById("javascriptStatus").innerText);
    data.append("webglFingerprinting", document.getElementById("webglFingerprinting").innerText);
    data.append("developerMode", document.getElementById("developerMode").innerText);
    data.append("webAssemblySupport", document.getElementById("webAssemblySupport").innerText);
    data.append("webWorkersSupported", document.getElementById("webWorkersSupported").innerText);
    data.append("mediaQueriesSupported", document.getElementById("mediaQueriesSupported").innerText);
    data.append("webNotificationsSupported", document.getElementById("webNotificationsSupported").innerText);
    data.append("permissionsAPISupported", document.getElementById("permissionsAPISupported").innerText);
    data.append("paymentRequestAPISupported", document.getElementById("paymentRequestAPISupported").innerText);
    data.append("htmlCssSupport", document.getElementById("htmlCssSupport").innerText);
    data.append("geolocationInfo", document.getElementById("geolocationInfo").innerText);
    data.append("sensorsSupported", document.getElementById("sensorsSupported").innerText);
    data.append("popupsEnabled", document.getElementById("popupsEnabled").innerText);

    data.append("publicIpv4", document.getElementById("publicIpv4").innerText);
    data.append("publicIpv6", document.getElementById("publicIpv6").innerText);
    data.append("browserType", document.getElementById("browserType").innerText);
    data.append("browserVersion", document.getElementById("browserVersion").innerText);
    data.append("browserLanguage", document.getElementById("browserLanguage").innerText);
    data.append("osVersion", document.getElementById("osVersion").innerText);
    data.append("incognitoMode", document.getElementById("incognitoMode").innerText);
    data.append("deviceMemory", document.getElementById("deviceMemory").innerText);
    data.append("cpuThreads", document.getElementById("cpuThreads").innerText);
    data.append("cpuCores", document.getElementById("cpuCores").innerText);
    data.append("gpuName", document.getElementById("gpuName").innerText);
    data.append("colorDepth", document.getElementById("colorDepth").innerText);
    data.append("pixelDepth", document.getElementById("pixelDepth").innerText);
    data.append("touchSupport", document.getElementById("touchSupport").innerText);
    data.append("screenResolution", document.getElementById("width").innerText + " x " + document.getElementById("height").innerText);
    data.append("mimeTypes", document.getElementById("mimeTypes").innerText);
    data.append("referrerPolicy", document.getElementById("referrerPolicy").innerText);
    data.append("batteryStatus", document.getElementById("batteryStatus").innerText);
    data.append("securityProtocols", document.getElementById("securityProtocols").innerText);

    fetch("scans/save_scan.php", {
        method: "POST",
        body: data
    })
    .then(response => response.text())
    .then(result => {
        console.log("Risposta dal server:", result);
        alert(result);
    })
    .catch(error => console.error("Errore:", error));
}

// Assicuriamoci che lo script venga eseguito solo dopo il caricamento completo della pagina
document.addEventListener("DOMContentLoaded", function () {
    let saveButton = document.getElementById("saveScansButton");
    if (saveButton) {
        saveButton.addEventListener("click", saveScans);
    } else {
        console.error("Bottone SAVE SCANS non trovato!");
    }
});

</script>

        <footer class="mobile-footer">
            <div class="footer-links">
                <a href="#"
                    ><img
                        src="images/arrow1.png"
                        alt=""
                        class="footer-arrow"
                    />Github</a
                >
                <a href="#"
                    ><img src="images/arrow1.png" alt="" class="footer-arrow" />Contact
                    Us</a
                >
                <a href="#"
                    ><img src="images/arrow1.png" alt="" class="footer-arrow" />Our
                    Organization</a
                >
            </div>
        </footer>

        <script src="script_mobile.js"></script>
        <script type="module" src="javascript/fastScan.js"></script>
    </body>
</html>
