// Funzione per ottenere il numero di threads e calcolare i core
export function getCPUThreadsAndCores() {
    const cpuThreads = navigator.hardwareConcurrency ? navigator.hardwareConcurrency : "N/D";
    const cpuCores = cpuThreads !== "N/D" ? Math.ceil(cpuThreads / 2) : "N/D";
    const cpuThreadsElement = document.getElementById('cpuThreads');
    const cpuCoresElement = document.getElementById('cpuCores');

    if (cpuThreadsElement) {
        cpuThreadsElement.innerText = cpuThreads + " threads";
    } else {
        console.error('Elemento con id "cpuThreads" non trovato.');
    }

    if (cpuCoresElement) {
        cpuCoresElement.innerText = cpuCores + " core";
    } else {
        console.error('Elemento con id "cpuCores" non trovato.');
    }
}