<x-app-layout>
    <div class="space-y-8"
         x-data="{
            openCreate: {{ $errors->hasBag('create') ? 'true' : 'false' }},
            openEdit: {{ $errors->hasBag('edit') ? 'true' : 'false' }},
            editUserId: '{{ old('user_edit_id') }}',
            editFormAction: '{{ old('user_edit_id') ? route('users.update', old('user_edit_id')) : '' }}',
            user: {
                id: '{{ old('user_edit_id') }}',
                name: '{{ $errors->hasBag('edit') ? addslashes(old('name')) : '' }}',
                email: '{{ $errors->hasBag('edit') ? old('email') : '' }}',
                role: '{{ $errors->hasBag('edit') ? old('role') : '' }}',
                role_id: '{{ $errors->hasBag('edit') ? old('role_id') : '' }}'
            }
         }"
         @open-create-user.window="openCreate = true">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestion des Comptes</h1>
                    <p class="text-sm text-slate-500 mt-1">Administrez les accès à la plateforme : création, modification et suppression des comptes utilisateurs.</p>
                </div>
            <form method="GET" action="{{ route('users.index') }}" class="relative w-full md:w-80 ml-0 md:ml-8">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher dans le tableau..."
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </form>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('roles.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Rôles & Permissions
                    </a>
                    <button @click="$dispatch('open-create-user')"
                            class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-emerald-600/10 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau Compte
                    </button>
                </div>
            </div>
        </x-slot>

        {{-- ─── ALERTES ─────────────────────────────────── --}}
        @if(session('status'))
            <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- ─── TABLEAU DES COMPTES ─────────────────────────────────── --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-emerald-600 text-white uppercase text-xxs font-semibold border-b border-emerald-700">
                            <th class="py-3 px-6">Nom complet</th>
                            <th class="py-3 px-6">Adresse email</th>
                            <th class="py-3 px-6">Rôle</th>
                            <th class="py-3 px-6">Employé associé</th>
                            <th class="py-3 px-6">Dernière MàJ</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $u->id === auth()->id() ? 'bg-emerald-50/50' : '' }}">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                            {{ $u->isAdmin() ? 'bg-violet-100 text-violet-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-800">{{ $u->name }}</span>
                                            @if($u->id === auth()->id())
                                                <span class="ml-2 text-xs text-emerald-600 font-semibold">(Vous)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-mono text-xs">{{ $u->email }}</td>
                                <td class="py-4 px-6">
                                    @if($u->customRole)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold ring-1 ring-inset"
                                              style="background-color:{{ $u->customRole->couleur }}18; color:{{ $u->customRole->couleur }}; ring-color:{{ $u->customRole->couleur }}33">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color:{{ $u->customRole->couleur }}"></span>
                                            {{ $u->customRole->name }}
                                        </span>
                                    @elseif($u->isAdmin())
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-violet-100 text-violet-700 ring-1 ring-inset ring-violet-600/10">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                            Administrateur
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                            Opérateur
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($u->employe)
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-700 font-semibold">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            {{ $u->employe->nom_complet }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Non associé</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-400">{{ $u->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                    {{-- Bouton Éditer --}}
                                    <button type="button"
                                            @click="
                                                editFormAction = '{{ route('users.update', $u) }}';
                                                user = {
                                                    id: '{{ $u->uuid }}',
                                                    name: '{{ addslashes($u->name) }}',
                                                    email: '{{ $u->email }}',
                                                    role: '{{ $u->role }}',
                                                    role_id: '{{ $u->role_id ?? '' }}'
                                                };
                                                openEdit = true;
                                            "
                                            class="inline-flex items-center justify-center p-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-500 hover:text-slate-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Bouton Supprimer --}}
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg text-rose-500 hover:text-rose-700 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center p-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-300 cursor-not-allowed" title="Impossible de supprimer votre propre compte">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 font-medium italic">Aucun compte utilisateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- ─── MODAL CRÉATION ─────────────────────────────────── --}}
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openCreate" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in" @click="openCreate = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col animate-modal-pop">
                {{-- En-tête --}}
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Créer un Compte</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Le nouveau compte aura accès à la plateforme dès sa création.</p>
                    </div>
                    <button @click="openCreate = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Formulaire --}}
                <form action="{{ route('users.store') }}" method="POST" class="p-6 space-y-5 overflow-y-auto">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nom complet <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required
                               placeholder="ex: Jean Dupont"
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               value="{{ $errors->hasBag('create') ? old('name') : '' }}" />
                        @error('name', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required
                               placeholder="ex: jean.dupont@agroprod.cm"
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               value="{{ $errors->hasBag('create') ? old('email') : '' }}" />
                        @error('email', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required
                                   placeholder="Minimum 6 caractères"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 pr-11" />
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Rôle personnalisé <span class="text-rose-500">*</span></label>
                        <select name="role_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Sélectionnez un rôle —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }}{{ $r->is_system ? ' ✦' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-400">Les rôles marqués ✦ sont les rôles système par défaut</p>
                    </div>
                    {{-- Legacy role hidden — inferred from role_id in controller --}}
                    <input type="hidden" name="role" value="operateur">

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                        <button type="button" @click="openCreate = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm">Créer le compte</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─── MODAL ÉDITION ─────────────────────────────────── --}}
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openEdit" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in" @click="openEdit = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col animate-modal-pop">
                {{-- En-tête --}}
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Modifier le Compte</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Modifiez l'email, le nom ou le mot de passe du compte.</p>
                    </div>
                    <button @click="openEdit = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Formulaire --}}
                <form :action="editFormAction" method="POST" class="p-6 space-y-5 overflow-y-auto">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_edit_id" x-model="user.id" />

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nom complet <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required x-model="user.name"
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                        @error('name', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required x-model="user.email"
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                        @error('email', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bloc changement de mot de passe --}}
                    <div x-data="{ changePassword: false }" class="rounded-2xl border border-slate-200 overflow-hidden">
                        {{-- Toggle --}}
                        <button type="button" @click="changePassword = !changePassword"
                                class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100 transition-colors text-left">
                            <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Changer le mot de passe
                            </span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="changePassword ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Champs visibles uniquement si toggle actif --}}
                        <div x-show="changePassword" x-collapse x-cloak class="px-4 pb-4 pt-3 space-y-4 border-t border-slate-200">
                            <p class="text-xs text-slate-500">Renseignez l'ancien mot de passe pour confirmer l'identité, puis saisissez le nouveau.</p>

                            <div x-data="{ showCurrent: false }">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Ancien mot de passe <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showCurrent ? 'text' : 'password'" name="current_password"
                                           placeholder="Saisir l'ancien mot de passe..."
                                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 pr-11" />
                                    <button type="button" @click="showCurrent = !showCurrent"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('current_password', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div x-data="{ showNew: false }">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    Nouveau mot de passe <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showNew ? 'text' : 'password'" name="password"
                                           placeholder="Minimum 6 caractères"
                                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 pr-11" />
                                    <button type="button" @click="showNew = !showNew"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Rôle personnalisé <span class="text-rose-500">*</span></label>
                        <select name="role_id" x-model="user.role_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Sélectionnez un rôle —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">
                                    {{ $r->name }}{{ $r->is_system ? ' ✦' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <input type="hidden" name="role" value="operateur">

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                        <button type="button" @click="openEdit = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
