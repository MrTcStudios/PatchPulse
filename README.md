# PatchPulse — Anti-phishing extension for Firefox

Warns you **before** you open a website whose address is a look-alike of an official one — the trick behind most phishing attacks.

Unlike blocklists (which only know sites that have already been reported), PatchPulse recognises look-alikes **by similarity** — even brand-new ones — and works **entirely locally**: no browsing history ever leaves your browser. You can also add your own domains (your bank, the services you use) and protect those too, not just the big names.

Part of the [PatchPulse](https://patchpulse.org) project.

## What it catches
- **Typos / ASCII homoglyphs** — `rnicrosoft.com` (rn→m), `paypa1.com` (1→l), `gogle.com`, swapped letters (`googel.com`) — on any domain ending (`paypa1.co.za`, `g00gle.de`)
- **Hyphen tricks** — `pay-pal.com`, `pay-pal.de`, `paypal.com-secure.ru`
- **Unicode / IDN homographs** — Cyrillic, Greek, Armenian and extended-Latin letters that imitate Latin ones (curated UTS #39 subset, with `xn--` punycode decoding): `аpple.com`, `ıcloud.com` — plus accented-Latin spoofs like `pàypal.com` or `gøogle.com` (diacritic folding, exact match only so `mañana.com`-style legit domains never trigger)
- **An official domain used as a sub-domain** of another — `paypal.com.evil-login.ru`, `paypal.com.tk`
- **Brand as sub-domain with phishing words** — `paypal.secure-verify.ru`
- **Combo-squatting** — the brand plus phishing words in English and Italian (~140 words): `paypal-login.com`, `applesupport.com`, `amazon-verifica.com`, `dhl-tracking.com`
- **TLD abuse** — the brand name, exact or as a hyphen token, on an abuse-prone TLD: `paypal.tk`, `paypal.co`, `fortnite-skins.tk`

Domains are resolved with the full **Public Suffix List**, so all of the above works on multi-level endings (`.co.za`, `.com.tr`, `.co.uk`...) — both for the 173 built-in domains and for the ones you add yourself, which get the exact same protection.

## Installation
**Firefox** — <https://addons.mozilla.org/firefox/addon/patchpulse-anti-phishing/>

**From source (for development):**
1. open `about:debugging#/runtime/this-firefox`
2. *Load Temporary Add-on…*
3. select the `manifest.json` file in this folder

To see the warning, type a look-alike of a default domain in the address bar, e.g. `rnicrosoft.com`, `paypa1.com` or `gogle.com`.

## How it works
1. It runs in the background on every site you open.
2. If the address is a look-alike, it shows a warning comparing the fake domain with the official one, **highlighting the deceptive letters** and explaining why.
3. You decide: **go back**, **continue anyway** (temporarily — the pass expires after ~15 minutes), or **add it to your protected list** if you trust it. False alarms can be reported with one click — it opens a pre-filled email, nothing is ever sent automatically.

A welcome page on first install lets you add your own sites right away, and the popup shows your protected domains plus the **latest blocked threats** (with reason and date) and the extension version.

## Privacy
No data collected or transmitted; the extension makes no network requests. All comparison happens on your device; the protected-domains list and the locally stored blocked-threats history never leave the browser.

## Structure
```
patchpulse-extension/
├─ manifest.json        # configuration and permissions (MV3)
├─ background.js        # listens to navigation and triggers the warning
├─ lib/
│  ├─ match.js          # logic: homoglyphs, edit distance, punycode, green-list
│  ├─ psl.js            # Public Suffix List (ICANN) snapshot, generated
│  └─ i18n.js           # UI translations (automatic IT/EN)
├─ _locales/            # store name/description (en, it)
├─ popup/               # popup: protected domains + recently blocked threats
├─ warning/             # warning page (reason, letter diff, false-alarm report)
├─ onboarding/          # welcome page shown once on install
└─ icons/icon.svg
```

## Development
- **No build step:** it's all plain JavaScript/HTML/CSS, loaded as-is.
- UI strings live in `lib/i18n.js` (Italian if the browser is in Italian, English otherwise).
- To package for the store: zip the **contents** of the folder (with `manifest.json` at the root).

## Known limitations
- **False positives:** some legitimate sites that closely resemble an official one may get flagged. Known cases are in a *green-list*; for the rest, the "I trust this site" button and the "report a false alarm" link on the warning handle it.
- Domain extraction uses the full **Public Suffix List** (ICANN section, embedded locally in `lib/psl.js` — regenerate it periodically from publicsuffix.org).
- The Unicode confusables map is a curated UTS #39 subset covering the scripts actually used in IDN attacks (Cyrillic, Greek, Armenian, extended Latin).
- Built for Firefox (MV3 with background `scripts`); Chrome would need a `service_worker`.

## License
[GNU General Public License v3.0](LICENSE) — like the rest of the PatchPulse project.
