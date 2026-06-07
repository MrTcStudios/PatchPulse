/* ============================================================
   PatchPulse — background.js
   Il "cervello" dell'estensione. Ascolta ogni navigazione, confronta il
   dominio con la lista di domini ufficiali e — se trova un sosia — reindirizza
   a una pagina di avviso PRIMA che il sito sospetto venga caricato.

   Funzioni e liste (normalize, levenshtein, registrableDomain, toAsciiSkeleton,
   DEFAULT_DOMAINS, GREEN_LIST...) arrivano da lib/match.js.
   ============================================================ */

const MAX_DOMAINS = 5000; // tetto di sicurezza alla lista personalizzabile

let officialDomains = [];
let officialIndex = [];   // versione pre-calcolata: [{ domain, norm, labels }]

/* Pre-calcola una volta sola la forma normalizzata e le etichette dei domini
   ufficiali, così non rifacciamo il lavoro a ogni navigazione. */
function rebuildIndex() {
  officialIndex = officialDomains.map((d) => ({
    domain: d,
    norm: normalize(d),
    labels: d.split(".")
  }));
}

async function loadDomains() {
  const stored = await browser.storage.local.get("officialDomains");
  if (stored.officialDomains && stored.officialDomains.length) {
    officialDomains = stored.officialDomains;
  } else {
    officialDomains = [...DEFAULT_DOMAINS];
    await browser.storage.local.set({ officialDomains });
  }
  rebuildIndex();
}

browser.storage.onChanged.addListener((changes, area) => {
  if (area === "local" && changes.officialDomains) {
    officialDomains = changes.officialDomains.newValue || [];
    rebuildIndex();
  }
});

/* Cerca un ufficiale UGUALE o MOLTO SIMILE (typo / omoglifi) a `candidate`. */
function nearestOfficial(candidate) {
  const nc = normalize(candidate);
  for (const o of officialIndex) {
    if (nc === o.norm) return o.domain;
    const dist = levenshtein(nc, o.norm);
    const maxDist = o.norm.length <= 6 ? 1 : 2;
    if (dist >= 1 && dist <= maxDist) return o.domain;
  }
  return null;
}

/* Riconosce quando il dominio ufficiale è incastonato come SOTTO-dominio di
   un altro dominio (es. "paypal.com.evil.ru"): l'utente vede "paypal.com"
   ma il dominio reale è "evil.ru".
   Richiediamo almeno 2 etichette dopo il blocco ufficiale, così NON segnaliamo
   i domini nazionali legittimi tipo "google.com.au". */
function findEmbeddedOfficial(hostname) {
  const H = hostname.toLowerCase().replace(/^www\./, "").split(".");
  for (const o of officialIndex) {
    const O = o.labels;
    if (O.length >= H.length) continue;
    for (let i = 0; i + O.length <= H.length; i++) {
      let match = true;
      for (let j = 0; j < O.length; j++) {
        if (H[i + j] !== O[j]) { match = false; break; }
      }
      if (match && (H.length - (i + O.length)) >= 2) {
        return o.domain;
      }
    }
  }
  return null;
}

/* Il dominio visitato è un sosia di uno ufficiale?
   Ritorna null se è tutto ok, oppure { domain, official, reason }. */
function findImpersonation(hostname) {
  const rawDomain = registrableDomain(hostname);

  if (officialDomains.includes(rawDomain)) return null;   // è il sito vero
  if (GREEN_LIST.has(rawDomain)) return null;             // sosia legittimo noto

  // 1) Typo / omoglifi ASCII (rn->m, 1->l, 0->o, ...).
  let official = nearestOfficial(rawDomain);
  if (official) return { domain: rawDomain, official, reason: "typo" };

  // 2) Omografo Unicode/IDN (cirillico, greco, punycode).
  if (/xn--/.test(hostname) || /[^\x00-\x7f]/.test(hostname)) {
    const skel = registrableDomain(toAsciiSkeleton(hostname));
    if (skel !== rawDomain) {
      official = nearestOfficial(skel);
      if (official) return { domain: rawDomain, official, reason: "omografi" };
    }
  }

  // 3) Dominio ufficiale usato come sotto-dominio di un altro (paypal.com.evil.ru).
  official = findEmbeddedOfficial(hostname);
  if (official) return { domain: rawDomain, official, reason: "sottodominio" };

  return null;
}

/* ─── Domini sbloccati dall'utente ("vai comunque"), per la sessione ───
   In storage.session: sopravvive allo spegnimento dell'event page. */
async function allowDomain(domain) {
  const { bypass = {} } = await browser.storage.session.get("bypass");
  bypass[domain] = true;
  await browser.storage.session.set({ bypass });
}

async function isBypassed(domain) {
  const { bypass = {} } = await browser.storage.session.get("bypass");
  return Boolean(bypass[domain]);
}

/* Aggiunge un dominio ai protetti (pulsante "Mi fido"). Valida e mette un tetto. */
async function addOfficial(domain) {
  if (typeof domain !== "string") return;
  if (!/^[a-z0-9.-]+\.[a-z]{2,}$/.test(domain)) return;
  if (officialDomains.includes(domain)) return;
  if (officialDomains.length >= MAX_DOMAINS) return;
  officialDomains = [...officialDomains, domain];
  rebuildIndex();
  await browser.storage.local.set({ officialDomains });
}

/* Contatore "minacce bloccate", mostrato nel popup. */
async function incrementBlocked() {
  const { blockedCount = 0 } = await browser.storage.local.get("blockedCount");
  await browser.storage.local.set({ blockedCount: blockedCount + 1 });
}

browser.runtime.onMessage.addListener((msg) => {
  if (!msg) return;
  if (msg.type === "proceed" && msg.domain) {
    return allowDomain(msg.domain).then(() => ({ ok: true }));
  }
  if (msg.type === "addOfficial" && msg.domain) {
    return addOfficial(msg.domain).then(() => ({ ok: true }));
  }
});

/* Intercetta la navigazione PRIMA che la pagina venga caricata. */
browser.webNavigation.onBeforeNavigate.addListener(async (details) => {
  if (details.frameId !== 0) return;

  let url;
  try { url = new URL(details.url); } catch (e) { return; }
  if (url.protocol !== "http:" && url.protocol !== "https:") return;
  if (!url.hostname.includes(".")) return;
  if (/^[\d.]+$/.test(url.hostname)) return;

  const hit = findImpersonation(url.hostname);
  if (!hit) return;
  if (await isBypassed(hit.domain)) return;

  await incrementBlocked();

  const warningUrl = browser.runtime.getURL("warning/warning.html")
    + "?suspicious=" + encodeURIComponent(hit.domain)
    + "&official="  + encodeURIComponent(hit.official)
    + "&reason="    + encodeURIComponent(hit.reason)
    + "&target="    + encodeURIComponent(details.url);

  browser.tabs.update(details.tabId, { url: warningUrl });
});

loadDomains();
