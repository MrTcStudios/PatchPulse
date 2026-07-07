/* ============================================================
   PatchPulse — match.js
   Funzioni condivise per il riconoscimento dei domini "sosia".
   Questo file viene incluso SIA nel background SIA nel popup,
   cosi' la logica di pulizia/confronto dei domini e' una sola.
   ============================================================ */

/* Tetto di sicurezza alla lista personalizzabile. Qui (e non nel background)
   perche' lo applicano anche popup e onboarding, che scrivono direttamente. */
const MAX_DOMAINS = 5000;

/* Domini ufficiali predefiniti. L'utente puo' aggiungerne altri dal popup;
   questi vengono usati solo al primo avvio per riempire la lista. */
const DEFAULT_DOMAINS = [
  // PatchPulse (il sito di questo progetto)
  "patchpulse.org",
  // Google / Microsoft / Apple / Amazon
  "google.com", "gmail.com", "youtube.com",
  "microsoft.com", "outlook.com", "hotmail.com", "live.com", "office.com", "microsoftonline.com", "onedrive.com", "xbox.com",
  "apple.com", "icloud.com",
  "amazon.com", "amazon.it", "primevideo.com",
  // Email / cloud / produttività
  "yahoo.com", "proton.me", "protonmail.com", "zoho.com",
  "dropbox.com", "wetransfer.com", "adobe.com", "docusign.com",
  "salesforce.com", "slack.com", "zoom.us", "notion.so",
  "atlassian.com", "trello.com", "asana.com", "canva.com", "figma.com",
  "cloudflare.com", "wordpress.com", "squarespace.com",
  "godaddy.com", "namecheap.com", "aruba.it", "photopea.com",
  // AI (tra i brand piu' phishati dal 2023 in poi)
  "openai.com", "chatgpt.com",
  // Social / messaggistica
  "facebook.com", "messenger.com", "instagram.com", "whatsapp.com",
  "twitter.com", "linkedin.com", "tiktok.com", "snapchat.com",
  "reddit.com", "pinterest.com", "tumblr.com", "quora.com",
  "telegram.org", "discord.com", "twitch.tv",
  // Pagamenti / fintech
  "paypal.com", "stripe.com", "wise.com", "revolut.com", "venmo.com",
  "westernunion.com", "skrill.com", "payoneer.com", "klarna.com",
  "n26.com", "sumup.com", "satispay.com", "nexi.it",
  "postepay.it", "googlepay.com", "googleplay.com", "amazonpay.com", "applepay.com",
  // Banche internazionali
  "chase.com", "bankofamerica.com", "wellsfargo.com", "citibank.com",
  "capitalone.com", "americanexpress.com", "discover.com", "usbank.com",
  "hsbc.com", "barclays.co.uk", "santander.com", "deutsche-bank.de",
  // Banche / poste Italia
  "intesasanpaolo.com", "unicredit.it", "poste.it", "posteitaliane.it", "bancoposta.it",
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
  "paramountplus.com", "dazn.com", "sky.it",
  // Gaming
  "steampowered.com", "steamcommunity.com", "epicgames.com", "playstation.com",
  "nintendo.com", "roblox.com", "riotgames.com", "battle.net",
  "minecraft.net", "mojang.com", "fortnite.com",
  // Dev / lavoro
  "github.com", "gitlab.com", "bitbucket.org", "stackoverflow.com",
  "npmjs.com", "docker.com", "example.com",
  // Spedizioni (bersagli tipici di smishing)
  "fedex.com", "usps.com", "ups.com", "dhl.com", "gls-italy.com",
  "brt.it", "sda.it", "inpost.it",
  // Provider / portali Italia
  "libero.it", "virgilio.it", "tiscali.it",
  // Telecom Italia
  "vodafone.it", "windtre.it", "fastweb.it", "iliad.it", "tim.it",
  // Energia / utility Italia
  "enel.it", "eni.com",
  // Pubblica amministrazione / istituzioni
  "agenziaentrate.gov.it", "inps.it", "spid.gov.it", "agid.gov.it",
  "poliziadistato.it", "carabinieri.it", "irs.gov", "europa.eu"
];

/* Fallback minimo usato SOLO se lib/psl.js non e' caricato (vedi
   registrableDomain): la fonte vera e' la Public Suffix List completa. */
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
  "fortnine.com", // negozio moto canadese, dista 1 lettera da fortnite.com
  "appletv.com",  // dominio reale di Apple, dista 2 lettere da apple.com
  "discovery.com",// Discovery Channel, dista 1 lettera da discover.com
  "uspa.com",     // U.S. Polo Assn, dista 1 lettera da usps.com
  "telegraf.rs",  // quotidiano serbo, brand a distanza 1 da telegram

  // ── Sweep Tranco top-10k (2026-07-08, tools/fp-sweep.html): siti REALI e
  //    popolari a distanza 1-2 da un protetto, oppure domini DI PROPRIETÀ dei
  //    brand stessi (onmicrosoft, adobelogin, telegra.ph, wal-mart, apple.co,
  //    google.ml/cf, tiktokv, myshopify, slackb, minecraftservices...).
  //    NON aggiungere nulla di non verificato: gli scarti dello sweep restano
  //    segnalati apposta (tiktokio, tumbex, strip2, cqloud, pacloudflare...).
  "aaa.com", "adage.com", "adobelogin.com", "adobesc.com", "adobess.com",
  "aetna.com", "afribaba.com", "airbus.com", "aiscore.com", "alamy.com",
  "apple.co", "applvn.com", "asahi.com", "asda.com", "badoo.com",
  "betking.com", "boeing.com", "box.com", "bumble.com", "byspotify.com",
  "canon.com", "chanel.com", "chess.com", "cigna.com", "cloud.com",
  "cloudflareaccess.com", "cloudflareportal.com", "ctrip.com", "dan.com",
  "dawn.com", "discogs.com", "e2ro.com", "eiga.com", "experian.com",
  "fifa.com", "fortinet.com", "gaana.com", "gigya.com", "gitea.com",
  "goal.com", "google.cf", "google.ml", "hicloud.com", "hotmart.com",
  "intel.com", "iscorp.com", "java.com", "joker.com", "luma.com",
  "microsoftonline.us", "minecraft-services.net", "minecraftservices.com",
  "mixcloud.com", "mlive.com", "moodle.com", "myshopify.com", "oanda.com",
  "ollama.com", "onmicrosoft.com", "opera.com", "ora.com", "osano.com",
  "overdrive.com", "pbase.com", "qcloud.com", "qunar.com", "redfin.com",
  "redhat.com", "replit.com", "revolve.com", "rumble.com", "s-microsoft.com",
  "savana.com", "scmp.com", "shell.com", "shopier.com", "sina.com",
  "slackb.com", "slate.com", "space.com", "telegra.ph", "tiktokv.com",
  "tmall.com", "torob.com", "tospotify.com", "trellix.com", "trip.com",
  "united.com", "usaa.com", "wal-mart.com", "webex.com", "wetter.com",
  "wo-cloud.com", "xboxservices.com", "yango.com", "yumpu.com"
]);

/* Brand dei domini in green-list. Servono alla regola "typo del brand su TLD
   diverso": le ALTRE estensioni degli stessi siti legittimi (fortnine.ca,
   paypay.co.jp) non vanno segnalate solo perche' il .com e' in green-list. */
const GREEN_BRANDS = new Set([...GREEN_LIST].map((d) => brandOf(d)));

/* Parole escluse dal SOLO controllo "brand+parola ATTACCATI": queste parole
   attaccate al brand sono quasi sempre un prodotto reale del brand stesso
   (postepay.it, googlepay.com, applecare.com, microsoftrewards.com,
   zohomail.com, playstationstore.com, vodafoneitalia.it...).
   Col trattino (poste-pay.it, apple-care.tk) restano invece un segnale di
   phishing e vengono ancora rilevate. */
const CONCAT_EXCLUDED = new Set([
  "pay", "care", "mail", "store", "rewards", "italia", "italiane"
]);

/* Brand il cui nome è una parola comune: per QUESTI non applichiamo i controlli
   "nome del brand + parola sospetta" (combo/sottodominio/TLD), perché parole come
   live, office o crypto compaiono in mille domini legittimi -> falsi allarmi. */
const BRAND_EXCLUDED = new Set([
  "live", "office", "crypto", "wise", "discover", "chase", "gemini"
]);

/* Parole tipiche dei domini di phishing, in inglese e italiano. Usate per i
   controlli combo-squatting e brand-nel-sottodominio.
   ATTENZIONE: ogni parola nuova va validata con la rassegna anti-falsi-positivi
   della suite (fulltest.html) prima di entrare in release. */
const SUSPICIOUS_WORDS = new Set([
  // EN — credenziali / accesso
  "login", "signin", "sign", "secure", "security", "verify", "verified",
  "verification", "account", "accounts", "auth", "authentication",
  "authenticate", "otp", "password", "credential", "credentials",
  "recovery", "recover", "restore", "unlock", "access", "validate",
  "validation", "official", "id", "user", "users", "profile", "settings",
  // EN — supporto / comunicazioni
  "support", "help", "helpdesk", "service", "services", "customer",
  "alert", "alerts", "notice", "notification", "notifications", "urgent",
  "update", "manage", "member", "members", "mail", "email", "web",
  "portal", "online", "care",
  // EN — soldi / acquisti
  "billing", "payment", "payments", "pay", "wallet", "banking", "bank",
  "refund", "refunds", "invoice", "confirm", "confirmation", "store",
  "shop", "premium", "bonus", "gift", "gifts", "giftcard", "giftcards",
  "free", "promo", "promotion", "promotions", "offer", "offers", "deal",
  "deals", "prize", "prizes", "reward", "rewards", "win", "winner",
  "claim", "suspended", "locked", "blocked", "limited", "restricted",
  "renew", "renewal", "expired", "expire", "activation", "activate",
  // EN — spedizioni (smishing) e crypto
  "delivery", "shipping", "track", "tracking", "parcel", "package",
  "exchange", "airdrop", "staking", "swap", "nft", "token",
  // EN — geografia abusata nei sosia
  "italy", "italia", "italiane",
  // IT — accesso / sicurezza
  "assistenza", "accesso", "accedi", "entra", "conferma", "confermare",
  "verifica", "verificare", "sicurezza", "recupero", "recupera",
  "ripristino", "ripristina", "blocco", "sblocca", "sospeso", "scadenza",
  "scaduto", "attiva", "attivazione", "aggiorna", "aggiornamento",
  "gestione", "profilo", "impostazioni", "utente", "utenti", "area",
  // IT — soldi / acquisti
  "clienti", "cliente", "pagamento", "pagamenti", "carta", "conto",
  "banca", "fattura", "rimborso", "premio", "premi", "vinci", "vincita",
  "regalo", "regali", "buono", "gratis", "offerta", "offerte",
  "promozione", "promozioni", "sconto", "sconti",
  // IT — spedizioni / avvisi
  "spedizione", "consegna", "pacco", "ordine", "ordini", "supporto",
  "urgente", "avviso", "notifica", "notifiche"
]);

/* TLD economici/gratuiti usati in modo sproporzionato per il phishing.
   Un brand noto su uno di questi TLD è quasi sempre un falso. */
const SUSPICIOUS_TLDS = new Set([
  "tk", "ml", "ga", "cf", "gq", "top", "xyz", "icu", "club", "click",
  "link", "cam", "monster", "cyou", "bid", "loan", "work", "men", "date",
  "faith", "review", "racing", "download", "stream", "zip", "mov",
  "support", "cfd", "sbs", "rest", "quest", "surf",
  // ondata 2025-26 (report di abuso Spamhaus/Interisle)
  "bond", "lol", "pics", "mom", "skin", "hair", "beauty", "autos", "boats",
  "co", "cm", "om"   // typo classici di ".com" (paypal.co, paypal.cm)
]);

/* Piattaforme di hosting "user-content": chiunque puo' creare un sotto-dominio
   gratuito (es. "secure-paypal.vercel.app"). Sono nella sezione PRIVATE della
   PSL, che NON includiamo, quindi qui il dominio registrabile coincide con la
   piattaforma. Vettore di phishing molto comune. */
const HOSTING_PLATFORMS = new Set([
  "github.io", "gitlab.io", "pages.dev", "workers.dev", "web.app",
  "firebaseapp.com", "vercel.app", "netlify.app", "glitch.me", "repl.co",
  "replit.app", "weebly.com", "wixsite.com", "000webhostapp.com",
  "blogspot.com", "webflow.io", "surge.sh", "onrender.com", "herokuapp.com",
  "azurewebsites.net", "translate.goog", "r2.dev", "neocities.org",
  "github.dev", "vercel.dev", "appspot.com",
  // ampliamento 2026: site-builder e piattaforme molto abusate di recente
  "notion.site", "carrd.co", "godaddysites.com", "square.site",
  "mystrikingly.com", "jimdosite.com", "railway.app", "fly.dev",
  "deno.dev", "koyeb.app",
  // tenant/bucket con nome scelto dall'attaccante (paypal-verify.sharepoint.com,
  // paypal-secure.blob.core.windows.net); il gating forte evita i FP sui
  // tenant legittimi (microsoft.sharepoint.com resta pulito)
  "sharepoint.com", "windows.net"
]);

/* Range Unicode degli alfabeti non-latini piu' usati negli attacchi IDN.
   Il cherokee ha molte sillabe identiche alle maiuscole latine (UTS #39):
   qui basta il range — un'etichetta latino+cherokee e' sempre un omografo.
   Le mappe skeleton per il cherokee "puro" restano fuori di proposito
   (richiederebbero la tabella UTS completa, rischio di voci sbagliate). */
function isConfusableScript(cp) {
  return (cp >= 0x0370 && cp <= 0x03ff) ||   // greco
         (cp >= 0x0400 && cp <= 0x052f) ||   // cirillico (+ supplemento)
         (cp >= 0x0530 && cp <= 0x058f) ||   // armeno
         (cp >= 0x13a0 && cp <= 0x13ff) ||   // cherokee (blocco storico)
         (cp >= 0xab70 && cp <= 0xabbf) ||   // cherokee minuscole (IDNA)
         (cp >= 0x1f00 && cp <= 0x1fff);     // greco esteso
}

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

/* Vero se la stringa ha la forma di un dominio: etichette alfanumeriche
   (trattini solo interni, niente etichette vuote) + TLD alfabetico finale.
   Unica validazione condivisa da background (addOfficial), popup e onboarding. */
function looksLikeDomain(v) {
  return /^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/.test(v)
      && /\.[a-z]{2,}$/.test(v);
}

/* Quante etichette finali dell'hostname compongono il suffisso pubblico,
   secondo l'algoritmo ufficiale della Public Suffix List:
   - vince la regola con piu' etichette;
   - le wildcard (*.ck) coprono un'etichetta in piu';
   - le eccezioni (!www.ck) prevalgono su tutto e ne tolgono una. */
function publicSuffixLen(labels) {
  let best = 1; // regola implicita "*": l'ultima etichetta e' il suffisso
  for (let i = 0; i < labels.length; i++) {
    const cand = labels.slice(i).join(".");
    if (PSL_EXCEPTIONS.has(cand)) return (labels.length - i) - 1;
    if (PSL_RULES.has(cand)) best = Math.max(best, labels.length - i);
    if (i + 1 < labels.length && PSL_WILDCARDS.has(labels.slice(i + 1).join("."))) {
      best = Math.max(best, labels.length - i);
    }
  }
  return best;
}

/* Estrae il dominio registrabile da un hostname usando la Public Suffix List
   completa (lib/psl.js): "login.absa.co.za" -> "absa.co.za",
   "tizio.gov.it" -> "tizio.gov.it", "www.poste.it" -> "poste.it".
   Se psl.js non e' caricato, fallback alla vecchia lista ridotta. */
function registrableDomain(hostname) {
  const parts = hostname.toLowerCase().replace(/\.$/, "").replace(/^www\./, "").split(".").filter(Boolean);
  if (parts.length <= 1) return parts.join(".");
  if (typeof PSL_RULES === "undefined") {
    if (parts.length === 2) return parts.join(".");
    const lastTwo = parts.slice(-2).join(".");
    if (MULTI_PART_TLDS.has(lastTwo)) return parts.slice(-3).join(".");
    return lastTwo;
  }
  const ps = publicSuffixLen(parts);
  if (ps >= parts.length) return parts.join("."); // l'hostname E' un suffisso pubblico
  return parts.slice(parts.length - ps - 1).join(".");
}

/* =============================================================
   OMOGRAFI UNICODE (attacchi IDN, es. la "a" cirillica al posto della "a")
   ============================================================= */

/* Caratteri non-latini che assomigliano (spesso in modo identico) a lettere
   ASCII. Sottoinsieme curato di Unicode UTS #39 limitato alle minuscole degli
   script realmente usati negli attacchi IDN (cirillico, greco, armeno, latino
   esteso). La sostituzione e' innocua per i domini internazionali legittimi:
   scatta solo se lo "scheletro" risultante coincide con un dominio protetto. */
const CONFUSABLES = {
  // Cirillico
  "а": "a", "е": "e", "о": "o", "р": "p", "с": "c",
  "у": "y", "х": "x", "ѕ": "s", "і": "i", "ј": "j",
  "ԁ": "d", "һ": "h", "ӏ": "l", "к": "k", "м": "m",
  "в": "b", "ԛ": "q", "ԝ": "w", "ѡ": "w", "ѵ": "v",
  "ү": "y", "ҫ": "c", "ҽ": "e",
  // Greco
  "ο": "o", "α": "a", "ρ": "p", "ε": "e", "ι": "i",
  "ν": "v", "κ": "k", "τ": "t", "υ": "u", "χ": "x",
  "ϲ": "c", "ω": "w", "γ": "y", "η": "n", "μ": "u",
  "ς": "s",
  // Armeno
  "ո": "n", "ս": "u", "օ": "o", "հ": "h",
  // Latino esteso / IPA
  "ı": "i", "ȷ": "j", "ɑ": "a", "ɡ": "g", "ɩ": "i"
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

/* Lettere latine "speciali" che NON si decompongono con NFKD ma imitano
   una lettera base (o una coppia). Complemento della piega qui sotto. */
const FOLD_EXTRA = {
  "ø": "o", "đ": "d", "ð": "d", "ł": "l", "ħ": "h", "ŧ": "t",
  "æ": "ae", "œ": "oe", "ß": "ss"
};

/* Piega i diacritici latini: "pàypal" -> "paypal", "gøogle" -> "google".
   NFKD separa lettera e accento, poi togliamo i segni combinanti (U+0300-036F)
   e applichiamo la mappa manuale. ATTENZIONE: il risultato va confrontato coi
   protetti SOLO per uguaglianza esatta, MAI per distanza — altrimenti torna il
   falso positivo mañana.com ~ asana.com che abbiamo gia' eliminato. */
function foldDiacritics(s) {
  let out = "";
  for (const ch of s.normalize("NFKD")) {
    const cp = ch.codePointAt(0);
    if (cp >= 0x0300 && cp <= 0x036f) continue;   // segno combinante: via
    out += (FOLD_EXTRA[ch] || ch);
  }
  return out;
}

/* Vero se UNA etichetta dell'hostname mescola lettere latine ASCII e lettere
   di un alfabeto confondibile (cirillico/greco/armeno): praticamente sempre
   un omografo malevolo, a prescindere dal brand. I registrar IDN seri vietano
   gia' i mix di script proprio per questo, quindi i falsi positivi sono rarissimi. */
function hasMixedScript(hostname) {
  for (const label of idnToUnicode(hostname.toLowerCase()).split(".")) {
    let latin = false, other = false;
    for (const ch of label) {
      if (ch >= "a" && ch <= "z") latin = true;
      else if (isConfusableScript(ch.codePointAt(0))) other = true;
    }
    if (latin && other) return true;
  }
  return false;
}
