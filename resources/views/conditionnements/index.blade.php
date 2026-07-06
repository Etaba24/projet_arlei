<x-app-layout>
    <div class="space-y-6">
        
        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Types de Conditionnement</h1>
                    <p class="text-sm text-slate-500 mt-1">Gérez les différents types d'emballages et conditionnements disponibles pour la production.</p>
                </div>
                <form method="GET" action="{{ route('conditionnements.index') }}" class="relative w-full md:w-80 ml-0 md:ml-8">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Rechercher un conditionnement..."
                           class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </form>
                <div class="ml-auto">
                    <label for="modal-create-toggle" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-emerald-600/20 transition-all cursor-pointer group">
                        <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau Type
                    </label>
                </div>
            </div>
        </x-slot>

        @if (session('status'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-4 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Libellé</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Unité associée</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Qté / unité</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($types as $type)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-slate-400">{{ $type->code }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $type->libelle }}</td>
                                <td class="px-6 py-4 font-medium text-slate-500">{{ $type->unite ?? '—' }}</td>
                                <td class="px-6 py-4 font-medium text-slate-500">{{ $type->quantite_par_unite ? number_format($type->quantite_par_unite, 2) : '—' }}</td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate">{{ $type->description ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <label for="edit-toggle-{{ $type->id }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg cursor-pointer transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </label>
                                    <form action="{{ route('conditionnements.destroy', $type) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>

                                    <!-- EDIT MODAL -->
                                    <input type="checkbox" id="edit-toggle-{{ $type->id }}" class="peer hidden" />
                                    <div class="fixed inset-0 z-[60] hidden peer-checked:flex items-center justify-center p-4 text-left font-normal">
                                        <label for="edit-toggle-{{ $type->id }}" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cursor-pointer animate-fade-in"></label>
                                        <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-modal-pop">
                                            <div class="p-8">
                                                <div class="flex items-center justify-between mb-8">
                                                    <div>
                                                        <h3 class="text-xl font-bold text-slate-900 text-left">Modifier le Type</h3>
                                                        <p class="text-sm text-slate-500 mt-1 text-left">Code: <span class="font-mono text-emerald-600 font-bold">{{ $type->code }}</span></p>
                                                    </div>
                                                    <label for="edit-toggle-{{ $type->id }}" class="p-2 hover:bg-slate-100 rounded-full cursor-pointer text-slate-400 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </label>
                                                </div>
                                                <form action="{{ route('conditionnements.update', $type) }}" method="POST" class="space-y-6">
                                                    @csrf 
                                                    @method('PUT')
                                                    <div class="space-y-4 text-left">
                                                        <div>
                                                            <label class="block text-sm font-bold text-slate-700 mb-2">Libellé (ex: Sachet, Bouteille) <span class="text-rose-500">*</span></label>
                                                            <input type="text" name="libelle" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ $type->libelle }}" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-bold text-slate-700 mb-2">Unité associée (Optionnel)</label>
                                                            <input type="text" name="unite" placeholder="ex: Kg, Litre, Pièce" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ $type->unite }}" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-bold text-slate-700 mb-2">Quantité par unité (Optionnel)</label>
                                                            <input type="number" step="0.01" min="0.0001" name="quantite_par_unite" placeholder="ex: 0.5" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ $type->quantite_par_unite }}" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                                            <textarea name="description" rows="2" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all">{{ $type->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="flex gap-3 pt-4">
                                                        <label for="edit-toggle-{{ $type->id }}" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-all cursor-pointer text-center">Annuler</label>
                                                        <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 font-medium italic">Aucun type de conditionnement répertorié.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($types->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $types->links() }}
                </div>
            @endif
        </div>

        <!-- CREATE MODAL -->
        <input type="checkbox" id="modal-create-toggle" class="peer hidden" {{ $errors->any() ? 'checked' : '' }} />
        <div class="fixed inset-0 z-[60] hidden peer-checked:flex items-center justify-center p-4">
            <label for="modal-create-toggle" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cursor-pointer animate-fade-in"></label>
            <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-modal-pop">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Nouveau Type</h3>
                            <p class="text-sm text-slate-500 mt-1">Créez un nouveau type de conditionnement.</p>
                        </div>
                        <label for="modal-create-toggle" class="p-2 hover:bg-slate-100 rounded-full cursor-pointer text-slate-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </label>
                    </div>
                    <form action="{{ route('conditionnements.store') }}" method="POST" class="space-y-6">
                        @csrf
                        @if($errors->any())
                            <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm font-semibold text-rose-700">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Libellé (ex: Sachet) <span class="text-rose-500">*</span></label>
                                <input type="text" name="libelle" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('libelle') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Unité associée (Optionnel)</label>
                                <input type="text" name="unite" placeholder="ex: Kg, Litre, Pièce" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('unite') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Quantité par unité (Optionnel)</label>
                                <input type="number" step="0.01" min="0.0001" name="quantite_par_unite" placeholder="ex: 0.5" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('quantite_par_unite') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                <textarea name="description" rows="2" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <label for="modal-create-toggle" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-all cursor-pointer text-center">Annuler</label>
                            <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
