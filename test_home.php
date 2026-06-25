<?php
/**
 * test_home.php — STYLE TEST ONLY (full homepage)
 * -----------------------------------------------
 * Intera homepage PatchPulse nel concept "dark hero / glassmorphism".
 * File standalone in HTML/CSS/JS vanilla: NON tocca home.php (sidebar concept).
 * Adattamenti rispetto allo spec React originale:
 *   - Tailwind via Play CDN (stesse utility class)
 *   - hls.js / Lucide / Google Fonts da CDN
 *   - framer-motion -> keyframes CSS + IntersectionObserver (reveal on scroll)
 *   - AnimatePresence + typewriter -> JS vanilla
 *   - background UNICO generato in canvas: mesh/rete di nodi + impulso "sonar"
 *     di scansione (tema monitoraggio rete). Nessun video esterno / nessun hls.js.
 * Testi: copia EN reale presa da lang/translations.php (hardcoded, file standalone).
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse — Free security scanners</title>

    <!-- Fonts: Inter (base) + Instrument Serif (headings) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <!-- Tailwind (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: { accent: '#8b7cf8', accent2: '#b5a8ff' },
                },
            },
        };
    </script>

    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --background: #000000;
            --foreground: #ffffff;
            --accent: #8b7cf8;
            --accent-2: #b5a8ff;
        }

        * { scroll-margin-top: 96px; }
        html { scroll-behavior: smooth; }
        body {
            background-color: var(--background);
            color: var(--foreground);
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
            margin: 0;
        }
        ::selection { background: #ffffff; color: #000000; }
        .serif { font-family: 'Instrument Serif', serif; }

        /* ───────── Animated background: network mesh + sonar scan (canvas) ───────── */
        .bg-ambient {
            position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none;
            background:
                radial-gradient(70% 55% at 50% -5%, rgba(139,124,248,0.12), transparent 70%),
                #000000;
        }
        #bg-canvas { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
        .orb { position: absolute; border-radius: 9999px; filter: blur(100px); }
        .orb-1 { width: 46vw; height: 46vw; left: -10vw; top: -8vw;    background: #7c5cff; opacity: 0.16; animation: drift1 24s ease-in-out infinite; }
        .orb-2 { width: 42vw; height: 42vw; right: -8vw; bottom: -14vw; background: #4f46e5; opacity: 0.12; animation: drift2 30s ease-in-out infinite; }
        @keyframes drift1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(40px,34px); } }
        @keyframes drift2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-46px,40px); } }

        /* ───────── Glassmorphism ───────── */
        .liquid-glass {
            background: rgba(255, 255, 255, 0.01);
            background-blend-mode: luminosity;
            -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px);
            border: none;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
            position: relative; overflow: hidden;
        }
        .liquid-glass::before {
            content: ""; position: absolute; inset: 0; border-radius: inherit; padding: 1.4px;
            background: linear-gradient(180deg,
                rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.15) 20%, rgba(255,255,255,0) 40%,
                rgba(255,255,255,0) 60%, rgba(255,255,255,0.15) 80%, rgba(255,255,255,0.45) 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
        }
        .glass {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.08);
            -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
        }
        .glass-hover { transition: transform .3s cubic-bezier(.16,1,.3,1), border-color .3s, box-shadow .3s, background .3s; }
        .glass-hover:hover {
            transform: translateY(-4px);
            border-color: rgba(139,124,248,0.45);
            box-shadow: 0 16px 50px rgba(124,92,255,0.18);
            background: rgba(255,255,255,0.05);
        }

        /* ───────── Entrance + reveal animations ───────── */
        @keyframes navIn   { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: none; } }
        @keyframes fadeUp  { from { opacity: 0; transform: translateY(10px);  } to { opacity: 1; transform: none; } }
        @keyframes fadeUpL { from { opacity: 0; transform: translateY(20px);  } to { opacity: 1; transform: none; } }
        @keyframes swapIn  { from { opacity: 0; transform: scale(0.95);       } to { opacity: 1; transform: scale(1); } }
        @keyframes bob     { 0%,100% { transform: translateY(0); } 50% { transform: translateY(7px); } }
        .anim-nav     { animation: navIn   0.6s ease both; }
        .anim-tagline { animation: fadeUp  0.6s ease both;  animation-delay: 0.1s; }
        .anim-heading { animation: fadeUpL 1s   cubic-bezier(0.16, 1, 0.3, 1) both; }
        .anim-cta     { animation: fadeUp  0.6s ease both;  animation-delay: 0.4s; }
        .anim-demo    { animation: fadeUp  0.6s ease both;  animation-delay: 0.8s; }
        .swap-in      { animation: swapIn  0.2s ease both; }
        .scroll-cue   { animation: fadeUp 0.6s ease both 1.1s, bob 2.4s ease-in-out infinite 1.7s; }

        .reveal { opacity: 0; transform: translateY(26px);
                  transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.in { opacity: 1; transform: none; }

        /* ───────── FAQ accordion ───────── */
        .faq-a { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .35s ease; }
        .faq-item.open .faq-a { grid-template-rows: 1fr; }
        .faq-a > div { overflow: hidden; }
        .faq-chevron { transition: transform .3s ease; }
        .faq-item.open .faq-chevron { transform: rotate(180deg); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; }
            .reveal { opacity: 1; transform: none; }
        }
    </style>
    <noscript><style>.reveal{opacity:1;transform:none;}</style></noscript>
</head>
<body>

    <!-- ══════════ ANIMATED BACKGROUND ══════════ -->
    <div class="bg-ambient" aria-hidden="true">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <canvas id="bg-canvas"></canvas>
    </div>

    <!-- ══════════ NAVBAR (fixed glass) ══════════ -->
    <nav class="anim-nav fixed top-0 left-0 right-0 z-50 px-4 sm:px-6 pt-5">
        <div class="liquid-glass rounded-full px-5 sm:px-6 py-3 flex items-center justify-between max-w-5xl mx-auto">
            <div class="flex items-center gap-8">
                <a href="#top" class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                    <span class="text-white font-semibold text-lg">PatchPulse</span>
                </a>
                <div class="hidden md:flex items-center gap-8 text-white/80 text-sm font-medium">
                    <a href="#scanners"  class="hover:text-white transition-colors duration-300">Scanners</a>
                    <a href="#extension" class="hover:text-white transition-colors duration-300">Extension</a>
                    <a href="#faq"       class="hover:text-white transition-colors duration-300">FAQ</a>
                    <a href="#about"     class="hover:text-white transition-colors duration-300">About</a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="log-reg.php#register" class="text-white hover:text-white/80 transition-colors text-sm font-medium cursor-pointer">Sign Up</a>
                <a href="log-reg.php#login" class="liquid-glass rounded-full px-6 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity cursor-pointer">Login</a>
            </div>
        </div>
    </nav>

    <main class="relative z-10">

        <!-- ══════════ HERO ══════════ -->
        <section id="top" class="relative min-h-screen flex flex-col items-center justify-center px-6 overflow-hidden">
            <!-- hero focus glow (the network mesh comes from the global canvas behind) -->
            <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(55% 55% at 50% 42%, rgba(139,124,248,0.10), transparent 70%);"></div>

            <div class="relative z-10 text-center max-w-5xl mx-auto flex flex-col items-center justify-center w-full gap-10 pt-24">
                <div>
                    <p class="anim-tagline text-white/80 text-[10px] md:text-[11px] font-medium tracking-[0.2em] uppercase mb-4">
                        Free Cybersecurity Tools — No Account Needed
                    </p>
                    <h1 class="anim-heading serif text-4xl md:text-[64px] font-medium tracking-[-0.01em] leading-[1.08] mb-6 bg-gradient-to-b from-white via-white/95 to-white/70 bg-clip-text text-transparent max-w-4xl">
                        A simpler way to find<br class="hidden md:block" /> and fix what's exposed
                    </h1>
                    <p class="anim-cta text-white/55 text-sm md:text-base max-w-xl mx-auto leading-relaxed">
                        Scan websites, check your VPN and monitor data breaches — professional scanners, completely free.
                    </p>
                </div>

                <!-- CTA area -->
                <div class="anim-cta min-h-[50px] flex items-center justify-center w-full">
                    <button id="cta-button" type="button"
                            class="swap-in px-10 py-3 text-[14px] font-medium border border-white/10 rounded-full hover:border-white/30 hover:bg-white/[0.02] transition-all duration-300 text-white/90 backdrop-blur-sm cursor-pointer">
                        Get early access
                    </button>
                    <form id="cta-form"
                          class="hidden items-center gap-2 pl-5 pr-1.5 py-1.5 text-[14px] font-medium border border-white/20 rounded-full bg-white/[0.02] backdrop-blur-sm w-full max-w-[320px] focus-within:border-white/40 transition-colors duration-300">
                        <input id="cta-email" type="email" required autocomplete="email"
                               class="flex-1 min-w-0 bg-transparent text-white placeholder-white/45 outline-none border-none" />
                        <button type="submit"
                                class="shrink-0 p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white cursor-pointer flex items-center justify-center">
                            <span id="icon-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
                            <span id="icon-check" class="hidden"><i data-lucide="check" class="w-4 h-4"></i></span>
                        </button>
                    </form>
                </div>

                <div class="anim-demo">
                    <a href="VulnerabilityScanner.php" target="_blank" class="text-white/80 hover:text-white/40 transition-colors duration-300 text-[13px] font-medium tracking-wide">
                        Start scanning now
                    </a>
                </div>
            </div>

            <a href="#scanners" class="scroll-cue absolute bottom-8 left-1/2 -translate-x-1/2 text-white/40 hover:text-white/70 transition-colors" aria-label="Scroll down">
                <i data-lucide="chevron-down" class="w-6 h-6"></i>
            </a>
        </section>

        <!-- ══════════ SCANNERS ══════════ -->
        <section id="scanners" class="px-6 py-24 md:py-32 max-w-6xl mx-auto">
            <div class="reveal text-center mb-14">
                <p class="text-accent2 text-xs font-medium tracking-[0.2em] uppercase mb-3">What you can do</p>
                <h2 class="serif text-4xl md:text-5xl text-white mb-4">Tools</h2>
                <p class="text-white/55 text-base max-w-xl mx-auto">Everything free. No account required to get started.</p>
            </div>

            <!-- Featured -->
            <a href="VulnerabilityScanner.php" target="_blank"
               class="reveal glass glass-hover group relative block rounded-3xl p-8 md:p-10 mb-5 overflow-hidden">
                <div class="absolute -right-10 -top-10 w-56 h-56 rounded-full blur-3xl pointer-events-none" style="background: rgba(139,124,248,0.18);"></div>
                <div class="relative flex flex-col md:flex-row items-start md:items-center gap-7">
                    <div class="shrink-0 w-24 h-24 rounded-3xl flex items-center justify-center border" style="background: rgba(139,124,248,0.14); border-color: rgba(139,124,248,0.30);">
                        <i data-lucide="shield-check" class="w-12 h-12" style="color: var(--accent-2);"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-accent2 text-[11px] font-semibold tracking-[0.12em] uppercase">Main Tool</span>
                        <h3 class="serif text-2xl md:text-3xl text-white mt-1 mb-2">Website Vulnerability Scanner</h3>
                        <p class="text-white/60 text-sm md:text-[15px] leading-relaxed max-w-2xl">Put your site to the test. Port scan, SSL analysis, Nikto test and DNS recon — all in one scan.</p>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-6 h-6 text-white/30 group-hover:text-white/70 transition-colors self-end md:self-auto"></i>
                </div>
            </a>

            <!-- Regular tools grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <a href="browser-scan.php" target="_blank" class="reveal glass glass-hover rounded-2xl p-6 block">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background: rgba(45,212,191,0.12);">
                        <i data-lucide="globe" class="w-6 h-6" style="color:#2dd4bf;"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-1">Browser Scanner</h3>
                    <p class="text-white/55 text-sm leading-relaxed mb-4">Find out how much information your browser is giving away to anyone who asks.</p>
                    <span class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-wide text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Active
                    </span>
                </a>

                <a href="vpn-checker.php" target="_blank" class="reveal glass glass-hover rounded-2xl p-6 block" style="transition-delay:.06s">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background: rgba(251,191,36,0.12);">
                        <i data-lucide="lock" class="w-6 h-6" style="color:#fbbf24;"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-1">VPN Checker</h3>
                    <p class="text-white/55 text-sm leading-relaxed mb-4">Your VPN is on, but are you really protected? Check IP, WebRTC and DNS leaks.</p>
                    <span class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-wide text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Active
                    </span>
                </a>

                <a href="data-breach-checker.php" target="_blank" class="reveal glass glass-hover rounded-2xl p-6 block">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background: rgba(251,113,133,0.12);">
                        <i data-lucide="eye" class="w-6 h-6" style="color:#fb7185;"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-1">Data Breach Checker</h3>
                    <p class="text-white/55 text-sm leading-relaxed mb-4">Has your email ended up in any leaks? Better find out before someone else does.</p>
                    <span class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-wide text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Active
                    </span>
                </a>

                <div class="reveal rounded-2xl p-6 flex items-center justify-center text-center border border-dashed border-white/12" style="transition-delay:.06s">
                    <span class="inline-flex items-center gap-2 text-white/35 text-sm font-medium">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Something new is coming...
                    </span>
                </div>
            </div>
        </section>

        <!-- ══════════ EXTENSION PROMO ══════════ -->
        <section id="extension" class="px-6 pb-24 md:pb-32 max-w-6xl mx-auto">
            <div class="reveal glass relative rounded-3xl p-8 md:p-12 overflow-hidden">
                <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full blur-3xl pointer-events-none" style="background: rgba(124,92,255,0.16);"></div>
                <div class="relative flex flex-col md:flex-row items-start md:items-center gap-8">
                    <div class="shrink-0 w-24 h-24 rounded-3xl flex items-center justify-center border" style="background: rgba(139,124,248,0.14); border-color: rgba(139,124,248,0.30);">
                        <i data-lucide="puzzle" class="w-12 h-12" style="color: var(--accent-2);"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-accent2 text-[11px] font-semibold tracking-[0.12em] uppercase">New · Browser extension</span>
                        <h2 class="serif text-2xl md:text-4xl text-white mt-1 mb-3">Stay safe from phishing while you browse</h2>
                        <p class="text-white/60 text-sm md:text-[15px] leading-relaxed max-w-2xl mb-6">The PatchPulse extension for Firefox warns you before you enter a site that mimics an official domain. Everything runs locally, without sending your history anywhere.</p>
                        <a href="extension.php" class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-medium text-white transition-colors" style="background: var(--accent);" onmouseover="this.style.background='#6c5ce7'" onmouseout="this.style.background='var(--accent)'">
                            Discover more
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════ STATS ══════════ -->
        <section class="px-6 pb-24 md:pb-32 max-w-5xl mx-auto">
            <div class="reveal glass rounded-3xl grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-white/10">
                <div class="p-8 text-center">
                    <div class="serif text-4xl md:text-5xl text-white mb-1">500+</div>
                    <div class="text-white/50 text-xs tracking-wide uppercase">Scans performed</div>
                </div>
                <div class="p-8 text-center">
                    <div class="serif text-4xl md:text-5xl text-white mb-1">99.9%</div>
                    <div class="text-white/50 text-xs tracking-wide uppercase">Guaranteed uptime</div>
                </div>
                <div class="p-8 text-center">
                    <div class="serif text-4xl md:text-5xl text-white mb-1">24/7</div>
                    <div class="text-white/50 text-xs tracking-wide uppercase">Always available</div>
                </div>
                <div class="p-8 text-center">
                    <div class="serif text-4xl md:text-5xl text-white mb-1">4</div>
                    <div class="text-white/50 text-xs tracking-wide uppercase">Active tools</div>
                </div>
            </div>
        </section>

        <!-- ══════════ FAQ ══════════ -->
        <section id="faq" class="px-6 pb-24 md:pb-32 max-w-3xl mx-auto">
            <div class="reveal text-center mb-12">
                <p class="text-accent2 text-xs font-medium tracking-[0.2em] uppercase mb-3">Support</p>
                <h2 class="serif text-4xl md:text-5xl text-white mb-4">Frequently Asked Questions</h2>
                <p class="text-white/55 text-base max-w-xl mx-auto">Everything you need to know about PatchPulse and our scanners.</p>
            </div>

            <div class="space-y-3" id="faq-list">
                <div class="faq-item reveal glass rounded-2xl overflow-hidden">
                    <button class="faq-q w-full flex items-center justify-between gap-4 text-left px-6 py-5 cursor-pointer">
                        <span class="text-white font-medium">Is everything really free?</span>
                        <i data-lucide="chevron-down" class="faq-chevron w-5 h-5 text-white/50 shrink-0"></i>
                    </button>
                    <div class="faq-a"><div><p class="px-6 pb-5 text-white/55 text-sm leading-relaxed">Yes, all our scanners are completely free. We believe online security should be accessible to everyone.</p></div></div>
                </div>
                <div class="faq-item reveal glass rounded-2xl overflow-hidden">
                    <button class="faq-q w-full flex items-center justify-between gap-4 text-left px-6 py-5 cursor-pointer">
                        <span class="text-white font-medium">Do I need to sign up to use the scanners?</span>
                        <i data-lucide="chevron-down" class="faq-chevron w-5 h-5 text-white/50 shrink-0"></i>
                    </button>
                    <div class="faq-a"><div><p class="px-6 pb-5 text-white/55 text-sm leading-relaxed">Registration is optional for all tools except the Website Vulnerability Scanner, where you need to log in and verify your domain.</p></div></div>
                </div>
                <div class="faq-item reveal glass rounded-2xl overflow-hidden">
                    <button class="faq-q w-full flex items-center justify-between gap-4 text-left px-6 py-5 cursor-pointer">
                        <span class="text-white font-medium">How does the Website Vulnerability Scanner work?</span>
                        <i data-lucide="chevron-down" class="faq-chevron w-5 h-5 text-white/50 shrink-0"></i>
                    </button>
                    <div class="faq-a"><div><p class="px-6 pb-5 text-white/55 text-sm leading-relaxed">Enter the URL of the site you want to analyze and our scanner will automatically check the most common vulnerabilities, security configurations and potential risks using well-known tools in the field like nmap, nikto, etc., bundled together to give you a complete service.</p></div></div>
                </div>
                <div class="faq-item reveal glass rounded-2xl overflow-hidden">
                    <button class="faq-q w-full flex items-center justify-between gap-4 text-left px-6 py-5 cursor-pointer">
                        <span class="text-white font-medium">How does the Browser Scanner work?</span>
                        <i data-lucide="chevron-down" class="faq-chevron w-5 h-5 text-white/50 shrink-0"></i>
                    </button>
                    <div class="faq-a"><div><p class="px-6 pb-5 text-white/55 text-sm leading-relaxed">The Browser Scanner shows what a site can get from your browser, revealing personal and browsing information. It is a way to check your online privacy.</p></div></div>
                </div>
            </div>
        </section>

        <!-- ══════════ ABOUT ══════════ -->
        <section id="about" class="px-6 pb-24 md:pb-32 max-w-3xl mx-auto">
            <div class="reveal text-center mb-10">
                <p class="text-accent2 text-xs font-medium tracking-[0.2em] uppercase mb-3">About us</p>
                <h2 class="serif text-4xl md:text-5xl text-white">Why PatchPulse</h2>
            </div>
            <div class="reveal glass rounded-3xl p-8 md:p-10 space-y-5">
                <p class="text-white/65 text-[15px] leading-relaxed">PatchPulse is a web application designed to improve users' online security by providing simple, accessible tools to scan websites for vulnerabilities and security risks.</p>
                <p class="text-white/65 text-[15px] leading-relaxed">Our goal is to raise awareness of security and privacy issues during web browsing, offering professional scanners that are completely free and always up to date.</p>
                <div class="rounded-2xl p-5 border-l-2" style="background: rgba(139,124,248,0.07); border-color: var(--accent);">
                    <h4 class="text-white font-semibold mb-1">🚧 Project in Development</h4>
                    <p class="text-white/55 text-sm leading-relaxed">PatchPulse is still under development and we are actively working to add new features and improvements. Follow us to stay up to date on what's new!</p>
                </div>
            </div>
        </section>

        <!-- ══════════ ACCOUNT ══════════ -->
        <section id="account" class="px-6 pb-24 md:pb-32 max-w-3xl mx-auto">
            <div class="reveal text-center mb-10">
                <p class="text-accent2 text-xs font-medium tracking-[0.2em] uppercase mb-3">Personal Area</p>
                <h2 class="serif text-4xl md:text-5xl text-white">Your Account</h2>
            </div>
            <div class="reveal glass rounded-3xl p-8 md:p-10 text-center">
                <h3 class="serif text-2xl md:text-3xl text-white mb-2">Sign in to your Account</h3>
                <p class="text-white/55 text-sm max-w-lg mx-auto mb-7">Sign up to save your scan results, access your history and receive notifications about new tools.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="log-reg.php#login" class="w-full sm:w-auto rounded-full px-8 py-3 text-sm font-medium text-white text-center transition-colors" style="background: var(--accent);" onmouseover="this.style.background='#6c5ce7'" onmouseout="this.style.background='var(--accent)'">Sign In</a>
                    <a href="log-reg.php#register" class="w-full sm:w-auto rounded-full px-8 py-3 text-sm font-medium text-white text-center border border-white/15 hover:border-white/35 transition-colors">Sign Up</a>
                </div>
                <p class="text-white/35 text-xs mt-6">Or continue without registering to use our basic scanners</p>
            </div>
        </section>

        <!-- ══════════ FOOTER ══════════ -->
        <footer class="border-t border-white/10 px-6 pt-16 pb-10">
            <div class="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-10">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                        <span class="text-white font-semibold">PatchPulse</span>
                    </div>
                    <p class="text-white/45 text-sm leading-relaxed max-w-xs">Free security scanners to improve your online safety. Identify vulnerabilities and privacy risks.</p>
                </div>
                <div>
                    <h4 class="text-white/90 font-semibold text-sm mb-3">Scanners</h4>
                    <div class="flex flex-col gap-2 text-sm text-white/50">
                        <a href="browser-scan.php" class="hover:text-white transition-colors">Browser Scanner</a>
                        <a href="VulnerabilityScanner.php" class="hover:text-white transition-colors">Website Vulnerability Scanner</a>
                        <a href="vpn-checker.php" class="hover:text-white transition-colors">VPN Security Checker</a>
                        <a href="data-breach-checker.php" class="hover:text-white transition-colors">Data Breach Checker</a>
                        <a href="#" class="hover:text-white transition-colors">Coming Soon...</a>
                    </div>
                </div>
                <div>
                    <h4 class="text-white/90 font-semibold text-sm mb-3">Contact</h4>
                    <div class="flex flex-col gap-2 text-sm text-white/50">
                        <a href="mailto:support@patchpulse.org" class="inline-flex items-center gap-2 hover:text-white transition-colors"><i data-lucide="mail" class="w-4 h-4"></i> support@patchpulse.org</a>
                        <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank" class="inline-flex items-center gap-2 hover:text-white transition-colors"><i data-lucide="github" class="w-4 h-4"></i> MrTcStudios/PatchPulse</a>
                    </div>
                </div>
                <div>
                    <h4 class="text-white/90 font-semibold text-sm mb-3">Resources</h4>
                    <div class="flex flex-col gap-2 text-sm text-white/50">
                        <a href="#account" class="hover:text-white transition-colors">Account Area</a>
                        <a href="#about" class="hover:text-white transition-colors">Documentation</a>
                        <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
                    </div>
                </div>
            </div>
            <div class="max-w-6xl mx-auto mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40">
                <p>© 2025 PatchPulse. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="policy/privacy_policy.php" target="_blank" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="policy/terms&condition.php" target="_blank" class="hover:text-white transition-colors">Terms of Service</a>
                    <a href="policy/security-policy.php" target="_blank" class="hover:text-white transition-colors">Security Policy</a>
                </div>
            </div>
        </footer>
    </main>

    <script>
        // ── Animated background: network mesh + sonar scan pulse (canvas) ───
        // Procedural & unique: drifting nodes linked into a mesh, with a radar
        // ring expanding from the center that lights up the nodes it crosses —
        // evokes a network being monitored / scanned (PatchPulse theme).
        (function () {
            var canvas = document.getElementById('bg-canvas');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var DPR = Math.min(window.devicePixelRatio || 1, 2);
            var ACC = '139,124,248';                 // brand purple #8b7cf8
            var W = 0, H = 0, nodes = [], linkDist = 150;
            var cx = 0, cy = 0, ring = 0, ringMax = 0, ringSpeed = 1, raf = 0;

            function build() {
                W = canvas.clientWidth; H = canvas.clientHeight;
                canvas.width = W * DPR; canvas.height = H * DPR;
                ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
                linkDist = Math.max(120, Math.min(180, W / 9));
                var count = Math.round(Math.min(80, (W * H) / 17000));
                nodes = [];
                for (var i = 0; i < count; i++) {
                    nodes.push({
                        x: Math.random() * W, y: Math.random() * H,
                        vx: (Math.random() - 0.5) * 0.22, vy: (Math.random() - 0.5) * 0.22,
                        r: Math.random() * 1.4 + 0.7
                    });
                }
                cx = W * 0.5; cy = H * 0.4;
                ringMax = Math.hypot(Math.max(cx, W - cx), Math.max(cy, H - cy)) + 60;
                ringSpeed = ringMax / 460;            // full sweep ~7.5s @60fps
                if (!ring) ring = Math.random() * ringMax;
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);

                for (var i = 0; i < nodes.length; i++) {       // move
                    var a = nodes[i];
                    a.x += a.vx; a.y += a.vy;
                    if (a.x < 0 || a.x > W) a.vx *= -1;
                    if (a.y < 0 || a.y > H) a.vy *= -1;
                }
                for (i = 0; i < nodes.length; i++) {           // links
                    for (var j = i + 1; j < nodes.length; j++) {
                        var b = nodes[i], c = nodes[j];
                        var dx = b.x - c.x, dy = b.y - c.y, d = Math.sqrt(dx * dx + dy * dy);
                        if (d < linkDist) {
                            ctx.strokeStyle = 'rgba(' + ACC + ',' + (1 - d / linkDist) * 0.16 + ')';
                            ctx.lineWidth = 1;
                            ctx.beginPath(); ctx.moveTo(b.x, b.y); ctx.lineTo(c.x, c.y); ctx.stroke();
                        }
                    }
                }

                ring += ringSpeed;                              // sonar ring
                if (ring > ringMax) ring = 0;
                ctx.strokeStyle = 'rgba(' + ACC + ',0.05)';
                ctx.lineWidth = 1.5;
                ctx.beginPath(); ctx.arc(cx, cy, ring, 0, Math.PI * 2); ctx.stroke();

                for (i = 0; i < nodes.length; i++) {           // nodes (+ ring highlight)
                    var n = nodes[i];
                    var diff = Math.abs(Math.hypot(n.x - cx, n.y - cy) - ring);
                    var hot = diff < 28 ? (1 - diff / 28) : 0;
                    if (hot > 0) {
                        ctx.fillStyle = 'rgba(' + ACC + ',' + (0.16 * hot) + ')';
                        ctx.beginPath(); ctx.arc(n.x, n.y, n.r + 6 + hot * 2, 0, Math.PI * 2); ctx.fill();
                    }
                    ctx.fillStyle = 'rgba(255,255,255,' + (0.22 + hot * 0.5) + ')';
                    ctx.beginPath(); ctx.arc(n.x, n.y, n.r + hot * 1.6, 0, Math.PI * 2); ctx.fill();
                }

                if (!reduce) raf = requestAnimationFrame(draw);
            }

            build();
            if (reduce) { ringSpeed = 0; draw(); }              // single static frame
            else { draw(); }
            window.addEventListener('resize', function () {
                cancelAnimationFrame(raf); build();
                if (reduce) { ringSpeed = 0; draw(); } else { draw(); }
            });
        })();

        // ── Lucide icons ───────────────────────────────────────────────────
        if (window.lucide) lucide.createIcons();

        // ── CTA: button <-> email form + typewriter placeholder ────────────
        (function () {
            var ctaButton = document.getElementById('cta-button');
            var ctaForm   = document.getElementById('cta-form');
            var emailInput = document.getElementById('cta-email');
            var iconArrow = document.getElementById('icon-arrow');
            var iconCheck = document.getElementById('icon-check');
            var twTimer = null, resetTimer = null;

            function typewrite(text) {
                if (twTimer) clearInterval(twTimer);
                emailInput.placeholder = '';
                var i = 0;
                twTimer = setInterval(function () {
                    emailInput.placeholder = text.slice(0, ++i);
                    if (i >= text.length) { clearInterval(twTimer); twTimer = null; }
                }, 60);
            }
            function replay(el) { el.classList.remove('swap-in'); void el.offsetWidth; el.classList.add('swap-in'); }

            function showForm() {
                ctaButton.classList.add('hidden');
                ctaForm.classList.remove('hidden'); ctaForm.classList.add('flex');
                replay(ctaForm);
                iconArrow.classList.remove('hidden'); iconCheck.classList.add('hidden');
                emailInput.disabled = false; emailInput.value = '';
                emailInput.focus();
                typewrite('Enter Your Email Here For Early Access');
            }
            function resetCta() {
                if (twTimer) { clearInterval(twTimer); twTimer = null; }
                ctaForm.classList.add('hidden'); ctaForm.classList.remove('flex');
                ctaButton.classList.remove('hidden'); replay(ctaButton);
                emailInput.value = ''; emailInput.placeholder = ''; emailInput.disabled = false;
                iconArrow.classList.remove('hidden'); iconCheck.classList.add('hidden');
            }

            ctaButton.addEventListener('click', showForm);
            ctaForm.addEventListener('submit', function (e) {
                e.preventDefault();
                iconArrow.classList.add('hidden'); iconCheck.classList.remove('hidden');
                emailInput.blur(); emailInput.disabled = true; emailInput.value = '';
                typewrite('You Will Receive Notifications By Email');
                if (resetTimer) clearTimeout(resetTimer);
                resetTimer = setTimeout(resetCta, 4000);
            });
        })();

        // ── FAQ accordion ───────────────────────────────────────────────────
        (function () {
            var items = document.querySelectorAll('#faq-list .faq-item');
            items.forEach(function (item) {
                item.querySelector('.faq-q').addEventListener('click', function () {
                    var isOpen = item.classList.contains('open');
                    items.forEach(function (i) { i.classList.remove('open'); });
                    if (!isOpen) item.classList.add('open');
                });
            });
        })();

        // ── Reveal on scroll ─────────────────────────────────────────────────
        (function () {
            var els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) {
                els.forEach(function (el) { el.classList.add('in'); });
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { entry.target.classList.add('in'); io.unobserve(entry.target); }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
            els.forEach(function (el) { io.observe(el); });
        })();
    </script>
</body>
</html>
