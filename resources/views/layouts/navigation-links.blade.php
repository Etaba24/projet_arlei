@php $nav = Auth::user(); @endphp

<div class="space-y-6">
    <div>
        <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Navigation</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('dashboard') }}"
               :class="sidebarCollapsed ? 'justify-center' : ''"
               :title="sidebarCollapsed ? 'Tableau de Bord' : null"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Tableau de Bord</span>
            </a>
        </div>
    </div>

    @if ($nav->hasAdminInterface())

        {{-- ── Production ── --}}
        @if ($nav->hasPermission('production.voir') || $nav->hasPermission('production.suivi') || $nav->hasPermission('production.creer'))
        <div>
            <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cœur Métier</p>
            <div class="mt-2 space-y-1">
                @if ($nav->hasPermission('production.suivi'))
                <a href="{{ route('suivi-production.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Suivi Production' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('suivi-production.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Suivi Production</span>
                </a>
                @endif

                @if ($nav->hasPermission('production.voir'))
                <a href="{{ route('ordre-productions.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Ordres de Production' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('ordre-productions.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Ordres de Production</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Stocks & Tiers ── --}}
        @if ($nav->hasPermission('stocks.voir') || $nav->hasPermission('stocks.matieres-premieres') || $nav->hasPermission('stocks.semi-finis') || $nav->hasPermission('stocks.produits-finis') || $nav->hasPermission('logistique.fournisseurs') || $nav->hasPermission('logistique.clients'))
        <div>
            <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stocks & Tiers</p>
            <div class="mt-2 space-y-1">
                @if ($nav->hasPermission('stocks.matieres-premieres') || $nav->hasPermission('stocks.voir'))
                <a href="{{ route('matieres-premieres.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Matières Premières' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('matieres-premieres.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Matières Premières</span>
                </a>
                @endif

                @if ($nav->hasPermission('stocks.semi-finis') || $nav->hasPermission('stocks.voir'))
                <a href="{{ route('produits-semi-finis.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Produits Semi-Finis' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('produits-semi-finis.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Produits Semi-Finis</span>
                </a>
                @endif

                @if ($nav->hasPermission('stocks.produits-finis') || $nav->hasPermission('stocks.voir'))
                <a href="{{ route('produits-finis.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Produits Finis' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('produits-finis.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Produits Finis</span>
                </a>
                @endif

                @if ($nav->hasPermission('logistique.fournisseurs') || $nav->hasPermission('logistique.voir'))
                <a href="{{ route('fournisseurs.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Fournisseurs' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('fournisseurs.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Fournisseurs</span>
                </a>
                @endif

                @if ($nav->hasPermission('logistique.clients') || $nav->hasPermission('logistique.voir'))
                <a href="{{ route('clients.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Clients' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('clients.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Clients</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Logistique ── --}}
        @if ($nav->hasPermission('logistique.mp') || $nav->hasPermission('logistique.pf') || $nav->hasPermission('logistique.voir'))
        <div>
            <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Logistique</p>
            <div class="mt-2 space-y-1">
                @if ($nav->hasPermission('logistique.mp') || $nav->hasPermission('logistique.voir'))
                <a href="{{ route('logistique.mp') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Entrées (Matières Prem.)' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('logistique.mp*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Entrées (Matières Prem.)</span>
                </a>
                @endif

                @if ($nav->hasPermission('logistique.pf') || $nav->hasPermission('logistique.voir'))
                <a href="{{ route('logistique.pf') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Sorties (Prod. Finis)' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('logistique.pf*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Sorties (Prod. Finis)</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Configuration industrielle ── --}}
        @if ($nav->hasPermission('stocks.machines') || $nav->hasPermission('stocks.transformations') || $nav->hasPermission('stocks.voir'))
        <div>
            <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Configuration</p>
            <div class="mt-2 space-y-1">
                @if ($nav->hasPermission('stocks.machines') || $nav->hasPermission('stocks.voir'))
                <a href="{{ route('machines.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Machines' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('machines.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Machines</span>
                </a>
                @endif

                @if ($nav->hasPermission('stocks.transformations') || $nav->hasPermission('stocks.voir'))
                <a href="{{ route('transformations.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Transformations' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('transformations.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Transformations</span>
                </a>

                <a href="{{ route('conditionnements.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Conditionnements' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('conditionnements.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Conditionnements</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── RH & Administration ── --}}
        @if ($nav->hasPermission('rh.employes') || $nav->hasPermission('rh.equipes') || $nav->hasPermission('rh.voir') || $nav->hasPermission('admin.utilisateurs') || $nav->hasPermission('admin.roles') || $nav->hasPermission('admin.voir'))
        <div>
            <p x-show="!sidebarCollapsed" x-transition.opacity class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ressources Humaines</p>
            <div class="mt-2 space-y-1">
                @if ($nav->hasPermission('rh.equipes') || $nav->hasPermission('rh.voir'))
                <a href="{{ route('equipes.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Départements & Équipes' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('equipes.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Départements & Équipes</span>
                </a>
                @endif

                @if ($nav->hasPermission('rh.employes') || $nav->hasPermission('rh.voir'))
                <a href="{{ route('employes.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Employés' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('employes.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Employés</span>
                </a>
                @endif

                @if ($nav->hasPermission('admin.utilisateurs'))
                <a href="{{ route('users.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Comptes' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Comptes</span>
                </a>
                @endif

                @if ($nav->hasPermission('admin.roles'))
                <a href="{{ route('roles.index') }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Rôles' : null"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('roles.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Rôles</span>
                </a>
                @endif
            </div>
        </div>
        @endif

    @endif
</div>
