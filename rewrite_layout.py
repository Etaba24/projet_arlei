import re

with open('/home/etaba/Bureau/production/resources/views/layouts/app.blade.php', 'r') as f:
    content = f.read()

# 1. FIX CSS WILDCARDS
content = content.replace('[data-theme="dark"] .bg-white,\n            [data-theme="dark"] [class*="bg-white"]', '[data-theme="dark"] .bg-white')
content = content.replace('[data-theme="dark"] .bg-slate-50,\n            [data-theme="dark"] [class*="bg-slate-50"]', '[data-theme="dark"] .bg-slate-50')
content = content.replace('[data-theme="dark"] .bg-slate-100,\n            [data-theme="dark"] [class*="bg-slate-100"]', '[data-theme="dark"] .bg-slate-100')
content = content.replace('[data-theme="dark"] [class*="border-slate-50"]', '[data-theme="dark"] .border-slate-50')
content = content.replace('[data-theme="dark"] [class*="border-slate-100"]', '[data-theme="dark"] .border-slate-100')
content = content.replace('[data-theme="dark"] [class*="border-slate-200"]', '[data-theme="dark"] .border-slate-200')
content = content.replace('[data-theme="dark"] [class*="divide-"] > * + *', '[data-theme="dark"] .divide-y > * + * {\n                border-color: var(--dk-border) !important;\n            }\n            [data-theme="dark"] .divide-x > * + *')
content = content.replace('[data-theme="dark"] [class*="text-slate-900"],\n            [data-theme="dark"] [class*="text-slate-800"]', '[data-theme="dark"] .text-slate-900,\n            [data-theme="dark"] .text-slate-800')
content = content.replace('[data-theme="dark"] [class*="text-slate-700"],\n            [data-theme="dark"] [class*="text-slate-600"]', '[data-theme="dark"] .text-slate-700,\n            [data-theme="dark"] .text-slate-600')
content = content.replace('[data-theme="dark"] [class*="text-slate-500"],\n            [data-theme="dark"] [class*="text-slate-400"]', '[data-theme="dark"] .text-slate-500,\n            [data-theme="dark"] .text-slate-400')

new_layout = """
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
                                                $lines = explode("\\n", $notif->observations);
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
                    <div class="max-w-7xl mx-auto w-full">
"""

content = re.sub(r'<div class="min-h-full flex">.*<!-- Contenu -->\n\s*<main class="flex-1 py-8 px-6 sm:px-8 bg-slate-50">\n\s*\{\{ \$slot \}\}', new_layout + '                        {{ $slot }}\n                    </div>', content, flags=re.DOTALL)

with open('/home/etaba/Bureau/production/resources/views/layouts/app.blade.php', 'w') as f:
    f.write(content)

print("Rewritten fully.")
