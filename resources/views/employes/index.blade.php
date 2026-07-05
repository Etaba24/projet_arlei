<x-app-layout>
    <div class="space-y-8" 
         x-data="{ 
            openCreate: {{ $errors->hasBag('create') ? 'true' : 'false' }}, 
            openEdit: {{ $errors->hasBag('edit') ? 'true' : 'false' }},
            editFormAction: '{{ old('employe_id') ? route('employes.update', old('employe_id')) : '' }}',
            employe: { 
                id: '{{ old('employe_id') }}',
                matricule: '{{ $errors->hasBag('edit') ? old('matricule') : '' }}', 
                nom: '{{ $errors->hasBag('edit') ? addslashes(old('nom')) : '' }}', 
                prenom: '{{ $errors->hasBag('edit') ? addslashes(old('prenom')) : '' }}', 
                residence: '{{ $errors->hasBag('edit') ? addslashes(old('residence')) : '' }}', 
                fonction: '{{ $errors->hasBag('edit') ? addslashes(old('fonction')) : '' }}', 
                telephone: '{{ $errors->hasBag('edit') ? old('telephone') : '' }}', 
                email: '{{ $errors->hasBag('edit') ? old('email') : '' }}', 
                departement_id: '{{ $errors->hasBag('edit') ? old('departement_id') : '' }}', 
                equipe_id: '{{ $errors->hasBag('edit') ? old('equipe_id') : '' }}', 
                user_id: '{{ $errors->hasBag('edit') ? old('user_id') : '' }}' 
            }
         }"
         @open-create.window="openCreate = true">
        
        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center w-full gap-3 flex-wrap">
                <div class="shrink-0">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestion des Employés</h1>
                    <p class="text-sm text-slate-500 mt-1">Registre du personnel, affectations aux équipes et liaisons de compte.</p>
                </div>

                {{-- ── Filtres instantanés ── --}}
                <form id="filter-form" method="GET" action="{{ route('employes.index') }}"
                      class="flex flex-1 flex-wrap items-center gap-2 ml-0 md:ml-6">

                    {{-- Recherche texte --}}
                    <div class="relative flex-1 min-w-[180px] max-w-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Nom, matricule, fonction…"
                               x-on:input.debounce.350ms="$el.form.submit()"
                               class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    </div>

                    {{-- Filtre département --}}
                    <div class="relative min-w-[180px]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <select name="departement_id"
                                x-on:change="$el.form.submit()"
                                class="w-full pl-10 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none appearance-none transition-all">
                            <option value="">Tous les départements</option>
                            @foreach($departements as $dep)
                                <option value="{{ $dep->id }}" {{ request('departement_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->designation }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Effacer les filtres si actifs --}}
                    @if(request('search') || request('departement_id'))
                        <a href="{{ route('employes.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-500 hover:text-rose-600 bg-slate-100 hover:bg-rose-50 rounded-xl border border-slate-200 hover:border-rose-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Effacer
                        </a>
                    @endif
                </form>

                <div class="shrink-0">
                    @can('create', App\Models\Employe::class)
                    @endcan
                    @if(Auth::user()->hasPermission('rh.employes'))
                    <button @click="$dispatch('open-create')"
                            class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-emerald-600/10 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvel Employé
                    </button>
                    @endif
                </div>
            </div>
        </x-slot>

        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-emerald-600 text-white uppercase text-xxs font-semibold border-b border-emerald-700">
                            <th class="py-3 px-6">Matricule</th>
                            <th class="py-3 px-6">Identité</th>
                            <th class="py-3 px-6">Département / Équipe</th>
                            <th class="py-3 px-6">Fonction</th>
                            <th class="py-3 px-6">Téléphone / Email</th>
                            <th class="py-3 px-6">Compte Utilisateur</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($employes as $emp)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-mono font-semibold text-slate-900">{{ $emp->matricule }}</td>
                                <td class="py-4 px-6 font-medium text-slate-700">{{ $emp->nom }} {{ $emp->prenom }}</td>
                                <td class="py-4 px-6">
                                    <div class="text-slate-900 font-semibold">{{ $emp->departement->designation ?? 'N/A' }}</div>
                                    <div class="text-xs text-emerald-600 font-medium">{{ $emp->equipe->nom ?? 'N/A' }}</div>
                                </td>
                                <td class="py-4 px-6 text-slate-500 font-semibold">{{ $emp->fonction }}</td>
                                <td class="py-4 px-6">
                                    <div class="text-slate-700 font-semibold">{{ $emp->telephone ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $emp->email ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($emp->user)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10">Lié : {{ $emp->user->email }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Aucun</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                    <button type="button" 
                                            @click="
                                                editFormAction = '{{ route('employes.update', $emp) }}';
                                                employe = {
                                                    matricule: '{{ $emp->matricule }}',
                                                    id: '{{ $emp->uuid }}',
                                                    nom: '{{ addslashes($emp->nom) }}',
                                                    prenom: '{{ addslashes($emp->prenom) }}',
                                                    residence: '{{ addslashes($emp->residence) }}',
                                                    fonction: '{{ addslashes($emp->fonction) }}',
                                                    telephone: '{{ $emp->telephone }}',
                                                    email: '{{ $emp->email }}',
                                                    departement_id: '{{ $emp->departement_id }}',
                                                    equipe_id: '{{ $emp->equipe_id }}',
                                                    user_id: '{{ $emp->user_id }}'
                                                };
                                                openEdit = true;
                                            "
                                            class="inline-flex items-center justify-center p-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-500 hover:text-slate-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    
                                    <form action="{{ route('employes.destroy', $emp) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this);">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg text-rose-500 hover:text-rose-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium italic">Aucun employé répertorié.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($employes->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $employes->links() }}
                </div>
            @endif
        </div>

        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openCreate" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in" @click="openCreate = false"></div>
            
            <div class="relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden max-h-[90vh] flex flex-col animate-modal-pop">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Ajouter un Employé</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Le matricule sera automatiquement généré lors de la création.</p>
                    </div>
                    <button @click="openCreate = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form action="{{ route('employes.store') }}" method="POST" class="p-6 overflow-y-auto space-y-4 flex-1">
                    @csrf

                    @if($errors->hasBag('create') && $errors->getBag('create')->any())
                        <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm font-semibold text-rose-700">
                            {{ $errors->getBag('create')->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nom de famille <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('nom') : '' }}" />
                            @error('nom', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Prénom <span class="text-rose-500">*</span></label>
                            <input type="text" name="prenom" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('prenom') : '' }}" />
                            @error('prenom', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse de résidence</label>
                            <input type="text" name="residence" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('residence') : '' }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Fonction / Poste <span class="text-rose-500">*</span></label>
                            <input type="text" name="fonction" required placeholder="ex: Opérateur Balance" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('fonction') : '' }}" />
                            @error('fonction', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Téléphone</label>
                            <input type="text" name="telephone" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('telephone') : '' }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse Email Professionnelle</label>
                            <input type="email" name="email" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ $errors->hasBag('create') ? old('email') : '' }}" />
                            @error('email', 'create') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Département d'Affectation <span class="text-rose-500">*</span></label>
                            <select name="departement_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Sélectionner...</option>
                                @foreach ($departements as $dep)
                                    <option value="{{ $dep->id }}" {{ old('departement_id') == $dep->id ? 'selected' : '' }}>{{ $dep->designation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Équipe Assignée <span class="text-rose-500">*</span></label>
                            <select name="equipe_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Sélectionner...</option>
                                @foreach ($equipes as $eq)
                                    <option value="{{ $eq->id }}" {{ old('equipe_id') == $eq->id ? 'selected' : '' }}>{{ $eq->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Compte Utilisateur Associé (optionnel)</label>
                        <select name="user_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Aucun compte (Employé simple)</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ ($errors->hasBag('create') ? old('user_id') : '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }}) {{ $u->employe ? '- Déjà lié' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id', 'create') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ createAccount: {{ old('create_user_account') ? 'true' : 'false' }} }" class="space-y-4 pt-2 border-t border-slate-100">
                        <div class="flex items-center">
                            <input id="create_user_account_create" name="create_user_account" type="checkbox" value="1" x-model="createAccount" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                            <label for="create_user_account_create" class="ml-2 block text-sm font-semibold text-slate-700">Créer un nouveau compte utilisateur pour cet employé</label>
                        </div>
                        
                        <div x-show="createAccount" x-cloak class="space-y-4" x-transition>
                            <p class="text-xs text-slate-500">Un compte sera créé avec le nom et l'adresse email de cet employé. Renseignez le mot de passe ci-dessous :</p>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe du compte <span class="text-rose-500">*</span></label>
                                <input type="password" name="password" ::required="createAccount" placeholder="Min. 6 caractères" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('password') }}" />
                                @error('password', 'create') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3 bg-white sticky bottom-0">
                        <button type="button" @click="openCreate = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm">Créer l'employé</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openEdit" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in" @click="openEdit = false"></div>
            
            <div class="relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden max-h-[90vh] flex flex-col animate-modal-pop">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Modifier la Fiche Employé</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Édition de l'identifiant matricule : <span class="font-mono font-bold text-emerald-700" x-text="employe.matricule"></span></p>
                    </div>
                    <button @click="openEdit = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form :action="editFormAction" method="POST" class="p-6 overflow-y-auto space-y-4 flex-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="employe_id" x-model="employe.id" />
                    <input type="hidden" name="matricule" x-model="employe.matricule" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nom de famille <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" required x-model="employe.nom" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                            @error('nom', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Prénom <span class="text-rose-500">*</span></label>
                            <input type="text" name="prenom" required x-model="employe.prenom" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                            @error('prenom', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse de résidence</label>
                            <input type="text" name="residence" x-model="employe.residence" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Fonction / Poste <span class="text-rose-500">*</span></label>
                            <input type="text" name="fonction" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" x-model="employe.fonction" />
                            @error('fonction', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Téléphone</label>
                            <input type="text" name="telephone" x-model="employe.telephone" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse Email Professionnelle</label>
                            <input type="email" name="email" x-model="employe.email" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                            @error('email', 'edit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Département d'Affectation <span class="text-rose-500">*</span></label>
                            <select name="departement_id" required x-model="employe.departement_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                @foreach ($departements as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->designation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Équipe Assignée <span class="text-rose-500">*</span></label>
                            <select name="equipe_id" required x-model="employe.equipe_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                @foreach ($equipes as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Compte Utilisateur Associé (optionnel)</label>
                        <select name="user_id" x-model="employe.user_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Aucun compte (Employé simple)</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" x-bind:disabled="'{{ $u->employe ? 1 : 0 }}' == '1' && employe.user_id != '{{ $u->id }}'">
                                    {{ $u->name }} ({{ $u->email }}) {{ $u->employe ? '- Déjà lié' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id', 'edit') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ createAccountEdit: false }" class="space-y-4 pt-2 border-t border-slate-100">
                        <!-- Cas 1 : Déjà un compte associé -->
                        <div x-show="employe.user_id" class="space-y-4" x-transition>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nouveau mot de passe du compte <span class="text-xs text-slate-400 font-normal">(laisser vide pour ne pas modifier)</span></label>
                                <input type="password" name="password" placeholder="Saisir un nouveau mot de passe..." class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                                @error('password', 'edit') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Cas 2 : Pas encore de compte associé -->
                        <div x-show="!employe.user_id" class="space-y-4" x-transition>
                            <div class="flex items-center">
                                <input id="create_user_account_edit" name="create_user_account" type="checkbox" value="1" x-model="createAccountEdit" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                <label for="create_user_account_edit" class="ml-2 block text-sm font-semibold text-slate-700">Créer un nouveau compte utilisateur pour cet employé</label>
                            </div>
                            
                            <div x-show="createAccountEdit" x-cloak class="space-y-4" x-transition>
                                <p class="text-xs text-slate-500">Un compte sera créé avec le nom et l'adresse email de cet employé. Renseignez le mot de passe ci-dessous :</p>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe du compte <span class="text-rose-500">*</span></label>
                                    <input type="password" name="password" ::required="createAccountEdit && !employe.user_id" placeholder="Min. 6 caractères" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                                    @error('password', 'edit') <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3 bg-white sticky bottom-0">
                        <button type="button" @click="openEdit = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>