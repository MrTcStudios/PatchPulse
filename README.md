# PatchPulse — Anti-phishing extension for Firefox

Warns you **before** you open a website whose address is a look-alike of an official one — the trick behind most phishing attacks.

Unlike blocklists (which only know sites that have already been reported), PatchPulse recognises look-alikes **by similarity** — even brand-new ones — and works **entirely locally**: no browsing history ever leaves your browser. You can also add your own domains (your bank, the services you use) and protect those too, not just the big names.

Part of the [PatchPulse](https://patchpulse.org) project.

## What it catches
- **Typos / ASCII homoglyphs** — `rnicrosoft.com` (rn→m), `paypa1.com` (1→l), `gogle.com`, swapped letters (`googel.com`)
- **Hyphen tricks** — `pay-pal.com`, `paypal.com-secure.ru`
- **Unicode / IDN homographs** — Cyrillic or Greek letters that imitate Latin ones, with `xn--` punycode decoding
- **An official domain used as a sub-domain** of another — `paypal.com.evil-login.ru`, `paypal.com.tk`
- **Brand as sub-domain with phishing words** — `paypal.secure-verify.ru`
- **Combo-squatting** — `paypal-login.com`, `applesupport.com`, `secure-paypal.com`
- **TLD abuse** — the exact brand name on an abuse-prone TLD: `paypal.tk`, `microsoft.top`

## Installation
**Firefox** — *coming soon on addons.mozilla.org (link will be added once published).*

**From source (for development):**
1. open `about:debugging#/runtime/this-firefox`
2. *Load Temporary Add-on…*
3. select the `manifest.json` file in this folder

To see the warning, type a look-alike of a default domain in the address bar, e.g. `rnicrosoft.com`, `paypa1.com` or `gogle.com`.

## How it works
1. It runs in the background on every site you open.
2. If the address is a look-alike, it shows a warning comparing the fake domain with the official one and explaining why.
3. You decide: **go back**, **continue anyway** (for this session only), or **add it to your protected list** if you trust it.

## Privacy
No data collected or transmitted. All comparison happens on your device; the list of protected domains and the blocked-threats counter stay in the browser's local storage.

## Structure
```
patchpulse-extension/
├─ manifest.json        # configuration and permissions (MV3)
├─ background.js        # listens to navigation and triggers the warning
├─ lib/
│  ├─ match.js          # logic: homoglyphs, edit distance, punycode, green-list
│  └─ i18n.js           # UI translations (automatic IT/EN)
├─ _locales/            # store name/description (en, it)
├─ popup/               # popup to manage protected domains
├─ warning/             # warning page (also shows the reason)
└─ icons/icon.svg
```

## Development
- **No build step:** it's all plain JavaScript/HTML/CSS, loaded as-is.
- UI strings live in `lib/i18n.js` (Italian if the browser is in Italian, English otherwise).
- To package for the store: zip the **contents** of the folder (with `manifest.json` at the root).

## Known limitations
- **False positives:** some legitimate sites that closely resemble an official one may get flagged. Known cases (e.g. `gitlab.com` vs `github.com`) are in a *green-list*; for the rest, the "I trust this site" button on the warning handles it. Domain extraction is simplified (it doesn't use the full Public Suffix List).
- The Unicode confusables map is curated but not exhaustive.
- Built for Firefox (MV3 with background `scripts`); Chrome would need a `service_worker`.

## License
[GNU General Public License v3.0](LICENSE) — like the rest of the PatchPulse project.
