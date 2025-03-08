import { getPublicIpAddresses } from '../javascript/getPublicIpAddresses.js';
import { checkWebRTC } from '../javascript/checkWebRTC.js';
import { checkJavaScriptStatus } from '../javascript/checkJavaScriptStatus.js';
import { checkBrowserFingerprinting } from '../javascript/checkBrowserFingerprinting.js';
import { fingerprintUser } from '../javascript/checkBrowserFingerprinting.js';
import { checkWebGLFingerprinting } from '../javascript/checkWebGLFingerprinting.js';
import { checkHttpsOnly } from '../javascript/checkHttpsOnly.js';
import { updateDimensions } from './windowSize.js';
import { checkBrowserType } from '../javascript/checkBrowserType.js';
import { detectBrowser } from '../javascript/checkBrowserType.js';
import { checkBrowserLanguage } from '../javascript/checkBrowserLanguage.js';
import { checkCookiesEnabled } from '../javascript/checkCookiesEnabled.js';
import { checkGeolocation } from './geolocation.js';
import { checkBrowserVersion } from '../javascript/checkBrowserVersion.js';
import { detectBrowserVersion } from '../javascript/checkBrowserVersion.js';
import { checkPopupsEnabled } from '../javascript/checkPopupsEnabled.js';

import { checkOSVersion } from '../javascript/checkOSVersion.js';
import { checkAdBlockEnabled } from '../javascript/checkAdBlockEnabled.js';
import { updateIncognitoStatus } from './detectIncognito.js';
import { checkDoNotTrack } from '../javascript/checkDoNotTrack.js';
import { checkBlockedResources } from '../javascript/checkBlockedResources.js';
import { checkDeveloperMode } from '../javascript/checkDeveloperMode.js';
import { getBatteryStatus } from './batteryStatus.js';
/*import { checkConnectionType } from '../javascript/checkConnectionType.js';*/
import { checkDeviceMemory } from '../javascript/checkDeviceMemory.js';
import { checkGPUInfo } from '../javascript/checkGPUInfo.js';
import { checkWebWorkersSupport } from '../javascript/checkWebWorkersSupport.js';
import { checkMediaQueriesSupport } from '../javascript/checkMediaQueriesSupport.js';
import { getLocation } from '../javascript/getLocation.js';
import { checkSensorSupport } from '../javascript/checkSensorSupport.js';
import { getCPUThreadsAndCores } from '../javascript/getCPUThreadsAndCores.js';
import { getSecurityProtocols } from '../javascript/getSecurityProtocols.js';
import { getMimeTypes } from '../javascript/getMimeTypes.js';
import { checkWebAssemblySupport } from '../javascript/checkWebAssemblySupport.js';
import { getColorDepth } from '../javascript/getColorDepth.js';
import { getPixelDepth } from '../javascript/getPixelDepth.js';
import { checkTouchSupport } from '../javascript/checkTouchSupport.js';
import { getReferrerPolicy } from '../javascript/getReferrerPolicy.js';
import { checkWebNotificationsSupport } from '../javascript/checkWebNotificationsSupport.js';
import { checkPaymentRequestAPISupport } from '../javascript/checkPaymentRequestAPISupport.js';
import { checkPermissionsAPISupport } from '../javascript/checkPermissionsAPISupport.js';
import { checkHtmlCssSupport } from '../javascript/checkHtmlCssSupport.js';




// Chiamata delle funzioni
getPublicIpAddresses();
checkWebRTC();
checkJavaScriptStatus();
checkBrowserFingerprinting();
fingerprintUser();
checkWebGLFingerprinting(); 
checkHttpsOnly();
updateDimensions();
checkBrowserType();
detectBrowser();
checkBrowserLanguage();
checkCookiesEnabled();
checkGeolocation();
checkBrowserVersion();
detectBrowserVersion();
checkPopupsEnabled();

checkOSVersion();
checkAdBlockEnabled();
updateIncognitoStatus();
checkDoNotTrack();
checkBlockedResources();
checkDeveloperMode();
getBatteryStatus();
/*checkConnectionType();*/ //Not working
checkDeviceMemory(); //Non sempre giusta
checkGPUInfo();

checkWebWorkersSupport();
checkMediaQueriesSupport();
getLocation();
checkSensorSupport();
getCPUThreadsAndCores();
getSecurityProtocols();
getMimeTypes();
checkWebAssemblySupport();
getColorDepth();
getPixelDepth();
checkTouchSupport();
getReferrerPolicy();
checkWebNotificationsSupport();
checkPaymentRequestAPISupport();
checkPermissionsAPISupport();
checkHtmlCssSupport();

