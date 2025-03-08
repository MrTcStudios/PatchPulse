CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30),
    email VARCHAR(40),
    password VARCHAR(255),
    agree_terms TINYINT,
    is_confirmed TINYINT,
    confirmation_token VARCHAR(64),
    temp_mail VARCHAR(255),
    deletion_token VARCHAR(64),
    deletion_token_expires DATETIME
);

CREATE TABLE scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cookiesEnabled VARCHAR(255),
    doNotTrack VARCHAR(255),
    browserFingerprinting VARCHAR(255),
    webrtcSupport VARCHAR(255),
    httpsOnly VARCHAR(255),
    blockedResources VARCHAR(255),
    adBlockEnabled VARCHAR(255),
    javascriptStatus VARCHAR(255), 
    webglFingerprinting VARCHAR(255),
    developerMode VARCHAR(255),
    webAssemblySupport VARCHAR(255),
    webWorkersSupported VARCHAR(255),
    mediaQueriesSupported VARCHAR(255),
    webNotificationsSupported VARCHAR(255),
    permissionsAPISupported VARCHAR(255),
    paymentRequestAPISupported VARCHAR(255),
    htmlCssSupport VARCHAR(255),
    geolocationInfo VARCHAR(255),
    sensorsSupported VARCHAR(255),
    popupsEnabled VARCHAR(255),
    publicIpv4 VARCHAR(255),
    publicIpv6 VARCHAR(255),
    browserType VARCHAR(255),
    browserVersion VARCHAR(255),
    browserLanguage VARCHAR(255),
    osVersion VARCHAR(255),
    incognitoMode VARCHAR(255),
    deviceMemory VARCHAR(255),
    cpuThreads VARCHAR(255),
    cpuCores VARCHAR(255),
    gpuName VARCHAR(255),
    colorDepth VARCHAR(255),
    pixelDepth VARCHAR(255),
    touchSupport VARCHAR(255),
    screenResolution VARCHAR(255),
    mimeTypes VARCHAR(255),
    referrerPolicy VARCHAR(255),
    batteryStatus VARCHAR(255),
    securityProtocols VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE scan_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scan_id INT NOT NULL,
    parameter VARCHAR(255) NOT NULL,
    value VARCHAR(255),
    risk_level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scan_id) REFERENCES scans(id) ON DELETE CASCADE
);

