/* PatchPulse — warning.js
   Mostra il dominio sospetto e quello ufficiale, e gestisce i tre pulsanti. */

// Lingua: italiano se il browser è in italiano, altrimenti inglese.
document.documentElement.lang = PP_LANG;
document.title = t("warnDocTitle");
applyI18n();

const params = new URLSearchParams(location.search);
const suspicious = params.get("suspicious") || "";
const official = params.get("official") || "";
const reason = params.get("reason") || "";
const target = params.get("target") || "";

document.getElementById("suspicious").textContent = suspicious;
document.getElementById("official").textContent = official;

// Spiega PERCHÉ il sito è stato segnalato.
const reasonKey = { typo: "reasonTypo", omografi: "reasonHomograph", sottodominio: "reasonSubdomain" }[reason];
const reasonEl = document.getElementById("reason");
if (reasonEl && reasonKey) reasonEl.textContent = t(reasonKey);

// Pulsante "Mi fido": mostriamo il dominio bloccato per chiarezza.
const trustBtn = document.getElementById("trust");
trustBtn.textContent = suspicious ? t("btnTrustDomain", suspicious) : t("btnTrust");

/* Accettiamo SOLO destinazioni http/https: blocca trucchi tipo
   target=javascript:... o target=data:... (sicurezza). */
function isSafeHttpUrl(value) {
  try {
    const u = new URL(value);
    return u.protocol === "http:" || u.protocol === "https:";
  } catch (e) {
    return false;
  }
}

/* "Torna indietro": torna alla pagina precedente; se non c'è cronologia,
   chiude la scheda. Avvolto in try/catch così non resta mai bloccato. */
document.getElementById("back").addEventListener("click", async () => {
  if (window.history.length > 1) {
    window.history.back();
    return;
  }
  try {
    const tab = await browser.tabs.getCurrent();
    if (tab) await browser.tabs.remove(tab.id);
  } catch (e) {
    console.error("[PatchPulse] impossibile chiudere la scheda:", e);
  }
});

/* "Mi fido": aggiunge il dominio ai protetti (protezione permanente) e prosegue. */
trustBtn.addEventListener("click", async () => {
  if (!isSafeHttpUrl(target)) return;
  try {
    await browser.runtime.sendMessage({ type: "addOfficial", domain: suspicious });
  } catch (e) {
    console.error("[PatchPulse] aggiunta ai protetti non riuscita, navigo comunque:", e);
  }
  window.location.replace(target);
});

/* "Vai comunque": via libera SOLO per questa sessione, poi prosegue. */
document.getElementById("proceed").addEventListener("click", async () => {
  if (!isSafeHttpUrl(target)) return;
  try {
    await browser.runtime.sendMessage({ type: "proceed", domain: suspicious });
  } catch (e) {
    console.error("[PatchPulse] messaggio non riuscito, navigo comunque:", e);
  }
  window.location.replace(target);
});
