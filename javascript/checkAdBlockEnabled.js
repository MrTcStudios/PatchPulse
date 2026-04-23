export function checkAdBlockEnabled() {
    return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        script.onerror = () => scriptFailed = true;
        
        let scriptFailed = false;
        document.head.appendChild(script);
        
        const img = new Image();
        img.src = 'https://www.googleadservices.com/pagead/imgad?id=123';
        img.onerror = () => imgFailed = true;
        
        let imgFailed = false;
        
        const bait1 = document.createElement('div');
        bait1.className = 'ad_unit ad-unit text-ad pub_300x250 pub_300x250m';
        bait1.style.cssText = 'width:1px; height:1px; position:absolute; left:-9999px;';
        document.body.appendChild(bait1);
        
        const bait2 = document.createElement('div');
        bait2.id = 'ad-slot';
        bait2.innerHTML = '&nbsp;';
        bait2.style.cssText = 'width:1px; height:1px; position:absolute; left:-9999px;';
        document.body.appendChild(bait2);
        
        const hasAdBlocker = () => {
            return (
                typeof window.google_ad_status === 'undefined' ||
                window.canRunAds === false ||
                window.canRunAds === undefined
            );
        };
        
        setTimeout(() => {
            const checkBait = (element) => {
                return !document.body.contains(element) ||
                    element.offsetHeight === 0 ||
                    element.offsetWidth === 0 ||
                    window.getComputedStyle(element).display === 'none' ||
                    window.getComputedStyle(element).visibility === 'hidden';
            };
            
            const adBlockDetected = hasAdBlocker() || 
                                    scriptFailed || 
                                    imgFailed ||
                                    checkBait(bait1) ||
                                    checkBait(bait2);
            
            if (document.body.contains(bait1)) document.body.removeChild(bait1);
            if (document.body.contains(bait2)) document.body.removeChild(bait2);
            if (document.head.contains(script)) document.head.removeChild(script);
            
            const adBlockEnabledElement = document.getElementById('adBlockEnabled');
            if (adBlockEnabledElement) {
                adBlockEnabledElement.innerText = (adBlockDetected ? 'Sì' : 'No');
                adBlockEnabledElement.dataset.detected = adBlockDetected.toString();
            } else {
                console.warn('Elemento con id "adBlockEnabled" non trovato.');
            }
            
            console.debug('AdBlock detection results:', {
                nativeDetection: hasAdBlocker(),
                scriptFailed,
                imgFailed,
                bait1Blocked: checkBait(bait1),
                bait2Blocked: checkBait(bait2),
                finalResult: adBlockDetected
            });
            
            resolve(adBlockDetected);
        }, 300);
    });
}

export function detectAdBlock() {
    return checkAdBlockEnabled()
        .then(isEnabled => {
            if (!isEnabled) {
                return new Promise(resolve => {
                    const testAd = document.createElement('div');
                    testAd.innerHTML = '&nbsp;';
                    testAd.className = 'adsbygoogle';
                    document.body.appendChild(testAd);
                    
                    setTimeout(() => {
                        const isBlocked = !document.body.contains(testAd) || 
                                        window.getComputedStyle(testAd).display === 'none';
                        if (document.body.contains(testAd)) document.body.removeChild(testAd);
                        
                        const adBlockEnabledElement = document.getElementById('adBlockEnabled');
                        if (adBlockEnabledElement && isBlocked) {
                            adBlockEnabledElement.innerText = 'Sì';
                            adBlockEnabledElement.dataset.detected = 'true';
                            adBlockEnabledElement.dataset.method = 'fallback';
                        }
                        
                        resolve(isBlocked);
                    }, 100);
                });
            }
            return isEnabled;
        });
}
