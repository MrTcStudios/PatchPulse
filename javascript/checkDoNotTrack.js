export function checkDoNotTrack() {
    var el = document.getElementById('doNotTrack');
    if (!el) return;

    var dnt = navigator.doNotTrack === '1' || window.doNotTrack === '1';
    var gpc = navigator.globalPrivacyControl === true;

    if (gpc && dnt) {
        el.innerText = 'Attivato (Do Not Track + Global Privacy Control)';
    } else if (gpc) {
        el.innerText = 'Attivato (Global Privacy Control)';
    } else if (dnt) {
        el.innerText = 'Attivato (Do Not Track — deprecato, molti siti lo ignorano)';
    } else {
        el.innerText = 'Disattivato';
    }
}
