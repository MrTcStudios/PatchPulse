<?php
include("config.php");
?>

<!DOCTYPE html>
<html lang="it">
    
<head>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx.css" />
        <title>PatchPulse</title>
        <meta charset="UTF-8" />
</head>

<body>





<section class="layout">
                    <div class="header">
                        <img
                            class="headerIcon"
                            src="images/PatchPulseLogo.svg"
                            alt="CentredLogo"
                        />
                        <span><a href="https://mrtc.cc" class="headerButton">Home</a></span>
			            <!-- Inizio V3.8-->
                        <span><div class="dropdown">
                            <a href="#" class="headerButton">Tool</a>
                            <div class="dropdown-content">
                                <a href="#">Fast Scan</a>
                                <a href="#">Web Scan</a>
                                <a href="#">Port Scan</a>
                                <a href="#">Custom Scan</a>
                            </div>
                        </div></span>
                        <!-- Fine V3.8-->
                        <span><a href="mailto:support@mrtc.cc" class="headerButton">Contact Us</a></span>

                <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Se l'utente è loggato, mostra "ACCOUNT" -->
                        <button class="inout-button" onclick="window.location.href='accountPage.php'">Account</button>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <button class="inout-button" onclick="window.location.href='loginPage.php'">Log In / Sign Up</button>
                    <?php endif; ?>
                    </div>
                    <div class="body">
                        <div class="choiceScansZone">
                            <button class="scans-button-left" data-scan="webTracking">Web Tracking</button>
                            <button class="center-button-right" data-scan="functionality">Functionality Support</button>
                            <button class="scans-button-right" data-scan="deviceInfo">Device Information</button>
                        </div>
                    </div>

                <div id="webTracking" class="scanResultZone active">
                    <div></div>
		    <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="saveScansButton" class="modeButton">SAVE ALL SCANS</button>
                    <?php else: ?>
                         <button class="modeButton">PRO MODE</button>
                    <?php endif; ?>
               <!--     <button class="modeButton">PRO MODE</button>  -->
                    <a href=""><strong>Cookies Tracking:</strong><br/> <span id="cookiesEnabled">Loading...</span></a>
                    <a href=""><strong>Do Not Track:</strong><br/> <span id="doNotTrack">Loading...</span></a>
                    <a href=""><strong>Browser Fingerprinting:</strong><br/> <span id="browserFingerprinting">Loading...</span></a>
                    <a href=""><strong>WebRTC Support:</strong><br/> <span id="webrtcSupport">Loading...</span></a>
                    <a href=""><strong>HTTPS Only:</strong><br/> <span id="httpsOnly">Loading...</span></a>
                    <a href=""><strong>Blocked Resources:</strong><br/> <span id="blockedResources">Loading...</span></a>
                    <a href=""><strong>AdBlocker:</strong><br/> <span id="adBlockEnabled">Loading...</span></a>
                </div>

                <div id="functionality" class="scanResultZone">
                    <div></div>
		     <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="saveScansButton" class="modeButton">SAVE ALL SCANS</button>
                    <?php else: ?>
                         <button class="modeButton">PRO MODE</button>
                    <?php endif; ?>
                    <a href=""><strong>JavaScript:</strong><br/> <span id="javascriptStatus">Loading...</span></a>
                    <a href=""><strong>WebGL:</strong><br/> <span id="webglFingerprinting">Loading...</span></a>
                    <a href=""><strong>Developer Mode:</strong><br/> <span id="developerMode">Loading...</span></a>
                    <a href=""><strong>WebAssembly:</strong><br/> <span id="webAssemblySupport">Loading...</span></a>
                    <a href=""><strong>Web Workers:</strong><br/> <span id="webWorkersSupported">Loading...</span></a>
                    <a href=""><strong>Media Queries:</strong><br/> <span id="mediaQueriesSupported">Loading...</span></a>
                    <a href=""><strong>Web Notifications:</strong><br/> <span id="webNotificationsSupported">Loading...</span></a>
                    <a href=""><strong>Permissions API:</strong><br/> <span id="permissionsAPISupported">Loading...</span></a>
                    <a href=""><strong>Payment Request API:</strong><br/> <span id="paymentRequestAPISupported">Loading...</span></a>
                    <a href=""><strong>HTML5/CSS3:</strong><br/> <span id="htmlCssSupport">Loading...</span></a>
                    <a href=""><strong>Geolocation:</strong><br/> <span id="geolocationInfo">Loading...</span></a>
                    <a href=""><strong>Sensors Support:</strong><br/> <span id="sensorsSupported">Loading...</span></a>
                    <a href=""><strong>Pop-ups:</strong><br/> <span id="popupsEnabled">Loading...</span></a>
                </div>

                <div id="deviceInfo" class="scanResultZone">
                    <div></div>
		    <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="saveScansButton" class="modeButton">SAVE ALL SCANS</button>
                    <?php else: ?>
                         <button class="modeButton">PRO MODE</button>
                    <?php endif; ?>
                    <a href=""><strong>Public IPv4:</strong><br/> <span id="publicIpv4">Loading...</span></a>
                    <a href=""><strong>Public IPv6:</strong><br/> <span id="publicIpv6">Loading...</span></a>
                    <a href=""><strong>Browser Type:</strong><br/> <span id="browserType">Loading...</span></a>
                    <a href=""><strong>Browser Version:</strong><br/> <span id="browserVersion">Loading...</span></a>
                    <a href=""><strong>Browser Language:</strong><br/> <span id="browserLanguage">Loading...</span></a>
                    <a href=""><strong>Operating System:</strong><br/> <span id="osVersion">Loading...</span></a>
                    <a href=""><strong>Incognito Mode:</strong><br/> <span id="incognitoMode">Loading...</span></a>
                    <a href=""><strong>Device Memory:</strong><br/> <span id="deviceMemory">Loading...</span></a>
                    <a href=""><strong>CPU Threads:</strong><br/> <span id="cpuThreads">Loading...</span></a>
                    <a href=""><strong>CPU Cores:</strong><br/> <span id="cpuCores">Loading...</span></a>
                    <a href=""><strong>GPU Info:</strong><br/> <span id="gpuName">Loading...</span></a>
                    <a href=""><strong>Color Depth:</strong><br/> <span id="colorDepth">Loading...</span></a>
                    <a href=""><strong>Pixel Depth:</strong><br/> <span id="pixelDepth">Loading...</span></a>
                    <a href=""><strong>Touch Support:</strong><br/> <span id="touchSupport">Loading...</span></a>
                    <a href=""><strong>Screen Resolution:</strong><br/> <span id="width">Loading...</span> x <span id="height">Loading...</span></a>
                    <a href=""><strong>MIME Types:</strong><br/> <span id="mimeTypes">Loading...</span></a>
                    <a href=""><strong>Referrer Policy:</strong><br/> <span id="referrerPolicy">Loading...</span></a>
                    <a href=""><strong>Battery Status:</strong><br/> <span id="batteryStatus">Loading...</span></a>
                    <a href=""><strong>Security Protocols:</strong><br/> <span id="securityProtocols">Loading...</span></a>
                </div>

              <!--  <iframe id="mapFrame" width="50%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="display: none;" src=""></iframe>     -->
                </section>

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

document.addEventListener("DOMContentLoaded", function () {
    let saveButton = document.getElementById("saveScansButton");
    if (saveButton) {
        saveButton.addEventListener("click", saveScans);
    } else {
        console.error("Bottone SAVE SCANS non trovato!");
    }
});


        </script>
        <hr class="sectionLine"/>
        <div class="fot1">
            <ul>
                <li>
                    <img
                        class="arrowsFot"
                        src="images/arrow1.png"
                        alt=""
                    />
                    <a href="" class="faqButton" data-faq="faq1"""
                        >Github</a
                    >
                </li>
                <li>
                    <img
                        class="arrowsFot"
                        src="images/arrow1.png"
                        alt=""
                    />
                    <a href="" class="faqButton" data-faq="faq1"""
                        >Contact Us</a
                    >
                </li>
                <li>
                    <img
                        class="arrowsFot"
                        src="images/arrow1.png"
                        alt=""
                    />
                    <a href="" class="faqButton" data-faq="faq1"""
                        >Our Organization</a
                    >
                </li>
            </ul>
        </div>
        <hr class="sectionLine"/>
        <script src="script.js"></script>
        <script type="module" src="javascript/fastScan.js"></script>
    </body>
</html>
