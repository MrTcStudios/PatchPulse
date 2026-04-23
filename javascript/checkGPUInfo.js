export function checkGPUInfo() {
    try {
        var canvas = document.createElement('canvas');
        var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');

        if (!gl) {
            document.getElementById("gpuName").innerText = "WebGL non disponibile";
            return;
        }

        var rendererInfo = gl.getExtension('WEBGL_debug_renderer_info');

        if (rendererInfo) {
            var vendor = gl.getParameter(rendererInfo.UNMASKED_VENDOR_WEBGL);
            var renderer = gl.getParameter(rendererInfo.UNMASKED_RENDERER_WEBGL);
            document.getElementById("gpuName").innerText = vendor + ' - ' + renderer;
        } else {
            document.getElementById("gpuName").innerText = "Info GPU non disponibile";
        }
    } catch (error) {
        document.getElementById("gpuName").innerText = "N/A";
    }
}
