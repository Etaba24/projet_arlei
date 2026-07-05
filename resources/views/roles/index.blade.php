<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start md:items-center flex-col md:flex-row w-full gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rôles & Permissions</h1>
                <p class="text-sm text-slate-500 mt-1">Créez des rôles personnalisés et assignez-leur des permissions par fonctionnalité.</p>
            </div>
            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('users.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Comptes
                </a>
                <button onclick="document.getElementById('modal-create-role').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Rôle
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $totalPerms = $permissions->flatten()->count();
        $groupColors = [
            'Production'          => ['dot'=>'bg-emerald-500','text'=>'text-emerald-700','bg'=>'bg-emerald-50', 'border'=>'border-emerald-200','badge'=>'bg-emerald-100'],
            'Stocks & Ressources' => ['dot'=>'bg-blue-500',   'text'=>'text-blue-700',   'bg'=>'bg-blue-50',   'border'=>'border-blue-200',  'badge'=>'bg-blue-100'],
            'Logistique'          => ['dot'=>'bg-amber-500',  'text'=>'text-amber-700',  'bg'=>'bg-amber-50',  'border'=>'border-amber-200', 'badge'=>'bg-amber-100'],
            'RH & Organisation'   => ['dot'=>'bg-violet-500', 'text'=>'text-violet-700', 'bg'=>'bg-violet-50', 'border'=>'border-violet-200','badge'=>'bg-violet-100'],
            'Administration'      => ['dot'=>'bg-rose-500',   'text'=>'text-rose-700',   'bg'=>'bg-rose-50',   'border'=>'border-rose-200',  'badge'=>'bg-rose-100'],
        ];
    @endphp

    @if(session('status'))
        <div class="max-w-6xl mx-auto mb-4 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3 text-sm font-semibold text-emerald-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-6xl mx-auto mb-4 bg-rose-50 border border-rose-200 rounded-2xl px-5 py-3 text-sm font-semibold text-rose-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="max-w-6xl mx-auto space-y-8">

        {{-- ── STATS ── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ $roles->count() }}</p>
                    <p class="text-xs text-slate-400 font-semibold">Rôles au total</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ $roles->where('is_system', false)->count() }}</p>
                    <p class="text-xs text-slate-400 font-semibold">Rôles personnalisés</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ $totalPerms }}</p>
                    <p class="text-xs text-slate-400 font-semibold">Permissions disponibles</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-900">{{ $permissions->count() }}</p>
                    <p class="text-xs text-slate-400 font-semibold">Groupes de permissions</p>
                </div>
            </div>
        </div>

        {{-- ── ROLES GRID ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($roles as $role)
            @php
                $permCount    = $role->permissions->count();
                $permSlugs    = $role->permissions->pluck('slug');
                $coverageRate = $totalPerms > 0 ? round($permCount / $totalPerms * 100) : 0;
                $popupId      = 'popup-role-' . $role->id;
            @endphp

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                {{-- ── Bande colorée en haut ── --}}
                <div class="h-2 w-full" style="background-color: {{ $role->couleur }}"></div>

                <div class="p-7">

                    {{-- Ligne principale : avatar + nom + actions --}}
                    <div class="flex items-start gap-5">

                        {{-- Avatar coloré --}}
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 text-2xl font-black"
                             style="background-color: {{ $role->couleur }}18; color: {{ $role->couleur }}">
                            {{ strtoupper(mb_substr($role->name, 0, 1)) }}
                        </div>

                        {{-- Infos --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-slate-900">{{ $role->name }}</h3>
                                @if($role->is_system)
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full"
                                          style="background-color: {{ $role->couleur }}18; color: {{ $role->couleur }}">
                                        Système
                                    </span>
                                @endif
                            </div>
                            @if($role->description)
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ $role->description }}</p>
                            @else
                                <p class="text-sm text-slate-300 mt-1 italic">Aucune description</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Info popup --}}
                            <button onclick="openPermPopup('{{ $popupId }}')"
                                    title="Voir les permissions"
                                    class="p-2 rounded-xl text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            {{-- Edit --}}
                            <button onclick="openEditModal('{{ $role->uuid }}', '{{ addslashes($role->name) }}', '{{ $role->couleur }}', '{{ addslashes($role->description ?? '') }}', {{ $role->permissions->pluck('id')->toJson() }})"
                                    title="Modifier"
                                    class="p-2 rounded-xl text-slate-400 hover:text-violet-600 hover:bg-violet-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            {{-- Delete --}}
                            @if(!$role->is_system)
                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                  onsubmit="return confirm('Supprimer le rôle « {{ $role->name }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                        class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- Séparateur --}}
                    <div class="border-t border-slate-100 my-5"></div>

                    {{-- Stats row --}}
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-2xl font-black" style="color: {{ $role->couleur }}">{{ $permCount }}</p>
                            <p class="text-xs text-slate-400 font-semibold mt-0.5">Permissions</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-slate-800">{{ $role->users_count }}</p>
                            <p class="text-xs text-slate-400 font-semibold mt-0.5">Utilisateurs</p>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-slate-400 font-semibold">Couverture</span>
                                <span class="text-xs font-black" style="color: {{ $role->couleur }}">{{ $coverageRate }}%</span>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width: {{ $coverageRate }}%; background-color: {{ $role->couleur }}"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Groupes présents --}}
                    <div class="flex flex-wrap gap-2 mt-5">
                        @foreach($permissions as $groupe => $perms)
                            @php
                                $gc = $groupColors[$groupe] ?? ['dot'=>'bg-slate-400','text'=>'text-slate-600','badge'=>'bg-slate-100'];
                                $count = $perms->filter(fn($p) => $permSlugs->contains($p->slug))->count();
                            @endphp
                            @if($count > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $gc['badge'] }} {{ $gc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $gc['dot'] }}"></span>
                                {{ $groupe }}
                                <span class="font-black">{{ $count }}</span>
                            </span>
                            @endif
                        @endforeach
                        @if($permCount === 0)
                            <span class="text-xs text-slate-300 italic">Aucune permission assignée</span>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ──────────── POPUP PERMISSIONS (caché, révélé par JS) ──────────── --}}
            <div id="{{ $popupId }}"
                 class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4"
                 onclick="if(event.target===this) closePermPopup('{{ $popupId }}')">

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

                {{-- Popup card --}}
                <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden"
                     style="max-height: 85vh">

                    {{-- Header coloré --}}
                    <div class="px-6 py-5 flex items-center justify-between"
                         style="background-color: {{ $role->couleur }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-lg">
                                {{ strtoupper(mb_substr($role->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">{{ $role->name }}</h3>
                                <p class="text-xs text-white/70 mt-0.5">{{ $permCount }} permission{{ $permCount > 1 ? 's' : '' }} assignée{{ $permCount > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <button onclick="closePermPopup('{{ $popupId }}')"
                                class="p-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Permissions body --}}
                    <div class="overflow-y-auto p-6 space-y-5" style="max-height: calc(85vh - 88px)">
                        @if($role->permissions->isEmpty())
                            <div class="text-center py-10">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">Aucune permission assignée</p>
                            </div>
                        @else
                            @foreach($permissions as $groupe => $perms)
                                @php
                                    $gc          = $groupColors[$groupe] ?? ['dot'=>'bg-slate-400','text'=>'text-slate-600','bg'=>'bg-slate-50','border'=>'border-slate-200','badge'=>'bg-slate-100'];
                                    $activePerms = $perms->filter(fn($p) => $permSlugs->contains($p->slug));
                                    $inactivePerms = $perms->filter(fn($p) => !$permSlugs->contains($p->slug));
                                @endphp
                                @if($activePerms->isNotEmpty())
                                <div class="rounded-2xl border {{ $gc['border'] }} overflow-hidden">
                                    {{-- Group header --}}
                                    <div class="px-4 py-3 {{ $gc['bg'] }} border-b {{ $gc['border'] }} flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $gc['dot'] }}"></span>
                                            <span class="text-xs font-black {{ $gc['text'] }} uppercase tracking-widest">{{ $groupe }}</span>
                                        </div>
                                        <span class="text-xs font-bold {{ $gc['text'] }}">
                                            {{ $activePerms->count() }}/{{ $perms->count() }}
                                        </span>
                                    </div>
                                    {{-- Permission list --}}
                                    <div class="p-3 space-y-1">
                                        @foreach($activePerms as $perm)
                                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl {{ $gc['bg'] }}">
                                            <svg class="w-3.5 h-3.5 shrink-0 {{ $gc['text'] }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-slate-800">{{ $perm->name }}</span>
                                            <span class="ml-auto text-[10px] text-slate-400 font-mono shrink-0 hidden sm:block">{{ $perm->slug }}</span>
                                        </div>
                                        @endforeach
                                        @foreach($inactivePerms as $perm)
                                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl opacity-40">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span class="text-xs text-slate-400 line-through">{{ $perm->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">
                            <strong class="text-slate-600">{{ $coverageRate }}%</strong> des permissions couvertes
                        </span>
                        <button onclick="closePermPopup('{{ $popupId }}')"
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
            {{-- /popup --}}

            @endforeach

            {{-- ── Carte "Nouveau rôle" ── --}}
            <button onclick="document.getElementById('modal-create-role').classList.remove('hidden')"
                    class="border-2 border-dashed border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 rounded-2xl p-10 flex flex-col items-center justify-center gap-4 text-slate-400 hover:text-slate-600 transition-all group">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold">Créer un nouveau rôle</p>
                    <p class="text-sm mt-1">Définissez un rôle et ses permissions</p>
                </div>
            </button>
        </div>

    </div>


    {{-- ═══════════ MODAL CRÉATION ═══════════ --}}
    <div id="modal-create-role"
         class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 py-8 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         onclick="if(event.target===this) document.getElementById('modal-create-role').classList.add('hidden')">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-slate-200">
            <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Nouveau Rôle</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Définissez le nom, la couleur et les permissions</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-create-role').classList.add('hidden')"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                @include('roles._form', ['role' => null, 'rolePerms' => collect(), 'permissions' => $permissions])
            </form>
        </div>
    </div>

    {{-- ═══════════ MODAL ÉDITION ═══════════ --}}
    <div id="modal-edit-role"
         class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 py-8 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         onclick="if(event.target===this) document.getElementById('modal-edit-role').classList.add('hidden')">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-slate-200">
            <div class="px-7 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Modifier le Rôle</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Modifiez les informations et les permissions</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-edit-role').classList.add('hidden')"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-role-form" method="POST">
                @csrf @method('PUT')
                @include('roles._form', ['role' => null, 'rolePerms' => collect(), 'permissions' => $permissions, 'isEdit' => true])
            </form>
        </div>
    </div>

    <script>
    function openPermPopup(id) {
        const popup = document.getElementById(id);
        if (!popup) return;
        popup.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Animate in
        const card = popup.querySelector('.relative.bg-white');
        card.style.transform = 'scale(0.95) translateY(8px)';
        card.style.opacity   = '0';
        card.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
        requestAnimationFrame(() => {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.opacity   = '1';
        });
    }

    function closePermPopup(id) {
        const popup = document.getElementById(id);
        if (!popup) return;
        const card = popup.querySelector('.relative.bg-white');
        card.style.transform = 'scale(0.95) translateY(8px)';
        card.style.opacity   = '0';
        setTimeout(() => {
            popup.classList.add('hidden');
            document.body.style.overflow = '';
        }, 180);
    }

    function openEditModal(id, name, couleur, description, permIds) {
        const modal = document.getElementById('modal-edit-role');
        const form  = document.getElementById('edit-role-form');

        form.action = '/roles/' + id;
        form.querySelector('[name="name"]').value        = name;
        form.querySelector('[name="couleur"]').value     = couleur;
        form.querySelector('[name="description"]').value = description;

        // Use Alpine component's resetToIds if available
        const alpineRoot = form.querySelector('[x-data]');
        if (alpineRoot && alpineRoot._x_dataStack) {
            const comp = alpineRoot._x_dataStack[0];
            if (comp && typeof comp.resetToIds === 'function') {
                comp.resetToIds(permIds);
                comp.couleur = couleur;
                modal.classList.remove('hidden');
                return;
            }
        }
        // Fallback: direct DOM
        form.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = permIds.includes(parseInt(cb.value));
        });
        form.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.dispatchEvent(new Event('change')));
        modal.classList.remove('hidden');
    }

    // Close popups on Escape key
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[id^="popup-role-"]:not(.hidden)').forEach(p => closePermPopup(p.id));
        ['modal-create-role','modal-edit-role'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
    });
    </script>
</x-app-layout>
