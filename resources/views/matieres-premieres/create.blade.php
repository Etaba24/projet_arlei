<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ajouter une Matière Première</h1>
            <p class="text-sm text-slate-500 mt-1">Créez une nouvelle fiche produit matière première avec génération automatique de code unique.</p>
        </div>
        <div>
            <a href="{{ route('matieres-premieres.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('matieres-premieres.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="libelle" class="block text-sm font-semibold text-slate-700">Libellé du produit <span class="text-rose-500">*</span></label>
                <input type="text" name="libelle" id="libelle" required placeholder="ex: Manioc, Café..." class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('libelle') }}" />
                @error('libelle')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="variete" class="block text-sm font-semibold text-slate-700">Variété / Type</label>
                <input type="text" name="variete" id="variete" placeholder="ex: Belombo, Adianga, Robusta..." class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('variete') }}" />
                @error('variete')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="unite_mesure" class="block text-sm font-semibold text-slate-700">Unité de mesure <span class="text-rose-500">*</span></label>
                    <select name="unite_mesure" id="unite_mesure" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="Kg" {{ old('unite_mesure') == 'Kg' ? 'selected' : '' }}>Kg</option>
                        <option value="Litres" {{ old('unite_mesure') == 'Litres' ? 'selected' : '' }}>Litres</option>
                        <option value="Tonnes" {{ old('unite_mesure') == 'Tonnes' ? 'selected' : '' }}>Tonnes</option>
                    </select>
                    @error('unite_mesure')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seuil_securite" class="block text-sm font-semibold text-slate-700">Seuil de sécurité (Kg/L) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="seuil_securite" id="seuil_securite" required placeholder="ex: 1000" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('seuil_securite') }}" />
                    @error('seuil_securite')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('matieres-premieres.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Créer la matière première
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
