/* PatchPulse — onboarding.js
   Pagina di benvenuto mostrata UNA volta, alla prima installazione.
   Lascia aggiungere subito i siti personali (stessa protezione dei default). */

document.documentElement.lang = PP_LANG;
document.title = "PatchPulse";
applyI18n();

const msgEl = document.getElementById("msg");
const inputEl = document.getElementById("domain-input");
const addedEl = document.getElementById("added-list");

document.getElementById("version").textContent =
  "PatchPulse v" + browser.runtime.getManifest().version;

/* Numero di siti gia' protetti (dallo storage; al primissimo avvio, se il
   background non ha ancora scritto, usa la lista predefinita). */
(async () => {
  const { officialDomains = [] } = await browser.storage.local.get("officialDomains");
  const n = officialDomains.length || DEFAULT_DOMAINS.length;
  document.getElementById("intro").textContent = t("ob_intro", String(n));
})();

function showMsg(text, isError = false) {
  msgEl.textContent = text;
  msgEl.className = "msg " + (isError ? "error" : "ok");
  setTimeout(() => { msgEl.textContent = ""; msgEl.className = "msg"; }, 2500);
}

/* Stessa pulizia dell'input usata dal popup: qualsiasi cosa -> dominio registrabile. */
function cleanDomain(value) {
  let v = value.trim().toLowerCase();
  if (!v) return "";
  try {
    if (v.includes("://")) v = new URL(v).hostname;
  } catch (e) { /* ignora */ }
  v = v.split("/")[0].replace(/^www\./, "");
  if (!/^[a-z0-9.-]+\.[a-z]{2,}$/.test(v)) return "";
  return registrableDomain(v);
}

async function addDomain(raw) {
  const domain = cleanDomain(raw);
  if (!domain) { showMsg(t("msgInvalid"), true); return; }
  const { officialDomains = [] } = await browser.storage.local.get("officialDomains");
  if (officialDomains.includes(domain)) { showMsg(t("msgAlready", domain)); return; }
  officialDomains.push(domain);
  await browser.storage.local.set({ officialDomains });
  showMsg(t("msgAdded", domain));
  const li = document.createElement("li");
  li.textContent = "✓ " + domain;
  addedEl.appendChild(li);
}

document.getElementById("add-btn").addEventListener("click", () => {
  addDomain(inputEl.value);
  inputEl.value = "";
  inputEl.focus();
});

inputEl.addEventListener("keydown", (e) => {
  if (e.key === "Enter") { addDomain(inputEl.value); inputEl.value = ""; }
});

/* "Fatto": chiude la scheda di benvenuto. */
document.getElementById("done").addEventListener("click", async () => {
  try {
    const tab = await browser.tabs.getCurrent();
    if (tab) await browser.tabs.remove(tab.id);
  } catch (e) {
    window.close();
  }
});
