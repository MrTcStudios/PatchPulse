/* PatchPulse — options.js
   Impostazioni: pausa protezione, siti fidati (allow-list personale, popolata
   dal pulsante "Mi fido" sulla pagina di avviso), cronologia minacce e
   ripristino della lista predefinita.
   Tutto in storage.local: nessun dato lascia il browser. */

document.documentElement.lang = PP_LANG;
document.title = "PatchPulse";
applyI18n();
document.getElementById("ver").textContent = "v" + browser.runtime.getManifest().version;

const pauseEl = document.getElementById("pause-toggle");
const pausedWarnEl = document.getElementById("paused-warn");
const historyEl = document.getElementById("history-toggle");
const greenListEl = document.getElementById("green-list");
const greenEmptyEl = document.getElementById("green-empty");
const msgEl = document.getElementById("msg");
const resetBtn = document.getElementById("reset-defaults");
const resetMsgEl = document.getElementById("reset-msg");

let msgTimer = null;
function flash(el, text) {
  el.textContent = text;
  clearTimeout(msgTimer);   // il timer del messaggio precedente non deve cancellare questo
  msgTimer = setTimeout(() => { el.textContent = ""; }, 2500);
}

function renderGreen(list) {
  greenListEl.replaceChildren();
  greenEmptyEl.hidden = list.length > 0;
  for (const d of [...list].sort()) {
    const li = document.createElement("li");
    const span = document.createElement("span");
    span.textContent = d;
    const btn = document.createElement("button");
    btn.className = "remove";
    btn.title = t("remove");
    btn.textContent = "×";
    btn.addEventListener("click", () => removeGreen(d));
    li.append(span, btn);
    greenListEl.appendChild(li);
  }
}

async function removeGreen(domain) {
  const { userGreenList = [] } = await browser.storage.local.get("userGreenList");
  const next = userGreenList.filter((d) => d !== domain);
  await browser.storage.local.set({ userGreenList: next });
  renderGreen(next);
}

pauseEl.addEventListener("change", async () => {
  await browser.storage.local.set({ paused: pauseEl.checked });
  pausedWarnEl.hidden = !pauseEl.checked;
});

historyEl.addEventListener("change", async () => {
  await browser.storage.local.set({ recordHistory: historyEl.checked });
});

document.getElementById("clear-history").addEventListener("click", async () => {
  await browser.storage.local.set({ blockedCount: 0, recentBlocked: [] });
  flash(msgEl, t("optHistoryCleared"));
});

/* Ripristino in due passi: il primo click "arma" il pulsante, il secondo
   (entro 5 secondi) esegue davvero. Niente dialoghi bloccanti. */
let resetArmed = null;
function disarmReset() {
  clearTimeout(resetArmed);
  resetArmed = null;
  resetBtn.classList.remove("danger");
  resetBtn.textContent = t("optResetBtn");
}
resetBtn.addEventListener("click", async () => {
  if (!resetArmed) {
    resetBtn.classList.add("danger");
    resetBtn.textContent = t("optResetConfirm");
    resetArmed = setTimeout(disarmReset, 5000);
    return;
  }
  disarmReset();
  await browser.storage.local.set({
    officialDomains: [...DEFAULT_DOMAINS],
    removedDefaults: []
  });
  flash(resetMsgEl, t("optResetDone", String(DEFAULT_DOMAINS.length)));
});

/* Stato iniziale dallo storage. */
(async () => {
  const { paused = false, userGreenList = [], recordHistory = true } =
    await browser.storage.local.get(["paused", "userGreenList", "recordHistory"]);
  pauseEl.checked = Boolean(paused);
  pausedWarnEl.hidden = !paused;
  historyEl.checked = recordHistory !== false;
  renderGreen(userGreenList);
})();
