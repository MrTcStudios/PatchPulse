<?php
// lang/lang.php
// Secure i18n resolver. Whitelist-only; no dynamic file inclusion; no untrusted
// header parsing beyond what Cloudflare's edge writes (HTTP_CF_IPCOUNTRY).

if (defined('PP_LANG_LOADED')) {
    return;
}
define('PP_LANG_LOADED', 1);

// Strict whitelist. Anything outside this set is rejected.
const PP_LANG_SUPPORTED = ['it', 'en'];
const PP_LANG_DEFAULT   = 'en';
const PP_LANG_COOKIE    = 'pp_lang';
const PP_LANG_COOKIE_TTL = 31536000; // 1 year

define('PP_LANG_INTERNAL', 1);
$PP_TRANSLATIONS = require __DIR__ . '/translations.php';

function pp_lang_is_valid($code): bool {
    return is_string($code) && in_array($code, PP_LANG_SUPPORTED, true);
}

// Determines the default language from Cloudflare's IP geolocation header.
// CF-IPCountry is set/overwritten by Cloudflare's edge and cannot be spoofed
// when the origin is only reachable through Cloudflare. We still validate the
// format (ISO 3166-1 alpha-2, uppercase) before trusting it.
function pp_lang_from_geo(): string {
    $cc = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if (!is_string($cc) || strlen($cc) !== 2 || !ctype_upper($cc)) {
        return PP_LANG_DEFAULT;
    }
    return $cc === 'IT' ? 'it' : 'en';
}

// Resolves the active language with priority:
//   1. session (set by settings.php on explicit user choice)
//   2. cookie (persisted explicit choice across browser sessions)
//   3. geo via CF-IPCountry
//   4. hardcoded default
// Every step is whitelist-validated; invalid values are silently discarded.
function pp_lang_resolve(): string {
    if (isset($_SESSION['lang']) && pp_lang_is_valid($_SESSION['lang'])) {
        return $_SESSION['lang'];
    }

    $cookie = $_COOKIE[PP_LANG_COOKIE] ?? null;
    if (pp_lang_is_valid($cookie)) {
        $_SESSION['lang'] = $cookie;
        return $cookie;
    }

    $geo = pp_lang_from_geo();
    $_SESSION['lang'] = $geo;
    return $geo;
}

// Persists an explicit user choice. Caller MUST validate $code first.
function pp_lang_persist(string $code): void {
    if (!pp_lang_is_valid($code)) {
        return;
    }
    $_SESSION['lang'] = $code;
    setcookie(PP_LANG_COOKIE, $code, [
        'expires'  => time() + PP_LANG_COOKIE_TTL,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

// Translation lookup. Falls back to the English value, then to the key itself
// (never to user input). $escape=true returns HTML-escaped output suitable for
// direct echo into HTML body/attribute contexts.
function t(string $key, bool $escape = true): string {
    global $PP_TRANSLATIONS;
    $lang = pp_lang_resolve();
    $value = $PP_TRANSLATIONS[$lang][$key]
        ?? $PP_TRANSLATIONS[PP_LANG_DEFAULT][$key]
        ?? $key;
    return $escape ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
}

function pp_lang_current(): string {
    return pp_lang_resolve();
}

// Emits a <script> tag that defines window.PP_I18N as a frozen JS object with
// only the `js.*` keys for the current language. Output is JSON, which is a
// strict subset of JS literal syntax — no template strings, no script-tag
// injection risk. We escape only HTML-meaningful chars inside the JSON payload
// for the closing </script> edge case.
function pp_lang_emit_js(): void {
    global $PP_TRANSLATIONS;
    $lang = pp_lang_resolve();
    $bundle = $PP_TRANSLATIONS[$lang] ?? $PP_TRANSLATIONS[PP_LANG_DEFAULT];

    $jsKeys = [];
    foreach ($bundle as $k => $v) {
        if (strncmp($k, 'js.', 3) === 0) {
            $jsKeys[$k] = $v;
        }
    }

    // Also expose the active language code so JS can read it without parsing
    // the cookie (matches what the server already resolved).
    $payload = [
        'lang' => $lang,
        'strings' => (object) $jsKeys, // (object) so {} stays an object even if empty
    ];

    // JSON_HEX_TAG/AMP/APOS/QUOT keeps the output safe inside <script>.
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    if ($json === false) {
        $json = '{"lang":"' . PP_LANG_DEFAULT . '","strings":{}}';
    }

    echo "<script>window.PP_I18N=Object.freeze(" . $json . ");</script>\n";
}
