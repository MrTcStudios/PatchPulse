/* ============================================================
   PatchPulse — i18n.js
   Lingua dell'interfaccia: ITALIANO se il browser è in italiano,
   INGLESE in tutti gli altri casi.
   Incluso nelle pagine popup e warning (prima dei rispettivi script).
   ============================================================ */

/* Email di supporto del progetto: usata dal link "segnala falso allarme"
   sulla pagina di avviso (mailto:, si apre il client di posta dell'utente —
   l'estensione non invia nulla da sola). */
const PP_SUPPORT_EMAIL = "support@patchpulse.org";

const I18N = {
  it: {
    warnDocTitle: "PatchPulse — Attenzione",
    badge: "PatchPulse · Avviso di sicurezza",
    warnTitle: "Attenzione: possibile sito truffa",
    warnLead: "Stai per entrare in un sito che assomiglia molto a un sito ufficiale. Potrebbe essere un tentativo di phishing per rubarti dati, password o denaro.",
    labelVisiting: "Stai visitando",
    labelLooksLike: "Assomiglia a",
    btnBack: "Torna indietro (consigliato)",
    btnTrust: "Conosco questo sito: aggiungilo ai protetti e vai",
    btnTrustDomain: "Mi fido di $D$: aggiungilo ai protetti e vai",
    btnProceed: "Vai comunque, solo questa volta",
    warnHint: "Se conosci e ti fidi di questo sito, puoi aggiungerlo ai tuoi domini ufficiali dall'icona di PatchPulse nella barra del browser.",
    tagline: "Protezione anti-phishing",
    addCurrent: "+ Proteggi il sito attuale",
    inputPlaceholder: "oppure: esempio.com",
    addBtn: "Aggiungi",
    listHead: "Domini protetti",
    statusActive: "Protezione attiva",
    remove: "Rimuovi",
    msgInvalid: "Dominio non valido",
    msgAlready: "$D$ è già protetto",
    msgAdded: "Aggiunto $D$",
    msgNoTab: "Nessuna scheda attiva",
    msgBadPage: "Pagina non valida",
    reasonTypo: "Differisce di pochi caratteri dal sito ufficiale.",
    reasonHomograph: "Usa caratteri che imitano quelli del sito ufficiale.",
    reasonSubdomain: "Usa il nome del sito ufficiale per ingannarti, ma il dominio reale è un altro.",
    reasonCombo: "Combina il nome del sito ufficiale con parole come login, verifica o assistenza.",
    reasonTld: "Usa il nome di un sito ufficiale su un'estensione di dominio sospetta.",
    blockedThreats: "minacce bloccate",
    reportLink: "Pensi sia un errore? Segnala il falso allarme",
    reportSubject: "[PatchPulse] Segnalazione falso allarme: $D$",
    reportBodyNote: "Spiegaci perché ritieni che questo sito sia legittimo (facoltativo ma utile):"
  },
  en: {
    warnDocTitle: "PatchPulse — Warning",
    badge: "PatchPulse · Security alert",
    warnTitle: "Warning: possible scam website",
    warnLead: "You are about to enter a site that closely resembles an official one. It may be a phishing attempt to steal your data, passwords or money.",
    labelVisiting: "You are visiting",
    labelLooksLike: "Looks like",
    btnBack: "Go back (recommended)",
    btnTrust: "I know this site: add it to protected and continue",
    btnTrustDomain: "I trust $D$: add it to protected and continue",
    btnProceed: "Continue anyway, just this once",
    warnHint: "If you know and trust this site, you can add it to your official domains from the PatchPulse icon in the browser toolbar.",
    tagline: "Anti-phishing protection",
    addCurrent: "+ Protect the current site",
    inputPlaceholder: "or: example.com",
    addBtn: "Add",
    listHead: "Protected domains",
    statusActive: "Protection active",
    remove: "Remove",
    msgInvalid: "Invalid domain",
    msgAlready: "$D$ is already protected",
    msgAdded: "Added $D$",
    msgNoTab: "No active tab",
    msgBadPage: "Invalid page",
    reasonTypo: "It differs by just a few characters from the official site.",
    reasonHomograph: "It uses characters that imitate the official site.",
    reasonSubdomain: "It uses the official site's name to trick you, but the real domain is different.",
    reasonCombo: "It combines the official site's name with words like login, verify or support.",
    reasonTld: "It uses an official site's name on a suspicious domain extension.",
    blockedThreats: "threats blocked",
    reportLink: "Think this is a mistake? Report a false alarm",
    reportSubject: "[PatchPulse] False alarm report: $D$",
    reportBodyNote: "Tell us why you believe this site is legitimate (optional but helpful):"
  }
};

/* Lingua dell'UI del browser (es. "it", "it-IT", "en-US"). */
const PP_LANG =
  (typeof browser !== "undefined" && browser.i18n &&
   browser.i18n.getUILanguage().toLowerCase().startsWith("it"))
    ? "it" : "en";

/* Restituisce la stringa tradotta; $D$ viene sostituito con `domain`. */
function t(key, domain) {
  const dict = I18N[PP_LANG] || I18N.en;
  let s = (key in dict) ? dict[key] : (I18N.en[key] || key);
  if (domain != null) s = s.replace("$D$", domain);
  return s;
}

/* Applica le traduzioni agli elementi con attributi data-i18n* . */
function applyI18n(root) {
  root = root || document;
  root.querySelectorAll("[data-i18n]").forEach((el) => {
    el.textContent = t(el.dataset.i18n);
  });
  root.querySelectorAll("[data-i18n-placeholder]").forEach((el) => {
    el.placeholder = t(el.dataset.i18nPlaceholder);
  });
}
