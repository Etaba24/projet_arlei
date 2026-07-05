@php
    $invalidationNotifications = collect();
    if (Auth::check() && Auth::user()->employe && Auth::user()->employe->equipe_id) {
        $invalidationNotifications = \App\Models\PhaseProduction::where('equipe_id', Auth::user()->employe->equipe_id)
            ->where('statut', 'en_attente')
            ->where('observations', 'like', '%invalidée par%')
            ->with('ordreProduction', 'transformation')
            ->orderBy('updated_at', 'desc')
            ->get();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Production App') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Dark mode init : avant tout CSS pour éviter le flash -->
        <script>
            (function(){
                var t = localStorage.getItem('theme');
                if(t === 'dark') document.documentElement.setAttribute('data-theme','dark');
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Outfit', sans-serif; }

            /* ═══════════════════════════════════════════════════════
               DARK THEME — « Industrial Night » (WCAG AA/AAA vérifié)
               Gris-bleu foncés, jamais de noir pur. L'élévation
               s'exprime par une surface plus claire, pas par l'ombre.

               Niveaux de surface :
                 L0 bg     : #11151C  — fond de page
                 L1 surface: #1A202B  — cartes / tableaux
                 L2 raised : #232B39  — modals / dropdowns / hover
                 L3 input  : #141A24  — champs de saisie
                 border    : #39465C  — séparateurs (décoratif)
                 border-in : #66809E  — bordures de champs (≥3:1, WCAG 1.4.11)

               Texte (ratios mesurés sur L1) :
                 t1 données   : #E9EEF6  → 14,0:1 (AAA)
                 t2 courant   : #ADB9CB  →  8,2:1 (AAA)
                 t3 discret   : #8E9DB2  →  5,9:1 (AA+)
                 (pas de blanc pur : évite l'effet de halo)

               Accent : #34D399 (9:1 sur L1)
               ═══════════════════════════════════════════════════════ */

            :root[data-theme="dark"] {
                --dk-bg:           #11151C;
                --dk-surface:      #1A202B;
                --dk-raised:       #232B39;
                --dk-input:        #141A24;
                --dk-border:       #39465C;
                --dk-border-input: #66809E;
                --dk-hover:        #232B39;
                --dk-t1:           #E9EEF6;
                --dk-t2:           #ADB9CB;
                --dk-t3:           #8E9DB2;
                --dk-accent:       #34D399;
            }

            /* ── Fond global ── */
            [data-theme="dark"] body,
            [data-theme="dark"] main { background-color: var(--dk-bg) !important; color: var(--dk-t2) !important; }

            /* ── Header de page ── */
            [data-theme="dark"] header:not(aside header) {
                background-color: var(--dk-surface) !important;
                border-color: var(--dk-border) !important;
            }

            /* ── Toutes les cartes (bg-white, rounded panels) ── */
            [data-theme="dark"] .bg-white,
            [data-theme="dark"] [class*="bg-white/"] {
                background-color: var(--dk-surface) !important;
            }

            /* ── Dégradés clairs ── */
            [data-theme="dark"] [class*="from-emerald-50"],
            [data-theme="dark"] [class*="to-teal-50"],
            [data-theme="dark"] [class*="from-blue-50"],
            [data-theme="dark"] [class*="to-indigo-50"] {
                --tw-gradient-from: var(--dk-raised) !important;
                --tw-gradient-to: var(--dk-raised) !important;
                --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
                background-color: var(--dk-raised) !important;
            }

            /* ── Niveaux gris clair → surface légèrement surélevée ── */
            [data-theme="dark"] .bg-slate-50,
            [data-theme="dark"] .bg-orange-50 {
                background-color: var(--dk-raised) !important;
            }
            [data-theme="dark"] .bg-slate-100 {
                background-color: var(--dk-raised) !important;
            }

            /* ── Couleurs sémantiques teintées pour le thème sombre ── */
            [data-theme="dark"] .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.2) !important; }
            [data-theme="dark"] .bg-blue-50    { background-color: rgba(59, 130, 246, 0.1) !important; border-color: rgba(59, 130, 246, 0.2) !important; }
            [data-theme="dark"] .bg-amber-50   { background-color: rgba(245, 158, 11, 0.1) !important; border-color: rgba(245, 158, 11, 0.2) !important; }
            [data-theme="dark"] .bg-violet-50  { background-color: rgba(139, 92, 246, 0.1) !important; border-color: rgba(139, 92, 246, 0.2) !important; }
            [data-theme="dark"] .bg-rose-50    { background-color: rgba(244, 63, 94, 0.1) !important; border-color: rgba(244, 63, 94, 0.2) !important; }

            .card-tinted {
                background-color: color-mix(in srgb, var(--tint-color) 4%, white);
            }
            [data-theme="dark"] .card-tinted {
                background-color: color-mix(in srgb, var(--tint-color) 8%, var(--dk-raised)) !important;
            }

            [data-theme="dark"] .text-emerald-700 { color: #34d399 !important; }
            [data-theme="dark"] .text-blue-700    { color: #60a5fa !important; }
            [data-theme="dark"] .text-amber-700   { color: #fbbf24 !important; }
            [data-theme="dark"] .text-violet-700  { color: #a78bfa !important; }
            [data-theme="dark"] .text-rose-700    { color: #fb7185 !important; }

            /* ── Ajustement couleurs spécifiques ── */
            [data-theme="dark"] .text-orange-900 { color: #fdba74 !important; }
            [data-theme="dark"] .border-orange-200 { border-color: #7c2d12 !important; }

            /* ── Survols ── */
            [data-theme="dark"] [class*="hover:bg-slate-50"]:hover,
            [data-theme="dark"] [class*="hover:bg-white"]:hover  { background-color: var(--dk-hover) !important; }
            [data-theme="dark"] [class*="hover:bg-slate-100"]:hover { background-color: #28303F !important; }
            [data-theme="dark"] [class*="hover:bg-slate-200"]:hover { background-color: #2C3646 !important; }

            /* ── Bordures ── */
            [data-theme="dark"] .border-slate-50  { border-color: #2E394B !important; }
            [data-theme="dark"] .border-slate-100 { border-color: var(--dk-border) !important; }
            [data-theme="dark"] .border-slate-200 { border-color: #44536C !important; }
            [data-theme="dark"] .divide-y > * + * {
                border-color: var(--dk-border) !important;
            }
            [data-theme="dark"] .divide-x > * + *  { border-color: var(--dk-border) !important; }

            /* ── Texte ── */
            [data-theme="dark"] .text-slate-900,
            [data-theme="dark"] .text-slate-800 { color: var(--dk-t1) !important; }
            [data-theme="dark"] .text-slate-700,
            [data-theme="dark"] .text-slate-600 { color: var(--dk-t2) !important; }
            [data-theme="dark"] .text-slate-500,
            [data-theme="dark"] .text-slate-400 { color: var(--dk-t3) !important; }

            /* ── Champs de saisie ── */
            [data-theme="dark"] input:not([type=checkbox]):not([type=radio]):not([type=color]):not([type=range]),
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
                background-color: var(--dk-input)         !important;
                color:            var(--dk-t1)             !important;
                border-color:     var(--dk-border-input)   !important;
            }
            [data-theme="dark"] input::placeholder,
            [data-theme="dark"] textarea::placeholder { color: var(--dk-t3) !important; opacity: 1; }
            [data-theme="dark"] input:focus,
            [data-theme="dark"] select:focus,
            [data-theme="dark"] textarea:focus {
                border-color: var(--dk-accent) !important;
                box-shadow: 0 0 0 3px rgba(52,211,153,.25) !important;
            }
            [data-theme="dark"] input:disabled { background-color: #141A24 !important; color: var(--dk-t3) !important; }

            /* ══════════════════════════════════════════════════════
               TABLEAUX — Design Premium Global
               Appliqué à toutes les pages automatiquement
               ══════════════════════════════════════════════════════ */

            /* ── Conteneur du tableau ── */
            .bg-white.border.border-slate-200.rounded-2xl.shadow-sm.overflow-hidden {
                box-shadow: 0 4px 20px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04) !important;
                border-color: #e2e8f0 !important;
            }

            /* ── En-têtes type Émeraude : vert clair uniforme ── */
            thead tr.bg-emerald-600,
            thead tr[class*="bg-emerald-6"],
            thead tr[class*="bg-emerald-7"],
            thead tr[class*="bg-emerald-8"] {
                background: #10b981 !important;
                position: relative;
            }

            /* ── En-têtes type Slate (tableaux secondaires) : style élégant uni ── */
            thead tr[class*="bg-slate-50"],
            thead tr.bg-slate-50 {
                background: linear-gradient(135deg, #f1f5f9 0%, #e8f4f8 100%) !important;
                border-bottom: 2px solid #10b981 !important;
            }
            thead tr[class*="bg-slate-50"] th,
            thead tr.bg-slate-50 th {
                color: #374151 !important;
                text-shadow: none !important;
            }
            [data-theme="dark"] thead tr[class*="bg-slate-50"],
            [data-theme="dark"] thead tr.bg-slate-50 {
                background: linear-gradient(135deg, #1A202B 0%, #232B39 100%) !important;
                border-bottom: 2px solid var(--dk-accent) !important;
            }
            [data-theme="dark"] thead tr[class*="bg-slate-50"] th,
            [data-theme="dark"] thead tr.bg-slate-50 th {
                color: var(--dk-t2) !important;
            }

            /* ── Cellules d'en-tête ── */
            thead th {
                letter-spacing: 0.08em !important;
                font-size: 0.68rem !important;
                font-weight: 700 !important;
                text-shadow: 0 1px 2px rgba(0,0,0,.15);
                position: relative;
                white-space: nowrap;
            }
            thead th:not(:last-child)::after {
                content: '';
                position: absolute;
                right: 0;
                top: 25%;
                height: 50%;
                width: 1px;
                background: rgba(255,255,255,.25);
            }

            /* ── Séparateurs de lignes plus visibles ── */
            tbody.divide-y > tr + tr {
                border-top: 1px solid #f1f5f9 !important;
            }

            /* ── Lignes alternées (zebra) ── */
            tbody tr:nth-child(even) {
                background-color: #f8fbff !important;
            }
            tbody tr:nth-child(odd) {
                background-color: #ffffff !important;
            }

            /* ── Survol enrichi avec barre latérale ── */
            tbody tr {
                transition: all .15s ease !important;
            }
            tbody tr:hover {
                background-color: #f0fdf9 !important;
                box-shadow: inset 3px 0 0 0 #10b981 !important;
            }
            tbody tr:hover td:first-child {
                padding-left: calc(1.5rem - 1px) !important;
            }

            /* ── Cellules ── */
            td {
                transition: color .15s ease;
            }
            tbody tr:hover td {
                color: #0f172a;
            }

            /* ── Barre de progression du header (arc-en-ciel subtil) ── */
            table {
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }

            /* ── Mode sombre : tableaux ── */
            [data-theme="dark"] table            { background-color: var(--dk-surface) !important; }
            [data-theme="dark"] tbody tr:nth-child(even) { background-color: rgba(35,43,57,.45) !important; }
            [data-theme="dark"] tbody tr:nth-child(odd)  { background-color: transparent !important; }
            [data-theme="dark"] tbody tr:hover    { background-color: var(--dk-hover) !important; box-shadow: inset 3px 0 0 0 var(--dk-accent) !important; }
            [data-theme="dark"] tbody tr:hover td { background-color: transparent !important; color: var(--dk-t1) !important; }
            [data-theme="dark"] td, [data-theme="dark"] th { border-color: var(--dk-border) !important; }
            [data-theme="dark"] tbody.divide-y > tr + tr { border-top-color: var(--dk-border) !important; }
            /* Émeraude profond : texte blanc lisible (5,1:1) contrairement à #10b981 (2,5:1) */
            [data-theme="dark"] thead tr.bg-emerald-600,
            [data-theme="dark"] thead tr[class*="bg-emerald-6"] {
                background: #047857 !important;
            }

            /* ── Ombres ── */
            [data-theme="dark"] [class*="shadow-sm"]  { box-shadow: 0 1px 6px rgba(0,0,0,.7) !important; }
            [data-theme="dark"] [class*="shadow-md"]  { box-shadow: 0 4px 16px rgba(0,0,0,.7) !important; }
            [data-theme="dark"] [class*="shadow-xl"]  { box-shadow: 0 14px 40px rgba(0,0,0,.8) !important; }
            [data-theme="dark"] [class*="shadow-2xl"] { box-shadow: 0 24px 60px rgba(0,0,0,.9) !important; }

            /* ── Pagination ── */
            [data-theme="dark"] nav span,
            [data-theme="dark"] nav a {
                background-color: var(--dk-surface) !important;
                border-color:     var(--dk-border)  !important;
                color:            var(--dk-t2)       !important;
            }
            [data-theme="dark"] nav a:hover { background-color: var(--dk-hover) !important; color: var(--dk-t1) !important; }

            /* ── SweetAlert2 ── */
            [data-theme="dark"] .swal2-popup {
                background-color: var(--dk-raised) !important;
                color: var(--dk-t1) !important;
                border: 1px solid var(--dk-border) !important;
            }
            [data-theme="dark"] .swal2-title         { color: var(--dk-t1) !important; }
            [data-theme="dark"] .swal2-html-container { color: var(--dk-t2) !important; }

            /* ── Scrollbar ── */
            [data-theme="dark"]::-webkit-scrollbar,
            [data-theme="dark"] *::-webkit-scrollbar        { width: 6px; height: 6px; }
            [data-theme="dark"]::-webkit-scrollbar-track,
            [data-theme="dark"] *::-webkit-scrollbar-track  { background: var(--dk-bg); }
            [data-theme="dark"]::-webkit-scrollbar-thumb,
            [data-theme="dark"] *::-webkit-scrollbar-thumb  { background: var(--dk-border); border-radius: 4px; }
            [data-theme="dark"] *::-webkit-scrollbar-thumb:hover { background: #4A5A73; }

            /* ── Badges neutres slate ── */
            [data-theme="dark"] .bg-slate-100.text-slate-700,
            [data-theme="dark"] .bg-slate-100.text-slate-600 {
                background-color: var(--dk-raised) !important;
                color: var(--dk-t3) !important;
            }

            /* ── Transition douce ── */
            [data-theme="dark"], [data-theme="dark"] * {
                transition: background-color .2s ease, border-color .2s ease, color .15s ease !important;
            }

            /* ── Impression (Print) ── */
            @media print {
                @page { size: auto; margin: 0mm; } /* Hide browser URL and Laravel title completely */

                header, aside, .no-print, [x-show="sidebarOpen"] { display: none !important; }
                .md\:ml-64, .md\:ml-20 { margin-left: 0 !important; }
                .pt-14 { padding-top: 0 !important; }
                .min-h-screen { min-height: auto !important; }
                
                body { padding: 15mm !important; } /* Restore margin via padding so content is not cut off */
                body, [data-theme="dark"] body { background-color: #fff !important; color: #000 !important; }
                [data-theme="dark"] .bg-white { background-color: #fff !important; color: #000 !important; }
                
                .bg-white { box-shadow: none !important; border: none !important; }
                table { width: 100% !important; border-collapse: collapse !important; margin-top: 1rem; }
                th { background-color: #10b981 !important; color: white !important; font-weight: bold; border: 1px solid #047857 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                td { border: 1px solid #cbd5e1 !important; color: #1e293b !important; }
                
                /* Cache la colonne des actions (qui est toujours la dernière) */
                th:last-child, td:last-child { display: none !important; }
                
                .bg-emerald-50 { border: 1px solid #10b981 !important; }
                .bg-amber-50 { border: 1px solid #f59e0b !important; }
                .bg-rose-50 { border: 1px solid #f43f5e !important; }
                .bg-blue-50 { border: 1px solid #3b82f6 !important; }
                
                /* Cache les éléments non pertinents */
                nav[aria-label="Pagination Navigation"] { display: none !important; }
                .sticky.top-14 { display: none !important; }
                form { display: none !important; } /* Hide search and filter forms */
                button { display: none !important; } /* Hide buttons */
                a.inline-flex, a.bg-emerald-600 { display: none !important; } /* Hide action links */
                
                /* En-tête d'impression spécifique avec décoration Emerald */
                .print-only-header { 
                    display: flex !important; 
                    flex-direction: column; 
                    align-items: center; 
                    justify-content: center; 
                    margin-bottom: 2rem; 
                    border-top: 8px solid #10b981; 
                    border-bottom: 2px solid #e2e8f0; 
                    padding: 1.5rem 0; 
                    background-color: #f8fafc !important;
                    -webkit-print-color-adjust: exact !important; 
                    print-color-adjust: exact !important;
                }
                .print-only-header img { max-height: 80px; margin-bottom: 10px; }
                .print-only-header h2 { font-size: 24px; font-weight: 800; margin: 0; color: #064e3b; text-transform: uppercase; letter-spacing: 0.05em; }
                .print-only-header p { font-size: 14px; color: #047857; margin-top: 5px; font-weight: 600; }
                
                /* Force full width content */
                .max-w-7xl { max-width: 100% !important; margin: 0 !important; }
                .p-4, .p-6, .p-8 { padding: 0 !important; }
                .overflow-x-auto { overflow: visible !important; }
            }

            /* ── En-tête masqué par défaut ── */
            .print-only-header { display: none; }

            /* ── Mode clair : fond de page et body ── */
            body { background-color: #f8fafc; }
        </style>
    </head>

    <body class="h-full text-slate-800 antialiased"
          x-data="{
              sidebarOpen: false,
              sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
              dark: document.documentElement.getAttribute('data-theme') === 'dark',
              toggleCollapsed() {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
                  localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
              },
              toggleDark() {
                  this.dark = !this.dark;
                  if (this.dark) {
                      document.documentElement.setAttribute('data-theme', 'dark');
                      localStorage.setItem('theme', 'dark');
                  } else {
                      document.documentElement.removeAttribute('data-theme');
                      localStorage.setItem('theme', 'light');
                  }
              }
          }">

        
        <!-- ── GLOBAL TOP NAVBAR ── -->
        <header class="fixed top-0 inset-x-0 h-14 z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="window.innerWidth >= 768 ? toggleCollapsed() : sidebarOpen = true" class="text-slate-500 hover:text-slate-900 focus:outline-none transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="shrink-0 flex items-center justify-center w-8 h-8 bg-emerald-600 rounded-lg text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                    <span class="font-bold text-xl text-slate-900 tracking-wide hidden sm:block">ARLEI</span>
                </a>
            </div>

            <div class="flex items-center gap-1 sm:gap-3">
                @if(Auth::user()->isOperateur() && Auth::user()->employe && Auth::user()->employe->equipe_id)
                    <div x-data="{ isOpen: false }" class="relative inline-block">
                        <button @click="isOpen = !isOpen" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors relative" title="Notifications d'invalidation">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            @if($invalidationNotifications->count() > 0)
                                <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                            @endif
                        </button>
                        <div x-show="isOpen" @click.outside="isOpen = false" x-transition class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl border border-slate-200 shadow-2xl z-50 p-4 text-slate-800" style="display: none;">
                            <h4 class="font-bold text-sm border-b border-slate-100 pb-2 mb-3 flex items-center justify-between">
                                <span>Tâches Invalidées</span>
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-600 rounded-full text-[10px] font-bold">{{ $invalidationNotifications->count() }}</span>
                            </h4>
                            <div class="max-h-60 overflow-y-auto space-y-3">
                                @forelse($invalidationNotifications as $notif)
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-left">
                                        <p class="font-bold text-slate-900">{{ $notif->ordreProduction->code }} - {{ $notif->transformation->designation }}</p>
                                        <p class="text-rose-600 font-semibold mt-1">Motif d'invalidation :</p>
                                        <p class="text-slate-600 mt-0.5 italic text-left">
                                            @php
                                                $lines = explode("
", $notif->observations);
                                                $lastComment = '';
                                                foreach(array_reverse($lines) as $line) {
                                                    if (str_contains($line, 'Phase invalidée par')) { $lastComment = $line; break; }
                                                }
                                                if (!$lastComment && !empty($lines)) { $lastComment = end($lines); }
                                            @endphp
                                            {{ $lastComment ?: 'Aucun détail' }}
                                        </p>
                                        <div class="mt-2 text-right">
                                            <a href="{{ route('ordre-productions.show', $notif->ordreProduction) }}" class="text-[10px] font-bold text-emerald-600 hover:underline">Voir la fiche &rarr;</a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center py-4 text-slate-400">Aucune tâche invalidée.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
                <button @click="toggleDark()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors" :title="dark ? 'Mode clair' : 'Mode sombre'">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <div class="flex items-center gap-2 px-2 border-l border-slate-200 ml-2">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-500">{{ Auth::user()->customRole?->name ?? Auth::user()->role }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-semibold text-xs shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-1">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors" title="Déconnexion">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <div class="min-h-screen flex pt-14">

            <!-- ── Sidebar Desktop ── -->
            <aside class="hidden md:flex md:flex-col md:fixed top-14 bottom-0 z-40 bg-white text-slate-600 border-r border-slate-200 transition-all duration-300 ease-in-out"
                   :class="sidebarCollapsed ? 'md:w-20' : 'md:w-64'">
                <div id="sidebar-scrollable-container" class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden px-4 py-4 space-y-7">
                    @include('layouts.navigation-links')
                </div>
            </aside>

            <!-- ── Sidebar Mobile ── -->
            <div class="relative z-50 md:hidden" role="dialog" aria-modal="true">
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="sidebarOpen = false" style="display: none;"></div>
                <div class="fixed inset-0 flex pointer-events-none">
                    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-col bg-white text-slate-600 shadow-2xl pointer-events-auto" style="display: none;">
                        <div class="flex items-center justify-between h-14 px-6 border-b border-slate-100 bg-white">
                            <span class="font-bold text-lg text-slate-900">Navigation</span>
                            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-600 focus:outline-none"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-7">
                            @include('layouts.navigation-links')
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Contenu principal ── -->
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
                <!-- Header de page -->
                @isset($header)
                    <div class="sticky top-14 z-30 px-4 sm:px-6 lg:px-8 py-3 bg-white/90 backdrop-blur-sm border-b border-slate-200">
                        <div class="max-w-7xl mx-auto w-full">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0">
                                {{ $header }}
                            </div>
                        </div>
                    </div>
                @endisset

                <!-- Contenu -->
                <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8 bg-slate-50">
                    <div class="print-only-header">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo ARLEI" />
                        <h2 id="print-document-title">Document Interne ARLEI</h2>
                        <p>Imprimé le {{ date('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="max-w-7xl mx-auto w-full">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-2xl shadow-xl border border-slate-100' },
                didOpen: (t) => {
                    t.addEventListener('mouseenter', Swal.stopTimer);
                    t.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

                @if(session('status') || session('success'))
                    Toast.fire({ icon: 'success', title: "{{ addslashes(session('status') ?? session('success')) }}", background: isDark ? '#1A202B' : '#fff', color: isDark ? '#E9EEF6' : '#1e293b' });
                @endif
                @if(session('error'))
                    Toast.fire({ icon: 'error', title: "{{ addslashes(session('error')) }}", background: isDark ? '#1A202B' : '#fff', color: isDark ? '#E9EEF6' : '#1e293b' });
                @endif

                // Filtrage automatique à la saisie pour tous les inputs de recherche
                const searchInputs = document.querySelectorAll('input[name="search"]');
                searchInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        const filter = this.value.toLowerCase().trim();
                        const tables = document.querySelectorAll('table');
                        tables.forEach(table => {
                            const rows = table.querySelectorAll('tbody tr');
                            rows.forEach(row => {
                                if (row.cells.length === 1 && (row.innerText.includes('Aucun') || row.innerText.includes('Aucune'))) {
                                    row.style.display = filter === '' ? '' : 'none';
                                    return;
                                }
                                const text = row.innerText.toLowerCase();
                                row.style.display = text.includes(filter) ? '' : 'none';
                            });
                        });
                    });
                });

                // Restauration de la position de scroll de la sidebar
                const sidebar = document.getElementById('sidebar-scrollable-container');
                if (sidebar) {
                    const scrollPos = sessionStorage.getItem('sidebarScrollPos');
                    if (scrollPos) {
                        sidebar.scrollTop = scrollPos;
                    }
                    sidebar.addEventListener('scroll', function() {
                        sessionStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
                    });
                }
            });

            function confirmDelete(event, form) {
                event.preventDefault();
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette suppression est définitive et irréversible.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#e11d48',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    background: isDark ? '#232B39' : '#ffffff',
                    color:      isDark ? '#E9EEF6' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                        cancelButton:  'rounded-xl font-bold px-6 py-2.5',
                    }
                }).then(r => { if(r.isConfirmed) form.submit(); });
            }
            function triggerInvalidation(phaseId, equipeNom) {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                Swal.fire({
                    title: "Motif d'invalidation",
                    html: `Veuillez indiquer à l'équipe <strong>${equipeNom}</strong> les raisons du rejet de cette tâche :`,
                    input: "textarea",
                    inputPlaceholder: "Décrivez les motifs de l'invalidation...",
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Confirmer l\'invalidation',
                    cancelButtonText: 'Annuler',
                    background: isDark ? '#232B39' : '#ffffff',
                    color:      isDark ? '#E9EEF6' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                        cancelButton:  'rounded-xl font-bold px-6 py-2.5',
                        input: 'rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500 text-sm'
                    },
                    preConfirm: (value) => {
                        if (!value || value.trim() === '') {
                            Swal.showValidationMessage("Le motif est obligatoire pour invalider la tâche.")
                        }
                        return value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const motifInput = document.getElementById(`motif-invalider-${phaseId}`);
                        const form = document.getElementById(`form-invalider-${phaseId}`);
                        if (motifInput && form) {
                            motifInput.value = result.value;
                            form.submit();
                        }
                    }
                });
            }

            // Auto-trigger print if requested
            @if(request('print') == 'true')
            window.addEventListener('load', function() {
                // Set the print document title dynamically from the page header
                const h1 = document.querySelector('.sticky h1') || document.querySelector('h1');
                if (h1 && h1.innerText.trim() !== '') {
                    document.getElementById('print-document-title').innerText = h1.innerText.trim();
                }
                setTimeout(function() {
                    window.print();
                }, 500);
            });
            @endif
        </script>
    </body>
</html>
