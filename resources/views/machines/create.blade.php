<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ajouter une Machine</h1>
            <p class="text-sm text-slate-500 mt-1">Enregistrez un nouvel équipement dans le parc machine.</p>
        </div>
        <div>
            <a href="{{ route('machines.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('machines.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="designation" class="block text-sm font-semibold text-slate-700">Nom / Désignation de la machine <span class="text-rose-500">*</span></label>
                <input type="text" name="designation" id="designation" required placeholder="ex: Broyeuse B-400, Four de Séchage F1..." class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('designation') }}" />
                @error('designation')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="etat" class="block text-sm font-semibold text-slate-700">État de fonctionnement initial <span class="text-rose-500">*</span></label>
                <select name="etat" id="etat" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="pret" {{ old('etat') == 'pret' ? 'selected' : '' }}>Prête / Disponible</option>
                    <option value="en_panne" {{ old('etat') == 'en_panne' ? 'selected' : '' }}>En panne</option>
                    <option value="en_maintenance" {{ old('etat') == 'en_maintenance' ? 'selected' : '' }}>En maintenance</option>
                </select>
                @error('etat')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('machines.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Ajouter la machine
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
