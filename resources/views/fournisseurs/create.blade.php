<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ajouter un Fournisseur</h1>
            <p class="text-sm text-slate-500 mt-1">Créez un nouveau contact de fournisseur de matières premières.</p>
        </div>
        <div>
            <a href="{{ route('fournisseurs.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('fournisseurs.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="designation" class="block text-sm font-semibold text-slate-700">Nom / Désignation <span class="text-rose-500">*</span></label>
                    <input type="text" name="designation" id="designation" required placeholder="ex: Plantations du Centre" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('designation') }}" />
                    @error('designation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="raison_sociale" class="block text-sm font-semibold text-slate-700">Raison Sociale</label>
                    <input type="text" name="raison_sociale" id="raison_sociale" placeholder="ex: Coopérative / SARL..." class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('raison_sociale') }}" />
                    @error('raison_sociale')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="nationalite" class="block text-sm font-semibold text-slate-700">Nationalité</label>
                    <input type="text" name="nationalite" id="nationalite" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('nationalite', 'Camerounaise') }}" />
                    @error('nationalite')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="localite" class="block text-sm font-semibold text-slate-700">Localité / Ville</label>
                    <input type="text" name="localite" id="localite" placeholder="ex: Bertoua" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('localite') }}" />
                    @error('localite')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="telephone" class="block text-sm font-semibold text-slate-700">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" placeholder="ex: +237699999999" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('telephone') }}" />
                    @error('telephone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Adresse Email</label>
                    <input type="email" name="email" id="email" placeholder="ex: contact@fournisseur.com" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('email') }}" />
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('fournisseurs.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Créer le fournisseur
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
