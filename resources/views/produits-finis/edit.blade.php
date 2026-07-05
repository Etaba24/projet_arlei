<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier le Produit Fini</h1>
            <p class="text-sm text-slate-500 mt-1">Mise à jour des informations de référence et du stock physique de {{ $produitFini->code }}.</p>
        </div>
        <div>
            <a href="{{ route('produits-finis.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('produits-finis.update', $produitFini) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="code" class="block text-sm font-semibold text-slate-700">Code Identifiant</label>
                <input type="text" id="code" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm" value="{{ $produitFini->code }}" />
            </div>

            <div>
                <label for="designation" class="block text-sm font-semibold text-slate-700">Désignation / Nom du produit <span class="text-rose-500">*</span></label>
                <input type="text" name="designation" id="designation" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('designation', $produitFini->designation) }}" />
                @error('designation')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="unite_mesure" class="block text-sm font-semibold text-slate-700">Unité d'emballage <span class="text-rose-500">*</span></label>
                    <select name="unite_mesure" id="unite_mesure" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="Sachets" {{ old('unite_mesure', $produitFini->unite_mesure) == 'Sachets' ? 'selected' : '' }}>Sachets</option>
                        <option value="g" {{ old('unite_mesure', $produitFini->unite_mesure) == 'g' ? 'selected' : '' }}>g (Grammes)</option>
                        <option value="Kg" {{ old('unite_mesure', $produitFini->unite_mesure) == 'Kg' ? 'selected' : '' }}>Kg (Kilogrammes)</option>
                    </select>
                    @error('unite_mesure')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="qte_en_stock" class="block text-sm font-semibold text-slate-700">Stock Physique <span class="text-xs font-normal text-slate-400">(Géré par le système)</span></label>
                    <input type="number" step="0.01" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm cursor-not-allowed select-none" value="{{ $produitFini->qte_en_stock }}" />
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('produits-finis.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
