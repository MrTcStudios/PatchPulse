/* ============================================================
   PatchPulse — match.js
   Funzioni condivise per il riconoscimento dei domini "sosia".
   Questo file viene incluso SIA nel background SIA nel popup,
   cosi' la logica di pulizia/confronto dei domini e' una sola.
   ============================================================ */

/* Domini ufficiali predefiniti. L'utente puo' aggiungerne altri dal popup;
   questi vengono usati solo al primo avvio per riempire la lista. */
const DEFAULT_DOMAINS = [
  // PatchPulse (il sito di questo progetto)
  "patchpulse.org",
  // Google / Microsoft / Apple / Amazon
  "google.com", "gmail.com", "youtube.com",
  "microsoft.com", "outlook.com", "hotmail.com", "live.com", "office.com", "microsoftonline.com", "xbox.com",
  "apple.com", "icloud.com",
  "amazon.com", "amazon.it", "primevideo.com",
  // Email / cloud / produttività
  "yahoo.com", "proton.me", "protonmail.com", "zoho.com",
  "dropbox.com", "wetransfer.com", "adobe.com",
  "salesforce.com", "slack.com", "zoom.us", "notion.so",
  "atlassian.com", "trello.com", "asana.com", "canva.com", "figma.com",
  "cloudflare.com", "wordpress.com", "squarespace.com",
  "godaddy.com", "namecheap.com", "aruba.it", "photopea.com",
  // Social / messaggistica
  "facebook.com", "messenger.com", "instagram.com", "whatsapp.com",
  "twitter.com", "linkedin.com", "tiktok.com", "snapchat.com",
  "reddit.com", "pinterest.com", "tumblr.com", "quora.com",
  "telegram.org", "discord.com", "twitch.tv",
  // Pagamenti / fintech
  "paypal.com", "stripe.com", "wise.com", "revolut.com", "venmo.com",
  "westernunion.com", "skrill.com", "payoneer.com", "klarna.com",
  "n26.com", "sumup.com", "satispay.com", "nexi.it",
  // Banche internazionali
  "chase.com", "bankofamerica.com", "wellsfargo.com", "citibank.com",
  "capitalone.com", "americanexpress.com", "discover.com", "usbank.com",
  "hsbc.com", "barclays.co.uk", "santander.com", "deutsche-bank.de",
  // Banche / poste Italia
  "intesasanpaolo.com", "unicredit.it", "poste.it", "bancoposta.it",
  "fineco.it", "finecobank.com", "bper.it", "credit-agricole.it",
  "mediolanum.it", "bancasella.it", "widiba.it",
  // Crypto
  "binance.com", "coinbase.com", "kraken.com", "crypto.com",
  "metamask.io", "blockchain.com", "bybit.com", "kucoin.com",
  "ledger.com", "trezor.io", "bitfinex.com", "gemini.com", "etoro.com",
  // Shopping / marketplace
  "ebay.com", "ebay.it", "aliexpress.com", "alibaba.com", "etsy.com",
  "walmart.com", "shopify.com", "zalando.it", "subito.it", "vinted.com",
  "shein.com", "temu.com", "noicompriamoauto.it",
  // Viaggi / mobilità
  "booking.com", "airbnb.com", "expedia.com", "tripadvisor.com",
  "ryanair.com", "uber.com", "lyft.com", "trainline.com",
  // Streaming / media
  "netflix.com", "spotify.com", "disneyplus.com", "hulu.com",
  "paramountplus.com", "dazn.com",
  // Gaming
  "steampowered.com", "steamcommunity.com", "epicgames.com", "playstation.com",
  "nintendo.com", "roblox.com", "riotgames.com", "battle.net",
  "minecraft.net", "mojang.com", "fortnite.com",
  // Dev / lavoro
  "github.com", "gitlab.com", "bitbucket.org", "stackoverflow.com",
  "npmjs.com", "docker.com", "example.com",
  // Spedizioni (bersagli tipici di smishing)
  "fedex.com", "usps.com", "dhl.com", "gls-italy.com",
  // Provider / portali Italia
  "libero.it", "virgilio.it", "tiscali.it",
  // Telecom Italia
  "vodafone.it", "windtre.it", "fastweb.it", "iliad.it",
  // Pubblica amministrazione / istituzioni
  "agenziaentrate.gov.it", "inps.it", "spid.gov.it", "agid.gov.it",
  "poliziadistato.it", "carabinieri.it", "irs.gov", "europa.eu"
];

/* TLD a due livelli piu' comuni: servono per capire qual e' il dominio
   "registrabile" (es. in "login.poste.it" il dominio e' "poste.it",
   ma in "tizio.gov.it" il dominio utile e' "tizio.gov.it"). */
const MULTI_PART_TLDS = new Set([
  "co.uk", "org.uk", "gov.uk", "ac.uk",
  "com.au", "co.jp", "com.br", "co.in", "gov.it"
]);

/* Domini LEGITTIMI che somigliano a quelli ufficiali: non vanno mai segnalati,
   altrimenti l'utente riceve falsi allarmi su siti noti e affidabili.
   Lista curata ed estendibile. */
const GREEN_LIST = new Set([
  "doodle.com",   // legittimo, simile a google.com
  "paypay.com",   // PayPay (Giappone), simile a paypal.com
  "finance.com",  // legittimo, simile a binance.com
  "wix.com",      // legittimo, vicino a wise.com
  "lulu.com",     // legittimo, vicino a hulu.com
  "easy.com",     // easyGroup, vicino a etsy.com
  "dell.com",     // legittimo, vicino a dhl.com
  "love.com",     // legittimo, vicino a live.com
  "hive.com",     // legittimo, vicino a live.com
  "appleid.com",  // dominio reale di Apple (redirect ufficiale)
  "mail.com",     // provider email reale, dista 1 lettera da gmail.com
  "fortnine.com"  // negozio moto canadese, dista 1 lettera da fortnite.com
]);

/* Parole escluse dal SOLO controllo "brand+parola ATTACCATI": "pay" attaccato
   al brand e' quasi sempre un prodotto reale del brand stesso (postepay.it,
   googlepay.com, amazonpay.com, applepay.com). Col trattino (poste-pay.it)
   resta invece un segnale di phishing e viene ancora rilevato. */
const CONCAT_EXCLUDED = new Set(["pay"]);

/* Brand il cui nome è una parola comune: per QUESTI non applichiamo i controlli
   "nome del brand + parola sospetta" (combo/sottodominio/TLD), perché parole come
   live, office o crypto compaiono in mille domini legittimi -> falsi allarmi. */
const BRAND_EXCLUDED = new Set([
  "live", "office", "crypto", "wise", "discover", "chase", "gemini"
]);

/* Parole tipiche dei domini di phishing, in inglese e italiano. Usate per i
   controlli combo-squatting e brand-nel-sottodominio. */
const SUSPICIOUS_WORDS = new Set([
  // EN
  "login", "signin", "sign", "secure", "security", "verify", "verified",
  "verification", "account", "accounts", "auth", "support", "help",
  "service", "services", "update", "billing", "payment", "payments", "pay",
  "wallet", "confirm", "confirmation", "id", "password", "recovery",
  "recover", "unlock", "alert", "alerts", "customer", "online", "banking",
  "web", "portal", "access", "validate", "validation", "official",
  // IT
  "assistenza", "accesso", "accedi", "conferma", "verifica", "sicurezza",
  "clienti", "pagamento", "pagamenti", "supporto", "aggiorna", "blocco",
  "sblocca", "premio"
]);

/* TLD economici/gratuiti usati in modo sproporzionato per il phishing.
   Un brand noto su uno di questi TLD è quasi sempre un falso. */
const SUSPICIOUS_TLDS = new Set([
  "tk", "ml", "ga", "cf", "gq", "top", "xyz", "icu", "club", "click",
  "link", "cam", "monster", "cyou", "bid", "loan", "work", "men", "date",
  "faith", "review", "racing", "download", "stream", "zip", "mov",
  "support", "cfd", "sbs", "rest", "quest", "surf",
  "co", "cm", "om"   // typo classici di ".com" (paypal.co, paypal.cm)
]);

/* Normalizza gli "omoglifi" ASCII: caratteri diversi che SEMBRANO uguali.
   Cosi' "rnicrosoft" diventa "microsoft", "paypa1" diventa "paypal" e
   "pay-pal" diventa "paypal" (i trattini sono spesso usati per camuffare). */
function normalize(domain) {
  return domain
    .toLowerCase()
    .replace(/-/g, "")     // trattini camuffati: pay-pal -> paypal
    .replace(/rn/g, "m")   // r+n -> m
    .replace(/vv/g, "w")   // v+v -> w
    .replace(/0/g, "o")    // zero -> o
    .replace(/1/g, "l")    // uno  -> l
    .replace(/5/g, "s")    // cinque -> s
    .replace(/\$/g, "s");
}

/* Distanza di Damerau-Levenshtein (variante OSA): quante modifiche minime
   (aggiungi/togli/cambia una lettera, o SCAMBIA due lettere adiacenti)
   servono per trasformare "a" in "b". Lo scambio conta 1: cosi' "googel"
   dista 1 da "google" e viene riconosciuto anche sui nomi corti. */
function levenshtein(a, b) {
  const m = a.length, n = b.length;
  if (m === 0) return n;
  if (n === 0) return m;
  let prev2 = new Array(n + 1);
  let prev = Array.from({ length: n + 1 }, (_, j) => j);
  let curr = new Array(n + 1);
  for (let i = 1; i <= m; i++) {
    curr[0] = i;
    for (let j = 1; j <= n; j++) {
      const cost = a[i - 1] === b[j - 1] ? 0 : 1;
      let v = Math.min(prev[j] + 1, curr[j - 1] + 1, prev[j - 1] + cost);
      if (i > 1 && j > 1 && a[i - 1] === b[j - 2] && a[i - 2] === b[j - 1]) {
        v = Math.min(v, prev2[j - 2] + 1);   // trasposizione adiacente
      }
      curr[j] = v;
    }
    const tmp = prev2; prev2 = prev; prev = curr; curr = tmp;
  }
  return prev[n];
}

/* Parte "nome" del dominio registrabile, senza TLD e senza trattini.
   "intesa-sanpaolo.com" -> "intesasanpaolo" ; "barclays.co.uk" -> "barclays" */
function brandOf(registrable) {
  return registrable.split(".")[0].replace(/-/g, "");
}

/* TLD del dominio registrabile: "paypal.tk" -> "tk" ; "barclays.co.uk" -> "co.uk" */
function tldOf(registrable) {
  return registrable.split(".").slice(1).join(".");
}

/* Estrae il dominio registrabile da un hostname.
   "www.login.poste.it" -> "poste.it" ; "tizio.gov.it" -> "tizio.gov.it"
   NB: versione semplificata. In produzione si userebbe la Public Suffix List. */
function registrableDomain(hostname) {
  const parts = hostname.toLowerCase().replace(/^www\./, "").split(".");
  if (parts.length <= 2) return parts.join(".");
  const lastTwo = parts.slice(-2).join(".");
  if (MULTI_PART_TLDS.has(lastTwo)) return parts.slice(-3).join(".");
  return lastTwo;
}

/* =============================================================
   OMOGRAFI UNICODE (attacchi IDN, es. la "a" cirillica al posto della "a")
   ============================================================= */

/* Caratteri non-latini che assomigliano (spesso in modo identico) a lettere
   ASCII. L'encoding UTF-8 del file preserva correttamente questi glifi.
   Mappa conservativa: solo glifi davvero confondibili, cosi' non penalizziamo
   i domini internazionali legittimi (es. "muller.de" con la u-umlaut). */
const CONFUSABLES = {
  // Cirillico
  "а": "a", "е": "e", "о": "o", "р": "p", "с": "c",
  "у": "y", "х": "x", "ѕ": "s", "і": "i", "ј": "j",
  "ԁ": "d", "һ": "h", "ӏ": "l", "к": "k", "м": "m",
  "в": "b",
  // Greco
  "ο": "o", "α": "a", "ρ": "p", "ε": "e", "ι": "i",
  "ν": "v", "κ": "k", "τ": "t", "υ": "u", "χ": "x",
  "ϲ": "c"
};

/* Decodifica un'etichetta punycode (la parte dopo "xn--") in testo Unicode.
   Implementazione dell'algoritmo RFC 3492. */
function punycodeDecode(input) {
  const maxInt = 2147483647;
  const base = 36, tMin = 1, tMax = 26, skew = 38, damp = 700;
  const initialBias = 72, initialN = 128, delimiter = "-";

  function digitValue(cp) {
    if (cp >= 0x30 && cp <= 0x39) return cp - 0x30 + 26; // 0-9
    if (cp >= 0x41 && cp <= 0x5A) return cp - 0x41;       // A-Z
    if (cp >= 0x61 && cp <= 0x7A) return cp - 0x61;       // a-z
    return base;
  }

  function adapt(delta, numPoints, firstTime) {
    delta = firstTime ? Math.floor(delta / damp) : delta >> 1;
    delta += Math.floor(delta / numPoints);
    let k = 0;
    while (delta > ((base - tMin) * tMax) >> 1) {
      delta = Math.floor(delta / (base - tMin));
      k += base;
    }
    return Math.floor(k + ((base - tMin + 1) * delta) / (delta + skew));
  }

  const output = [];
  let i = 0, n = initialN, bias = initialBias;

  let basicEnd = input.lastIndexOf(delimiter);
  if (basicEnd < 0) basicEnd = 0;
  for (let j = 0; j < basicEnd; j++) output.push(input.charCodeAt(j));

  let index = basicEnd > 0 ? basicEnd + 1 : 0;
  while (index < input.length) {
    const oldi = i;
    let w = 1;
    for (let k = base; ; k += base) {
      if (index >= input.length) throw new RangeError("punycode");
      const digit = digitValue(input.charCodeAt(index++));
      if (digit >= base) throw new RangeError("punycode");
      if (digit > Math.floor((maxInt - i) / w)) throw new RangeError("overflow");
      i += digit * w;
      const t = k <= bias ? tMin : (k >= bias + tMax ? tMax : k - bias);
      if (digit < t) break;
      const baseMinusT = base - t;
      if (w > Math.floor(maxInt / baseMinusT)) throw new RangeError("overflow");
      w *= baseMinusT;
    }
    const outLength = output.length + 1;
    bias = adapt(i - oldi, outLength, oldi === 0);
    if (Math.floor(i / outLength) > maxInt - n) throw new RangeError("overflow");
    n += Math.floor(i / outLength);
    i %= outLength;
    output.splice(i++, 0, n);
  }
  return String.fromCodePoint(...output);
}

/* Converte un hostname con etichette punycode (xn--...) nella forma Unicode. */
function idnToUnicode(hostname) {
  return hostname.split(".").map((label) => {
    if (label.startsWith("xn--")) {
      try { return punycodeDecode(label.slice(4)); } catch (e) { return label; }
    }
    return label;
  }).join(".");
}

/* "Scheletro" ASCII di un hostname: decodifica l'IDN e sostituisce i caratteri
   confondibili con la loro lettera latina. Cosi' "appl-e" cirillico diventa
   "apple.com" ed e' confrontabile con i domini ufficiali. */
function toAsciiSkeleton(hostname) {
  const unicode = idnToUnicode(hostname.toLowerCase());
  let out = "";
  for (const ch of unicode) out += (CONFUSABLES[ch] || ch);
  return out;
}
