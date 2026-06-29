<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier le Client</h1>
            <p class="text-sm text-slate-500 mt-1">Mise à jour des coordonnées et des informations de {{ $client->code }}.</p>
        </div>
        <div>
            <a href="{{ route('clients.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="code" class="block text-sm font-semibold text-slate-700">Code Identifiant</label>
                <input type="text" id="code" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm" value="{{ $client->code }}" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="nom" class="block text-sm font-semibold text-slate-700">Nom de famille <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" id="nom" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('nom', $client->nom) }}" />
                    @error('nom')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prenom" class="block text-sm font-semibold text-slate-700">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('prenom', $client->prenom) }}" />
                    @error('prenom')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="entreprise" class="block text-sm font-semibold text-slate-700">Nom de l'entreprise</label>
                    <input type="text" name="entreprise" id="entreprise" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('entreprise', $client->entreprise) }}" />
                    @error('entreprise')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="raison_sociale" class="block text-sm font-semibold text-slate-700">Raison Sociale</label>
                    <input type="text" name="raison_sociale" id="raison_sociale" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('raison_sociale', $client->raison_sociale) }}" />
                    @error('raison_sociale')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="telephone" class="block text-sm font-semibold text-slate-700">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('telephone', $client->telephone) }}" />
                    @error('telephone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Adresse Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('email', $client->email) }}" />
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('clients.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
