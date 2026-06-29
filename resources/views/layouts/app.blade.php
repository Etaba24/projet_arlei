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
               DARK THEME — « Midnight Ocean »
               Palette bleue nuit profonde, totalement différente
               du gris slate du mode clair.

               Niveaux de surface :
                 L0 bg     : #080c14  — fond de page
                 L1 surface: #0d1321  — cartes / tableaux
                 L2 raised : #111a2e  — modals / dropdowns
                 L3 input  : #0a1020  — champs de saisie
                 border    : #1c2d4a  — séparateurs
                 hover     : #152240  — survol de ligne

               Texte (bleu-blanc doux) :
                 h1 / strong : #dde8f8
                 body        : #9ab3d0
                 muted       : #4e6a8a
               ═══════════════════════════════════════════════════════ */

            :root[data-theme="dark"] {
                --dk-bg:      #080c14;
                --dk-surface: #0d1321;
                --dk-raised:  #111a2e;
                --dk-input:   #0a1020;
                --dk-border:  #1c2d4a;
                --dk-hover:   #152240;
                --dk-t1:      #dde8f8;
                --dk-t2:      #9ab3d0;
                --dk-t3:      #4e6a8a;
                --dk-accent:  #10b981;
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
            [data-theme="dark"] [class*="bg-white"] {
                background-color: var(--dk-surface) !important;
            }

            /* ── Niveaux gris clair → surface légèrement surélevée ── */
            [data-theme="dark"] .bg-slate-50,
            [data-theme="dark"] [class*="bg-slate-50"] {
                background-color: var(--dk-raised) !important;
            }
            [data-theme="dark"] .bg-slate-100,
            [data-theme="dark"] [class*="bg-slate-100"] {
                background-color: var(--dk-raised) !important;
            }

            /* ── Survols ── */
            [data-theme="dark"] [class*="hover:bg-slate-50"]:hover,
            [data-theme="dark"] [class*="hover:bg-white"]:hover  { background-color: var(--dk-hover) !important; }
            [data-theme="dark"] [class*="hover:bg-slate-100"]:hover { background-color: #172038 !important; }
            [data-theme="dark"] [class*="hover:bg-slate-200"]:hover { background-color: #1d2b42 !important; }

            /* ── Bordures ── */
            [data-theme="dark"] [class*="border-slate-50"]  { border-color: #111e34 !important; }
            [data-theme="dark"] [class*="border-slate-100"] { border-color: var(--dk-border) !important; }
            [data-theme="dark"] [class*="border-slate-200"] { border-color: #1e304d !important; }
            [data-theme="dark"] [class*="divide-"] > * + *  { border-color: var(--dk-border) !important; }

            /* ── Texte ── */
            [data-theme="dark"] [class*="text-slate-900"],
            [data-theme="dark"] [class*="text-slate-800"] { color: var(--dk-t1) !important; }
            [data-theme="dark"] [class*="text-slate-700"],
            [data-theme="dark"] [class*="text-slate-600"] { color: var(--dk-t2) !important; }
            [data-theme="dark"] [class*="text-slate-500"],
            [data-theme="dark"] [class*="text-slate-400"] { color: var(--dk-t3) !important; }

            /* ── Champs de saisie ── */
            [data-theme="dark"] input:not([type=checkbox]):not([type=radio]):not([type=color]):not([type=range]),
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
                background-color: var(--dk-input)  !important;
                color:            var(--dk-t1)      !important;
                border-color:     var(--dk-border)  !important;
            }
            [data-theme="dark"] input::placeholder,
            [data-theme="dark"] textarea::placeholder { color: var(--dk-t3) !important; opacity: 1; }
            [data-theme="dark"] input:focus,
            [data-theme="dark"] select:focus,
            [data-theme="dark"] textarea:focus {
                border-color: var(--dk-accent) !important;
                box-shadow: 0 0 0 3px rgba(16,185,129,.2) !important;
            }
            [data-theme="dark"] input:disabled { background-color: #0a1220 !important; color: var(--dk-t3) !important; }

            /* ── Tableaux ── */
            [data-theme="dark"] table            { background-color: var(--dk-surface) !important; }
            [data-theme="dark"] tbody tr:hover td { background-color: var(--dk-hover) !important; }
            [data-theme="dark"] td, [data-theme="dark"] th { border-color: var(--dk-border) !important; }

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
            [data-theme="dark"] *::-webkit-scrollbar-thumb:hover { background: #253f65; }

            /* ── Badges neutres slate ── */
            [data-theme="dark"] .bg-slate-100.text-slate-700,
            [data-theme="dark"] .bg-slate-100.text-slate-600 {
                background-color: #0e1e35 !important;
                color: #6b8fba !important;
            }

            /* ── Transition douce ── */
            [data-theme="dark"], [data-theme="dark"] * {
                transition: background-color .2s ease, border-color .2s ease, color .15s ease !important;
            }

            /* ── Mode clair : fond de page et body ── */
            body { background-color: #f8fafc; }
        </style>
    </head>

    <body class="h-full text-slate-800 antialiased"
          x-data="{
              sidebarOpen: false,
              dark: document.documentElement.getAttribute('data-theme') === 'dark',
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

        <div class="min-h-full flex">

            <!-- ── Sidebar Desktop ── -->
            <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 z-20 bg-slate-900 text-slate-300 border-r border-slate-800">
                <div class="flex items-center h-16 px-6 bg-slate-950 border-b border-slate-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <span class="p-2 bg-emerald-600 rounded-lg text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </span>
                        <span class="font-bold text-lg text-white tracking-wide">ARLEI</span>
                    </a>
                </div>

                <div class="flex-1 flex flex-col overflow-y-auto px-4 py-6 space-y-7">
                    @include('layouts.navigation-links')
                </div>

                <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="truncate max-w-[110px]">
                            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ Auth::user()->customRole?->name ?? Auth::user()->role }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <!-- Toggle dark -->
                        <button @click="toggleDark()"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                :title="dark ? 'Mode clair' : 'Mode sombre'">
                            <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="Déconnexion">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- ── Sidebar Mobile ── -->
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 md:hidden" style="display:none;">
                <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="sidebarOpen = false"></div>
                <div class="fixed inset-y-0 left-0 flex w-full max-w-xs flex-col bg-slate-900 text-slate-300">
                    <div class="flex items-center justify-between h-16 px-6 bg-slate-950 border-b border-slate-800">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                            <span class="p-2 bg-emerald-600 rounded-lg text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>
                            <span class="font-bold text-lg text-white">ARLEI</span>
                        </a>
                        <button @click="sidebarOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-7">
                        @include('layouts.navigation-links')
                    </div>
                    <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-400">{{ Auth::user()->customRole?->name ?? Auth::user()->role }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="toggleDark(); sidebarOpen = false"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                                <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Contenu principal ── -->
            <div class="flex-1 md:pl-64 flex flex-col min-h-screen">
                <!-- Top bar mobile -->
                <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow-sm md:hidden border-b border-slate-200">
                    <button @click="sidebarOpen = true" class="px-4 border-r border-slate-200 text-slate-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </button>
                    <div class="flex flex-1 justify-between px-4 items-center">
                        <span class="font-bold text-lg text-slate-900">ARLEI</span>
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-semibold text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    </div>
                </header>

                <!-- Header de page -->
                @isset($header)
                    <header class="bg-white border-b border-slate-200 py-6 px-6 sm:px-8">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Contenu -->
                <main class="flex-1 py-8 px-6 sm:px-8 bg-slate-50">
                    {{ $slot }}
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
                    Toast.fire({ icon: 'success', title: "{{ addslashes(session('status') ?? session('success')) }}", background: isDark ? '#0d1321' : '#fff', color: isDark ? '#dde8f8' : '#1e293b' });
                @endif
                @if(session('error'))
                    Toast.fire({ icon: 'error', title: "{{ addslashes(session('error')) }}", background: isDark ? '#0d1321' : '#fff', color: isDark ? '#dde8f8' : '#1e293b' });
                @endif
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
                    background: isDark ? '#111a2e' : '#ffffff',
                    color:      isDark ? '#dde8f8' : '#1e293b',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                        cancelButton:  'rounded-xl font-bold px-6 py-2.5',
                    }
                }).then(r => { if(r.isConfirmed) form.submit(); });
            }
        </script>
    </body>
</html>
