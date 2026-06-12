/* ============================================================
   PatchPulse — background.js
   Il "cervello" dell'estensione. Ascolta ogni navigazione, confronta il
   dominio con la lista di domini ufficiali e — se trova un sosia — reindirizza
   a una pagina di avviso PRIMA che il sito sospetto venga caricato.

   Controlli, in ordine:
     1. typo/omoglifi ASCII e trattini (distanza di Damerau-Levenshtein),
        su qualsiasi TLD se il nome e' un omoglifo del brand
     2. omografi Unicode/IDN (cirillico/greco/armeno, punycode xn--)
     3. dominio ufficiale incastonato nei sottodomini (paypal.com.evil.ru),
        anche con trattini (paypal.com-secure.ru) o su TLD sospetto
     4. nome del brand come sottodominio + parola sospetta (paypal.verify-x.ru)
     4b. brand su piattaforma di hosting gratuito (secure-paypal.vercel.app)
     5. combo-squatting: brand + parola sospetta, anche con omoglifi
        (paypal-login.com, paypa1-login.com)
     6. abuso di TLD: brand esatto o con un typo su TLD spazzatura (paypal.tk)
     7. etichetta a script misti latino+non-latino (omografo generico)

   Funzioni e liste arrivano da lib/match.js.
   ============================================================ */

const MAX_DOMAINS = 5000;     // tetto di sicurezza alla lista personalizzabile
const DEFAULTS_VERSION = 5;   // alza questo numero ogni volta che amplii DEFAULT_DOMAINS

let officialDomains = [];
let officialIndex = [];   // pre-calcolo: [{ domain, norm, labels, brand, brandNorm }]

/* Pre-calcola una volta sola le forme derivate dei domini ufficiali,
   cosi' non rifacciamo il lavoro a ogni navigazione. */
function rebuildIndex() {
  officialIndex = officialDomains.map((d) => ({
    domain: d,
    norm: normalize(d),
    labels: d.split("."),
    brand: brandOf(d),
    brandNorm: normalize(brandOf(d)),
    tld: tldOf(d)
  }));
}

async function loadDomains() {
  const stored = await browser.storage.local.get(["officialDomains", "defaultsVersion"]);
  if (!stored.officialDomains || !stored.officialDomains.length) {
    // Primo avvio: parti dalla lista predefinita completa.
    officialDomains = [...DEFAULT_DOMAINS];
    await browser.storage.local.set({ officialDomains, defaultsVersion: DEFAULTS_VERSION });
  } else if ((stored.defaultsVersion || 1) < DEFAULTS_VERSION) {
    // Aggiornamento: aggiunge i nuovi domini predefiniti mancanti, SENZA
    // rimuovere quelli aggiunti (o tenuti) dall'utente.
    const merged = new Set(stored.officialDomains);
    for (const d of DEFAULT_DOMAINS) merged.add(d);
    officialDomains = [...merged];
    await browser.storage.local.set({ officialDomains, defaultsVersion: DEFAULTS_VERSION });
  } else {
    officialDomains = stored.officialDomains;
  }
  rebuildIndex();
}

browser.storage.onChanged.addListener((changes, area) => {
  if (area === "local" && changes.officialDomains) {
    officialDomains = changes.officialDomains.newValue || [];
    rebuildIndex();
  }
});

/* ── 1) Typo/omoglifi: ufficiale UGUALE o MOLTO SIMILE a `candidate`.
   Soglia stretta sui nomi corti (1 modifica fino a 8 caratteri) per ridurre
   i falsi allarmi tra nomi brevi legittimi (es. dhl.com vs dell.com). */
function nearestOfficial(candidate) {
  const nc = normalize(candidate);
  // Domini con lettere non-ASCII (accentate: mañana, café, müller) sono IDN
  // legittimi: NON li confrontiamo per distanza con brand ASCII (eviterebbe
  // mañana.com -> asana.com). Gli attacchi IDN passano via skeleton/script-misti.
  if (/[^\x00-\x7f]/.test(nc)) return null;
  const cBrandRaw = brandOf(candidate);          // senza trattini, NON normalizzato
  const cBrandNorm = normalize(cBrandRaw);
  const cTld = tldOf(candidate);
  for (const o of officialIndex) {
    if (nc === o.norm) return o.domain;
    // Nome identico solo DOPO la normalizzazione (paypa1, g00gle, rnicrosoft):
    // e' un omoglifo del brand -> attacco, su QUALSIASI TLD (paypa1.co.za).
    if (cBrandRaw !== o.brand && cBrandNorm === o.brandNorm) return o.domain;
    // Brand davvero identico, TLD diverso (amazon.de vs amazon.it): variante
    // nazionale quasi sempre legittima -> niente confronto per distanza.
    // I TLD davvero pericolosi (.tk, .co, .cm...) li gestisce findTldAbuse.
    if (cBrandRaw === o.brand && cTld !== o.tld) continue;
    const dist = levenshtein(nc, o.norm);
    const maxDist = o.norm.length <= 8 ? 1 : 2;
    if (dist >= 1 && dist <= maxDist) return o.domain;
  }
  return null;
}

/* ── 3) Dominio ufficiale incastonato nei sottodomini.
   "paypal.com.evil.ru": l'utente vede "paypal.com" ma il dominio vero e' altro.
   Regole anti-falso-positivo: dopo il blocco ufficiale devono esserci almeno
   2 etichette (cosi' google.com.au resta legittimo), OPPURE esattamente 1 se
   e' un TLD sospetto (cosi' paypal.com.tk viene beccato). */
function findEmbeddedOfficial(labels) {
  for (const o of officialIndex) {
    const O = o.labels;
    if (O.length >= labels.length) continue;
    for (let i = 0; i + O.length <= labels.length; i++) {
      let match = true;
      for (let j = 0; j < O.length; j++) {
        if (labels[i + j] !== O[j]) { match = false; break; }
      }
      if (!match) continue;
      const after = labels.length - (i + O.length);
      if (after >= 2) return o.domain;
      if (after === 1 && SUSPICIOUS_TLDS.has(labels[labels.length - 1])) return o.domain;
    }
  }
  return null;
}

/* ── 4) Nome del brand usato come sottodominio di un dominio estraneo
   (es. "paypal.secure-verify.ru"). Per non segnalare siti legittimi come
   apple.stackexchange.com, scatta SOLO se nell'indirizzo c'e' anche una
   parola tipica del phishing, o se il dominio sta su un TLD sospetto. */
function findBrandSubdomain(hostname, registrable) {
  if (hostname.length <= registrable.length) return null;
  const subPart = hostname.slice(0, hostname.length - registrable.length - 1);
  const subLabels = subPart.split(".").filter(Boolean);
  if (!subLabels.length) return null;

  const allTokens = hostname.split(/[.-]/).filter(Boolean);
  const hasSuspicious = allTokens.some((tk) => SUSPICIOUS_WORDS.has(tk));
  if (!hasSuspicious && !SUSPICIOUS_TLDS.has(tldOf(registrable))) return null;

  for (const o of officialIndex) {
    if (o.brand.length < 3 || BRAND_EXCLUDED.has(o.brand)) continue;
    if (registrable === o.domain) continue;
    for (const lb of subLabels) {
      if (normalize(lb) === o.brandNorm) return o.domain;
    }
  }
  return null;
}

/* ── 5) Combo-squatting: il nome del brand combinato con parole tipiche del
   phishing, con trattino ("paypal-login.com", "secure-paypal.com") o
   attaccato ("applesupport.com", "loginpaypal.com").
   I token sono NORMALIZZATI prima del confronto, cosi' becca anche il brand
   camuffato con omoglifi ("paypa1-login.com", "g00gle-support.com"): resta
   comunque richiesta una parola sospetta, quindi niente nuovi falsi positivi. */
function findComboSquat(registrable) {
  const sld = registrable.split(".")[0];
  const tokens = sld.split("-").map(normalize).filter(Boolean);
  const words = tokens.filter((tk) => SUSPICIOUS_WORDS.has(tk));
  const flat = tokens.join("");
  for (const o of officialIndex) {
    const b = o.brandNorm;
    if (b.length < 3 || BRAND_EXCLUDED.has(o.brand)) continue;
    if (o.domain === registrable) continue;

    // Forma con trattini: un token E' il brand e un ALTRO token e' parola sospetta.
    if (tokens.length > 1 && tokens.includes(b) &&
        words.some((w) => w !== b)) {
      return o.domain;
    }
    // Forma attaccata: brand+parola o parola+brand (senza trattini).
    // CONCAT_EXCLUDED: "brand+pay" e' quasi sempre un prodotto reale del brand
    // (postepay, googlepay...), non un attacco.
    if (tokens.length === 1 && flat !== b && flat.length > b.length) {
      const suffix = flat.slice(b.length);
      if (flat.startsWith(b) && SUSPICIOUS_WORDS.has(suffix) && !CONCAT_EXCLUDED.has(suffix)) return o.domain;
      const prefix = flat.slice(0, flat.length - b.length);
      if (flat.endsWith(b) && SUSPICIOUS_WORDS.has(prefix) && !CONCAT_EXCLUDED.has(prefix)) return o.domain;
    }
  }
  return null;
}

/* ── 5b) Brand su piattaforma di hosting gratuito ("secure-paypal.vercel.app",
   "paypa1.github.io"). Gating anti-falso-positivo FORTE: un'azienda vera puo'
   avere "microsoft.github.io", quindi segnaliamo SOLO se l'etichetta e' un
   OMOGLIFO del brand (non il brand esatto) oppure se compare insieme a una
   parola sospetta. Il brand esatto da solo NON basta. */
function findHostedBrand(hostname, registrable) {
  if (!HOSTING_PLATFORMS.has(registrable)) return null;
  if (hostname.length <= registrable.length) return null;
  const sub = hostname.slice(0, hostname.length - registrable.length - 1);
  const rawTokens = sub.split(/[.-]/).filter(Boolean);
  const hasSuspicious = rawTokens.some((tk) => SUSPICIOUS_WORDS.has(normalize(tk)));
  for (const o of officialIndex) {
    if (o.brandNorm.length < 3 || BRAND_EXCLUDED.has(o.brand)) continue;
    for (const tk of rawTokens) {
      if (normalize(tk) !== o.brandNorm) continue;
      if (tk !== o.brand) return o.domain;   // omoglifo/typo del brand -> sicuro
      if (hasSuspicious) return o.domain;     // brand esatto + parola sospetta
    }
  }
  return null;
}

/* ── 6) Abuso di TLD: il nome del brand su un TLD economico/sospetto.
   Due forme: brand esatto ("paypal.tk", "g00gle.xyz") e brand come token
   separato da trattini ("minecraft-premium.tk", "fortnite-skins.tk").
   La forma a token NON si applica a .co/.cm/.om (typo di .com ma usati anche
   da siti legittimi, es. agenzie "brand-experts.co"): li' serve il brand esatto. */
const TLD_TOKEN_EXEMPT = new Set(["co", "cm", "om"]);

function findTldAbuse(registrable) {
  const tld = tldOf(registrable);
  if (!SUSPICIOUS_TLDS.has(tld)) return null;
  const sld = registrable.split(".")[0];
  const nb = normalize(sld);
  if (nb.length < 3) return null;
  // Sui TLD-spazzatura veri (non .co/.cm/.om, usati anche legittimamente)
  // accettiamo anche il brand a 1 modifica di distanza: li' un typo del brand
  // e' quasi sempre malevolo (es. "amazoon.tk", "netfllx.top").
  const fuzzy = !TLD_TOKEN_EXEMPT.has(tld);
  const tokens = fuzzy ? sld.split("-").filter(Boolean) : [];
  for (const o of officialIndex) {
    if (BRAND_EXCLUDED.has(o.brand)) continue;
    if (o.domain === registrable) continue;
    if (nb === o.brandNorm) return o.domain;
    if (fuzzy && o.brandNorm.length >= 5 && levenshtein(nb, o.brandNorm) === 1) return o.domain;
    for (const tk of tokens) {
      if (tk.length >= 3 && normalize(tk) === o.brandNorm) return o.domain;
    }
  }
  return null;
}

/* Pipeline completa. Ritorna null se e' tutto ok,
   oppure { domain, official, reason }. */
function findImpersonation(hostname) {
  hostname = hostname.toLowerCase().replace(/\.$/, "").replace(/^www\./, "");
  const rawDomain = registrableDomain(hostname);

  if (officialDomains.includes(rawDomain)) return null;   // e' il sito vero
  if (GREEN_LIST.has(rawDomain)) return null;             // sosia legittimo noto

  // 1) Typo / omoglifi ASCII / trattini camuffati.
  let official = nearestOfficial(rawDomain);
  if (official) return { domain: rawDomain, official, reason: "typo" };

  const isIdn = /xn--/.test(hostname) || /[^\x00-\x7f]/.test(hostname);

  // 2) Omografo Unicode/IDN (cirillico, greco, punycode).
  if (isIdn) {
    const skel = registrableDomain(toAsciiSkeleton(hostname));
    if (skel !== rawDomain) {
      official = nearestOfficial(skel);
      if (official) return { domain: rawDomain, official, reason: "omografi" };
    }
  }

  // 3) Ufficiale incastonato nei sottodomini, anche con trattini al posto dei punti.
  official = findEmbeddedOfficial(hostname.split("."));
  if (!official && hostname.includes("-")) {
    official = findEmbeddedOfficial(hostname.replace(/-/g, ".").split("."));
  }
  if (official) return { domain: rawDomain, official, reason: "sottodominio" };

  // 4) Brand come sottodominio + segnali di phishing.
  official = findBrandSubdomain(hostname, rawDomain);
  if (official) return { domain: rawDomain, official, reason: "sottodominio" };

  // 4b) Brand su piattaforma di hosting gratuito (secure-paypal.vercel.app).
  official = findHostedBrand(hostname, rawDomain);
  if (official) return { domain: rawDomain, official, reason: "hosting" };

  // 5) Combo-squatting (brand + parola sospetta).
  official = findComboSquat(rawDomain);
  if (official) return { domain: rawDomain, official, reason: "combo" };

  // 6) Brand esatto (o typo) su TLD sospetto.
  official = findTldAbuse(rawDomain);
  if (official) return { domain: rawDomain, official, reason: "tld" };

  // 7) Etichetta a script misti (latino + cirillico/greco/armeno): omografo
  //    malevolo a prescindere dal brand. Ultimo, cosi' i casi noti mantengono
  //    il motivo piu' specifico.
  if (isIdn && hasMixedScript(hostname)) {
    return { domain: rawDomain, official: rawDomain, reason: "scriptmisti" };
  }

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

/* Registra la minaccia: contatore totale + ultime 10 (mostrate nel popup).
   Tutto in storage locale, nessun dato lascia il browser. */
async function recordThreat(hit) {
  const { blockedCount = 0, recentBlocked = [] } =
    await browser.storage.local.get(["blockedCount", "recentBlocked"]);
  recentBlocked.unshift({ d: hit.domain, o: hit.official, r: hit.reason, t: Date.now() });
  if (recentBlocked.length > 10) recentBlocked.length = 10;
  await browser.storage.local.set({ blockedCount: blockedCount + 1, recentBlocked });
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

/* Dedup tra onBeforeNavigate e onCommitted: evita doppio conteggio/redirect
   sullo stesso (tab, url) a breve distanza. In memoria: se l'event page si
   spegne perdiamo la cache, ma isBypassed copre comunque i casi di confine. */
const handledRecently = new Map();

/* Logica unica condivisa dai due listener di navigazione. */
async function handleNavigation(details) {
  if (details.frameId !== 0) return;

  let url;
  try { url = new URL(details.url); } catch (e) { return; }
  if (url.protocol !== "http:" && url.protocol !== "https:") return;
  if (!url.hostname.includes(".")) return;
  if (/^[\d.]+$/.test(url.hostname)) return;

  const prev = handledRecently.get(details.tabId);
  if (prev && prev.url === details.url && Date.now() - prev.ts < 4000) return;
  if (handledRecently.size > 200) handledRecently.clear();
  handledRecently.set(details.tabId, { url: details.url, ts: Date.now() });

  const hit = findImpersonation(url.hostname);
  if (!hit) return;
  if (await isBypassed(hit.domain)) return;

  await recordThreat(hit);

  const warningUrl = browser.runtime.getURL("warning/warning.html")
    + "?suspicious=" + encodeURIComponent(hit.domain)
    + "&official="  + encodeURIComponent(hit.official)
    + "&reason="    + encodeURIComponent(hit.reason)
    + "&target="    + encodeURIComponent(details.url);

  browser.tabs.update(details.tabId, { url: warningUrl });
}

/* onBeforeNavigate: blocca PRIMA del caricamento (caso normale).
   onCommitted: rete di sicurezza per i redirect lato server (30x) che possono
   non rilanciare onBeforeNavigate sull'hop finale. */
browser.webNavigation.onBeforeNavigate.addListener(handleNavigation);
browser.webNavigation.onCommitted.addListener(handleNavigation);

/* Alla PRIMA installazione (non agli aggiornamenti) apre la pagina di
   benvenuto, dove l'utente puo' subito aggiungere i propri siti. */
browser.runtime.onInstalled.addListener((details) => {
  if (details.reason === "install") {
    browser.tabs.create({ url: browser.runtime.getURL("onboarding/onboarding.html") });
  }
});

loadDomains();
