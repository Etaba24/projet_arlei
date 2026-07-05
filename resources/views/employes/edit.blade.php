<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier l'Employé</h1>
            <p class="text-sm text-slate-500 mt-1">Mise à jour des informations de {{ $employe->matricule }}.</p>
        </div>
        <div>
            <a href="{{ route('employes.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('employes.update', $employe) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="matricule" class="block text-sm font-semibold text-slate-700">Matricule Identifiant</label>
                <input type="text" id="matricule" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm" value="{{ $employe->matricule }}" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="nom" class="block text-sm font-semibold text-slate-700">Nom de famille <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" id="nom" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('nom', $employe->nom) }}" />
                    @error('nom')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prenom" class="block text-sm font-semibold text-slate-700">Prénom <span class="text-rose-500">*</span></label>
                    <input type="text" name="prenom" id="prenom" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('prenom', $employe->prenom) }}" />
                    @error('prenom')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="residence" class="block text-sm font-semibold text-slate-700">Résidence / Ville</label>
                    <input type="text" name="residence" id="residence" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('residence', $employe->residence) }}" />
                    @error('residence')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fonction" class="block text-sm font-semibold text-slate-700">Fonction / Poste <span class="text-rose-500">*</span></label>
                    <input type="text" name="fonction" id="fonction" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('fonction', $employe->fonction) }}" />
                    @error('fonction')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="telephone" class="block text-sm font-semibold text-slate-700">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('telephone', $employe->telephone) }}" />
                    @error('telephone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Adresse Email Professionnelle</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('email', $employe->email) }}" />
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                <div>
                    <label for="departement_id" class="block text-sm font-semibold text-slate-700">Département d'Affectation <span class="text-rose-500">*</span></label>
                    <select name="departement_id" id="departement_id" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}" {{ old('departement_id', $employe->departement_id) == $dep->id ? 'selected' : '' }}>{{ $dep->designation }}</option>
                        @endforeach
                    </select>
                    @error('departement_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="equipe_id" class="block text-sm font-semibold text-slate-700">Équipe Assignée <span class="text-rose-500">*</span></label>
                    <select name="equipe_id" id="equipe_id" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        @foreach ($equipes as $eq)
                            <option value="{{ $eq->id }}" {{ old('equipe_id', $employe->equipe_id) == $eq->id ? 'selected' : '' }}>{{ $eq->nom }}</option>
                        @endforeach
                    </select>
                    @error('equipe_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="user_id" class="block text-sm font-semibold text-slate-700">Compte Utilisateur Associé (optionnel)</label>
                <select name="user_id" id="user_id" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Aucun compte (Employé simple)</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id', $employe->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-400">Associer un compte utilisateur permet à cet employé de se connecter avec son rôle opérateur.</p>
                @error('user_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('employes.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
