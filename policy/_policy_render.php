<?php
// policy/_policy_render.php
// Shared helper: renders a policy data structure as safe HTML.
//
// Markup convention in the data file:
//   {strong}...{/strong}        → <strong>...</strong>
//   {a:href}label{/a}           → <a href="href">label</a>
//
// We HTML-escape the raw text first, then convert only the whitelisted markers.
// This means any literal '<' or '&' in the source becomes its entity, and only
// {strong}/{a:...} sequences ever produce live HTML. href is restricted to a
// safe-URL pattern (no javascript:, no data:, etc.).

if (!defined('PP_POLICY_RENDER_LOADED')) {
    define('PP_POLICY_RENDER_LOADED', 1);

    function pp_policy_render(string $text): string {
        // Escape first — defense in depth even though source is hard-coded.
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // {strong}...{/strong}  (after escaping the braces survive untouched)
        $escaped = preg_replace('/\{strong\}(.*?)\{\/strong\}/s', '<strong>$1</strong>', $escaped);

        // {a:href}label{/a} — href must be a safe URL (relative path, mailto:, https://).
        $escaped = preg_replace_callback(
            '/\{a:([^}]+)\}(.*?)\{\/a\}/s',
            function ($m) {
                $href = $m[1];
                // Whitelist of allowed URL forms.
                $ok = preg_match('#^(?:[a-zA-Z0-9._/\-?#=&]+|mailto:[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+|https?://[^"<>\s]+)$#', $href);
                if (!$ok) {
                    return $m[2]; // strip the link, keep the text
                }
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . $m[2] . '</a>';
            },
            $escaped
        );

        return $escaped;
    }

    function pp_policy_render_blocks(array $blocks): void {
        foreach ($blocks as $b) {
            $type = $b['type'] ?? 'p';
            switch ($type) {
                case 'h2':
                    echo '<h2>' . htmlspecialchars($b['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</h2>';
                    break;
                case 'h3':
                    echo '<h3>' . htmlspecialchars($b['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</h3>';
                    break;
                case 'p':
                    echo '<p>' . pp_policy_render($b['text'] ?? '') . '</p>';
                    break;
                case 'ul':
                    echo '<ul>';
                    foreach (($b['items'] ?? []) as $item) {
                        echo '<li>' . pp_policy_render((string) $item) . '</li>';
                    }
                    echo '</ul>';
                    break;
                case 'note':
                    echo '<div class="note">' . pp_policy_render($b['text'] ?? '') . '</div>';
                    break;
                // Unknown types are silently dropped (defense against future schema drift).
            }
        }
    }
}
