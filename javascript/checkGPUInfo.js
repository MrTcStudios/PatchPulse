// Funzione per ottenere informazioni sulla GPU
export function checkGPUInfo() {

    // Utilizza WebGL per ottenere informazioni sulla GPU
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        const rendererInfo = gl.getExtension('WEBGL_debug_renderer_info');

        if (rendererInfo) {
            const vendor = gl.getParameter(rendererInfo.UNMASKED_VENDOR_WEBGL);
            const renderer = gl.getParameter(rendererInfo.UNMASKED_RENDERER_WEBGL);

            document.getElementById("gpuName").innerText = `${vendor} - ${renderer}`;
        } else {
            document.getElementById("gpuName").innerText = "N/A";
        }
    } catch (error) {
        console.error('Errore durante la rilevazione della GPU:', error);
        document.getElementById("gpuName").innerText = "N/A";
    }
}