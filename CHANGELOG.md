# Changelog

## 1.6.1 — 2026-07-08
- Fix: rows hidden via the `hidden` attribute could remain visible as empty boxes ("real address" on every warning, "looks like" on mixed-script warnings) and the new popup filter did not actually hide non-matching entries — page CSS `display` rules were overriding the attribute. A global `[hidden] { display: none !important; }` rule now wins on every page, and the test suite checks computed visibility instead of the DOM property.

## 1.6.0 — 2026-07-08
- **Settings page** (`options_ui`): pause protection, personal **trusted sites** list, disable/clear the locally stored threat history and counter, restore the default protected list (two-step confirm).
- **"I trust this site"** on the warning now adds to the trusted list — the site simply won't be flagged again — instead of granting it brand protections in the protected list.
- Warning page polish: IDN look-alikes shown in readable form with the actual punycode address on its own row; **copy details** button (for users without a mail client); default focus on "Go back" so Enter is the safe choice; demo mode.
- Popup: **filter** for the domain list, "yours" tag on user-added domains, settings shortcut. Toolbar icon shows a **blocked-threats badge**.
- Onboarding: "see a sample warning" button (opens the warning in demo mode, no real navigation).
- **False-positive sweep** over the Tranco top-10k real-world domains (`tools/fp-sweep.html`): 95 legitimate popular sites added to the built-in green-list (opera.com, box.com, intel.com, chess.com, tmall.com, telegra.ph, onmicrosoft.com, adobelogin.com, wal-mart.com, apple.co, google.ml…). Structural fix: a brand's own two-level national domains (google.com.co, google.com.om) are no longer flagged.
- Public Suffix List refreshed (snapshot 2026-07-08, 6910 rules); generator committed as `tools/generate-psl.ps1`.
- Test suite: 582 assertions, 0 failures.

## 1.5.0 — 2026-07-07
- New detection: one-letter brand typos on a **different TLD** for longer brand names (`steamcommunlty.ru`, `facebok.ru`), with strict anti-FP guards (protected/green-list brand variants and common-word brands are never flagged this way).
- Brands of 3 letters or less are excluded from edit-distance matching (kills `tin.it`/`ski.it`/`up.com`-style false positives) while keeping exact/homoglyph/combo/TLD protections.
- Cherokee Unicode ranges added to the mixed-script homograph check.
- 12 new built-in domains (185 total): DocuSign, OpenAI, ChatGPT, OneDrive, UPS, BRT, SDA, InPost, Enel, Eni, TIM, Sky. Green-list: discovery.com, uspa.com, telegraf.rs.
- Abuse-prone TLD list +9 (.bond, .lol, .pics, .mom, .skin, .hair, .beauty, .autos, .boats); hosting-platform list +12 (notion.site, carrd.co, sharepoint.com tenants, Azure blob hostnames, railway.app, fly.dev…).

## 1.4.1 — 2026-07-07
- Fixed a race on event-page wake-up: the navigation that wakes the extension is now always checked.
- Built-in domains removed by the user no longer come back after a list update.
- New detection: accented-Latin homographs (`pàypal.com`, `gøogle.com`) via NFKD folding, exact-match only; hyphenated brand disguises on foreign TLDs (`pay-pal.de`).
- "Continue anyway" now expires after 15 minutes instead of lasting the whole session.
- Shared stricter domain validation everywhere; 5000-domain cap enforced in popup/onboarding too; message toast timer fix.

## 1.4.0 — 2026-06-12
- Fixed combo-squatting missing homoglyph-disguised brands (`paypa1-login.com`).
- New detections: brand pages on free hosting platforms (`secure-paypal.vercel.app`) with strong anti-FP gating; mixed-script labels (Latin + Cyrillic/Greek/Armenian) flagged for any brand.
- Server-side redirects covered via `onCommitted` listener with dedup.
- Accented legitimate IDNs (mañana.com) no longer distance-compared with ASCII brands.

## 1.3.0 — 2026-06-12
- Full Public Suffix List (ICANN section) embedded: correct registrable domains on multi-level endings (`.co.za`, `.com.tr`) for detection and for user-added sites.
- Brand homoglyphs caught on any TLD (`paypa1.co.za`, `g00gle.de`).
- Expanded UTS #39 confusables (~50 entries: Cyrillic, Greek, Armenian, extended Latin).
- Welcome page on first install; recent blocked threats shown in the popup.

## 1.2.0 — 2026-06-11
- One-click false-alarm reporting (pre-filled email, nothing sent automatically).
- Phishing vocabulary expanded to ~140 English + Italian words.
- Deceptive letters highlighted in the warning comparison.
- Brand-as-hyphen-token detection on abuse-prone TLDs (`fortnite-skins.tk`).

## 1.1.0 / 1.1.1 / 1.1.2 — 2026-06-10/11
- Default list grown to 172 domains; combo-squatting, brand-in-subdomain and TLD-abuse detections; Damerau (OSA) distance; green-list; parity guaranteed between built-in and user-added domains.

## 1.0.0 — 2026-06-07
- First release: typo/homoglyph detection against a curated list, warning page, session bypass, protected-domains popup, GPLv3.
