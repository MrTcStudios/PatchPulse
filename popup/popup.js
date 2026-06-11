/* PatchPulse — popup.js
   Gestisce la lista dei domini ufficiali: mostra, aggiunge, rimuove.
   registrableDomain() arriva da lib/match.js; t()/applyI18n() da lib/i18n.js. */

// Lingua: italiano se il browser è in italiano, altrimenti inglese.
document.documentElement.lang = PP_LANG;
applyI18n();

const listEl = document.getElementById("domain-list");
const countEl = document.getElementById("count");
const inputEl = document.getElementById("domain-input");
const msgEl = document.getElementById("msg");

function showMsg(text, isError = false) {
  msgEl.textContent = text;
  msgEl.className = "msg " + (isError ? "error" : "ok");
  setTimeout(() => { msgEl.textContent = ""; msgEl.className = "msg"; }, 2500);
}

/* Pulisce un input qualsiasi -> dominio registrabile.
   "https://www.Esempio.com/login" -> "esempio.com" */
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

async function getDomains() {
  const { officialDomains = [] } = await browser.storage.local.get("officialDomains");
  return officialDomains;
}

async function saveDomains(domains) {
  await browser.storage.local.set({ officialDomains: domains });
}

function render(domains) {
  listEl.replaceChildren();
  countEl.textContent = domains.length;
  for (const d of [...domains].sort()) {
    const li = document.createElement("li");
    const span = document.createElement("span");
    span.textContent = d;
    const btn = document.createElement("button");
    btn.className = "remove";
    btn.title = t("remove");
    btn.textContent = "×";
    btn.addEventListener("click", () => removeDomain(d));
    li.append(span, btn);
    listEl.appendChild(li);
  }
}

async function addDomain(raw) {
  const domain = cleanDomain(raw);
  if (!domain) { showMsg(t("msgInvalid"), true); return; }
  const domains = await getDomains();
  if (domains.includes(domain)) { showMsg(t("msgAlready", domain)); return; }
  domains.push(domain);
  await saveDomains(domains);
  render(domains);
  showMsg(t("msgAdded", domain));
}

async function removeDomain(domain) {
  const domains = (await getDomains()).filter(d => d !== domain);
  await saveDomains(domains);
  render(domains);
}

/* Mostra quante minacce sono state bloccate finora. */
async function showBlocked() {
  const { blockedCount = 0 } = await browser.storage.local.get("blockedCount");
  const el = document.getElementById("blocked");
  if (el && blockedCount > 0) el.textContent = blockedCount + " " + t("blockedThreats");
}

/* Ultime minacce bloccate (max 10, salvate in locale dal background). */
async function showRecent() {
  const { recentBlocked = [] } = await browser.storage.local.get("recentBlocked");
  if (!recentBlocked.length) return;
  const section = document.getElementById("recent-section");
  const list = document.getElementById("recent-list");
  section.hidden = false;
  list.replaceChildren();
  const lang = PP_LANG === "it" ? "it-IT" : "en-US";
  for (const e of recentBlocked) {
    const li = document.createElement("li");
    const dom = document.createElement("span");
    dom.className = "rb-domain";
    dom.textContent = e.d;
    const meta = document.createElement("span");
    meta.className = "rb-meta";
    const when = new Date(e.t).toLocaleDateString(lang, { day: "numeric", month: "short" })
      + " " + new Date(e.t).toLocaleTimeString(lang, { hour: "2-digit", minute: "2-digit" });
    meta.textContent = t("rs_" + e.r) + " · " + when;
    li.append(dom, meta);
    list.appendChild(li);
  }
}

document.getElementById("add-btn").addEventListener("click", () => {
  addDomain(inputEl.value);
  inputEl.value = "";
});

inputEl.addEventListener("keydown", (e) => {
  if (e.key === "Enter") { addDomain(inputEl.value); inputEl.value = ""; }
});

document.getElementById("add-current").addEventListener("click", async () => {
  const [tab] = await browser.tabs.query({ active: true, currentWindow: true });
  if (!tab || !tab.url) { showMsg(t("msgNoTab"), true); return; }
  try {
    addDomain(new URL(tab.url).hostname);
  } catch (e) { showMsg(t("msgBadPage"), true); }
});

/* Avvio */
getDomains().then(render);
showBlocked();
showRecent();
document.getElementById("ver").textContent = "v" + browser.runtime.getManifest().version;
