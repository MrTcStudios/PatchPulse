/* ============================================================
   PatchPulse — match.js
   Funzioni condivise per il riconoscimento dei domini "sosia".
   Questo file viene incluso SIA nel background SIA nel popup,
   cosi' la logica di pulizia/confronto dei domini e' una sola.
   ============================================================ */

/* Domini ufficiali predefiniti. L'utente puo' aggiungerne altri dal popup;
   questi vengono usati solo al primo avvio per riempire la lista. */
const DEFAULT_DOMAINS = [
  // Big tech / servizi globali
  "google.com", "microsoft.com", "apple.com", "amazon.com",
  "paypal.com", "facebook.com", "instagram.com", "netflix.com",
  "github.com", "linkedin.com", "whatsapp.com", "booking.com",
  // Italia
  "poste.it", "intesasanpaolo.com", "unicredit.it",
  "agenziaentrate.gov.it", "inps.it", "spid.gov.it"
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
  "gitlab.com",   // simile a github.com
  "doodle.com",   // simile a google.com
  "paypay.com"    // PayPay (Giappone), simile a paypal.com
]);

/* Normalizza gli "omoglifi" ASCII: caratteri diversi che SEMBRANO uguali.
   Cosi' "rnicrosoft" diventa "microsoft" e "paypa1" diventa "paypal". */
function normalize(domain) {
  return domain
    .toLowerCase()
    .replace(/rn/g, "m")   // r+n -> m
    .replace(/vv/g, "w")   // v+v -> w
    .replace(/0/g, "o")    // zero -> o
    .replace(/1/g, "l")    // uno  -> l
    .replace(/5/g, "s")    // cinque -> s
    .replace(/\$/g, "s");
}

/* Distanza di Levenshtein: quante modifiche minime (aggiungi/togli/cambia
   una lettera) servono per trasformare "a" in "b". */
function levenshtein(a, b) {
  const m = a.length, n = b.length;
  if (m === 0) return n;
  if (n === 0) return m;
  let prev = Array.from({ length: n + 1 }, (_, j) => j);
  let curr = new Array(n + 1);
  for (let i = 1; i <= m; i++) {
    curr[0] = i;
    for (let j = 1; j <= n; j++) {
      const cost = a[i - 1] === b[j - 1] ? 0 : 1;
      curr[j] = Math.min(prev[j] + 1, curr[j - 1] + 1, prev[j - 1] + cost);
    }
    const tmp = prev; prev = curr; curr = tmp;
  }
  return prev[n];
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
