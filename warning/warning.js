/* PatchPulse — warning.js
   Mostra il dominio sospetto e quello ufficiale (evidenziando le lettere
   ingannevoli), spiega il motivo e gestisce i tre pulsanti + la segnalazione
   dei falsi allarmi. */

// Lingua: italiano se il browser è in italiano, altrimenti inglese.
document.documentElement.lang = PP_LANG;
document.title = t("warnDocTitle");
applyI18n();

const params = new URLSearchParams(location.search);
const suspicious = params.get("suspicious") || "";
const official = params.get("official") || "";
const reason = params.get("reason") || "";
const target = params.get("target") || "";
const isDemo = params.get("demo") === "1";

/* Per gli IDN il browser ci consegna il punycode (xn--...): all'utente
   mostriamo la forma com'appare nella barra (idnToUnicode da match.js) e
   il punycode finisce nella riga "Indirizzo reale". Storage e messaggi al
   background restano SEMPRE sul punycode. */
const suspiciousShown =
  (typeof idnToUnicode === "function") ? idnToUnicode(suspicious) : suspicious;

/* Evidenzia la parte del dominio sospetto che differisce da quello ufficiale:
   prefisso e suffisso comuni restano normali, il "trucco" viene marcato.
   Costruito con textContent (mai innerHTML con dati esterni). */
function renderDiff(el, bad, good) {
  el.textContent = "";
  let p = 0;
  while (p < bad.length && p < good.length && bad[p] === good[p]) p++;
  let s = 0;
  while (s < bad.length - p && s < good.length - p &&
         bad[bad.length - 1 - s] === good[good.length - 1 - s]) s++;
  const mid = bad.slice(p, bad.length - s);
  if (!mid) { el.textContent = bad; return; }
  const pre = document.createElement("span");
  pre.textContent = bad.slice(0, p);
  const diff = document.createElement("span");
  diff.className = "diff";
  diff.textContent = mid;
  const post = document.createElement("span");
  post.textContent = bad.slice(bad.length - s);
  el.append(pre, diff, post);
}

// Se c'è un sito ufficiale di riferimento, evidenzia le lettere ingannevoli e
// mostra il confronto. Per gli "script misti" non c'è un ufficiale preciso:
// mostriamo solo il dominio visitato e nascondiamo la riga "Assomiglia a".
if (official) {
  renderDiff(document.getElementById("suspicious"), suspiciousShown, official);
  document.getElementById("official").textContent = official;
} else {
  document.getElementById("suspicious").textContent = suspiciousShown;
  document.getElementById("official-row").hidden = true;
}

// Se la forma mostrata differisce dal punycode, fai vedere anche quello:
// e' l'indirizzo che il browser usa davvero.
if (suspiciousShown !== suspicious) {
  document.getElementById("puny-row").hidden = false;
  document.getElementById("puny").textContent = suspicious;
}

// Spiega PERCHÉ il sito è stato segnalato.
const reasonKey = {
  typo: "reasonTypo",
  omografi: "reasonHomograph",
  sottodominio: "reasonSubdomain",
  combo: "reasonCombo",
  tld: "reasonTld",
  hosting: "reasonHosting",
  scriptmisti: "reasonMixed"
}[reason];
const reasonEl = document.getElementById("reason");
if (reasonEl && reasonKey) reasonEl.textContent = t(reasonKey);

// Versione dell'estensione (richiesta esplicita: visibile sull'avviso).
const version = browser.runtime.getManifest().version;
document.getElementById("version").textContent = "PatchPulse v" + version;

// Pulsante "Mi fido": mostriamo il dominio bloccato per chiarezza.
const trustBtn = document.getElementById("trust");
trustBtn.textContent = suspiciousShown ? t("btnTrustDomain", suspiciousShown) : t("btnTrust");

// Modalita' demo (dall'onboarding): solo dimostrativa, niente pulsanti
// che navigano o modificano le liste.
if (isDemo) {
  document.getElementById("demo-note").hidden = false;
  trustBtn.hidden = true;
  document.getElementById("proceed").hidden = true;
}

/* Link "segnala falso allarme": mailto: apre il client di posta DELL'UTENTE
   con destinatario/oggetto/corpo precompilati; l'invio resta una scelta sua.
   L'estensione non effettua alcuna richiesta di rete. */
const reportBody =
  t("labelVisiting") + ": " + suspicious + "\n" +
  t("labelLooksLike") + ": " + official + "\n" +
  "Reason: " + reason + "\n" +
  "PatchPulse: v" + version + "\n\n" +
  t("reportBodyNote") + "\n";
document.getElementById("report").href =
  "mailto:" + PP_SUPPORT_EMAIL +
  "?subject=" + encodeURIComponent(t("reportSubject", suspicious)) +
  "&body=" + encodeURIComponent(reportBody);

/* "Copia dettagli": per chi non ha un client di posta configurato (il link
   mailto non aprirebbe nulla). Solo appunti locali, nessuna rete. */
const copyBtn = document.getElementById("copy");
copyBtn.addEventListener("click", async () => {
  try {
    await navigator.clipboard.writeText(reportBody);
    copyBtn.textContent = t("copied");
  } catch (e) {
    // fallback per contesti senza Clipboard API
    const ta = document.createElement("textarea");
    ta.value = reportBody;
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand("copy"); copyBtn.textContent = t("copied"); } catch (e2) {}
    ta.remove();
  }
});

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

/* "Mi fido": mette il dominio nell'allow-list personale (non verra' piu'
   segnalato — NON diventa un sito protetto) e prosegue. Gestibile dalle
   impostazioni, sezione "Siti fidati". */
trustBtn.addEventListener("click", async () => {
  if (!isSafeHttpUrl(target)) return;
  try {
    await browser.runtime.sendMessage({ type: "allowForever", domain: suspicious });
  } catch (e) {
    console.error("[PatchPulse] aggiunta ai siti fidati non riuscita, navigo comunque:", e);
  }
  window.location.replace(target);
});

/* "Vai comunque": via libera temporaneo (15 minuti), poi prosegue. */
document.getElementById("proceed").addEventListener("click", async () => {
  if (!isSafeHttpUrl(target)) return;
  try {
    await browser.runtime.sendMessage({ type: "proceed", domain: suspicious });
  } catch (e) {
    console.error("[PatchPulse] messaggio non riuscito, navigo comunque:", e);
  }
  window.location.replace(target);
});

/* Focus sulla scelta sicura: premere Invio = "Torna indietro". */
document.getElementById("back").focus();
