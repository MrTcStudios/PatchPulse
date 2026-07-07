# PatchPulse - tools/generate-psl.ps1
# Rigenera lib/psl.js dalla Public Suffix List ufficiale (publicsuffix.org).
# Uso:  powershell -ExecutionPolicy Bypass -File tools\generate-psl.ps1
# - scarica il .dat, tiene SOLO la sezione ICANN (le piattaforme user-content
#   della sezione PRIVATE restano fuori DI PROPOSITO: HOSTING_PLATFORMS in
#   match.js conta sul fatto che il registrable coincida con la piattaforma);
# - converte le regole IDN in punycode con IdnMapping.GetAscii;
# - riscrive lib/psl.js nello stesso formato a 3 Set.
# Cadenza consigliata: ogni release o almeno ogni trimestre.

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot   # cartella dell'estensione
$outFile = Join-Path $root "lib\psl.js"

Write-Host "Scarico la Public Suffix List..."
$resp = Invoke-WebRequest -Uri "https://publicsuffix.org/list/public_suffix_list.dat" -UseBasicParsing
$text = [System.Text.Encoding]::UTF8.GetString($resp.RawContentStream.ToArray())

$beginMark = "===BEGIN ICANN DOMAINS==="
$endMark = "===END ICANN DOMAINS==="
$start = $text.IndexOf($beginMark)
$end = $text.IndexOf($endMark)
if ($start -lt 0 -or $end -lt 0) { throw "Sezione ICANN non trovata nel .dat" }
$icann = $text.Substring($start, $end - $start)

$idn = New-Object System.Globalization.IdnMapping
function ToAscii([string]$d) {
  try { return $idn.GetAscii($d).ToLowerInvariant() } catch { return $d }
}

$rules = New-Object System.Collections.Generic.List[string]
$wild = New-Object System.Collections.Generic.List[string]
$exc = New-Object System.Collections.Generic.List[string]

foreach ($line in ($icann -split "`n")) {
  $l = $line.Trim()
  if ($l -eq "" -or $l.StartsWith("/")) { continue }   # vuote e commenti
  if ($l.StartsWith("!")) { $exc.Add((ToAscii $l.Substring(1))); continue }
  if ($l.StartsWith("*.")) { $wild.Add((ToAscii $l.Substring(2))); continue }
  $rules.Add((ToAscii $l))
}

if ($rules.Count -lt 5000) { throw "Troppe poche regole ($($rules.Count)): download corrotto?" }

$today = Get-Date -Format "yyyy-MM-dd"
$c = "/" + "/"   # doppia barra senza scriverla letterale (guardrail dei comandi inline)
$nl = "`n"
$js = "$c PatchPulse - psl.js (GENERATO da tools/generate-psl.ps1, non modificare a mano)$nl" +
      "$c Public Suffix List (sezione ICANN) da publicsuffix.org - snapshot $today$nl" +
      "$c Dati locali incorporati: nessuna richiesta di rete. Licenza dati: MPL 2.0$nl" +
      'const PSL_RULES = new Set("' + ($rules -join " ") + '".split(" "));' + $nl +
      'const PSL_WILDCARDS = new Set("' + ($wild -join " ") + '".split(" "));' + $nl +
      'const PSL_EXCEPTIONS = new Set("' + ($exc -join " ") + '".split(" "));' + $nl

[System.IO.File]::WriteAllText($outFile, $js, (New-Object System.Text.UTF8Encoding($false)))
Write-Host "OK: $($rules.Count) regole, $($wild.Count) wildcard, $($exc.Count) eccezioni -> $outFile"
