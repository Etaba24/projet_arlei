<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier la Machine</h1>
            <p class="text-sm text-slate-500 mt-1">Mise à jour des informations de {{ $machine->code }}.</p>
        </div>
        <div>
            <a href="{{ route('machines.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 transition-colors">
                Retour
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
        <form action="{{ route('machines.update', $machine) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="code" class="block text-sm font-semibold text-slate-700">Code Identifiant</label>
                <input type="text" id="code" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm" value="{{ $machine->code }}" />
            </div>

            <div>
                <label for="designation" class="block text-sm font-semibold text-slate-700">Désignation <span class="text-rose-500">*</span></label>
                <input type="text" name="designation" id="designation" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" value="{{ old('designation', $machine->designation) }}" />
                @error('designation')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">État de fonctionnement <span class="text-rose-500">*</span></label>
                @if(in_array($machine->etat, ['en_marche', 'arret']))
                    <input type="text" disabled class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-sm cursor-not-allowed select-none" 
                           value="{{ $machine->etat === 'en_marche' ? 'En marche (Géré par la production)' : 'À l\'arrêt (Géré par la production)' }}" />
                    <input type="hidden" name="etat" value="{{ $machine->etat }}" />
                @else
                    <select name="etat" id="etat" required class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="pret" {{ old('etat', $machine->etat) == 'pret' ? 'selected' : '' }}>Prête / Disponible</option>
                        <option value="en_panne" {{ old('etat', $machine->etat) == 'en_panne' ? 'selected' : '' }}>En panne</option>
                        <option value="en_maintenance" {{ old('etat', $machine->etat) == 'en_maintenance' ? 'selected' : '' }}>En maintenance</option>
                    </select>
                @endif
                @error('etat')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="{{ route('machines.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
