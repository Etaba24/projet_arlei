<x-app-layout>
    <div class="space-y-6">
        
        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestion des Matières Premières</h1>
                    <p class="text-sm text-slate-500 mt-1">Catalogue des stocks et seuils d'alerte sécurité.</p>
                </div>
                <form method="GET" action="{{ route('matieres-premieres.index') }}" class="relative w-full md:w-80 ml-0 md:ml-8">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Rechercher une matière première..."
                           class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </form>
                <div class="ml-auto">
                    <label for="modal-toggle" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-emerald-600/20 transition-all cursor-pointer group">
                        <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle Matière
                    </label>
                </div>
            </div>
        </x-slot>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-emerald-600">
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Libellé</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Variété</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Unité</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Seuil Alerte</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($matieres as $matiere)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-slate-400">{{ $matiere->code }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900 capitalize">{{ $matiere->libelle }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 capitalize">{{ $matiere->variete ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($matiere->qte_en_stock <= $matiere->seuil_securite)
                                        <span class="font-bold text-rose-600">{{ number_format($matiere->qte_en_stock, 2) }}</span>
                                        <span class="ml-1 text-xs text-rose-400">⚠ alerte</span>
                                    @else
                                        <span class="font-bold text-emerald-600">{{ number_format($matiere->qte_en_stock, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $matiere->unite_mesure }}</td>
                                <td class="px-6 py-4 text-sm text-slate-400">{{ number_format($matiere->seuil_securite, 2) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <label for="edit-toggle-{{ $matiere->code }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg cursor-pointer transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        
                                    </label>
                                    <form action="{{ route('matieres-premieres.destroy', $matiere->id) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            
                                        </button>
                                    </form>
                                    <input type="checkbox" id="edit-toggle-{{ $matiere->code }}" class="peer hidden" />
                                    
                                    <div class="fixed inset-0 z-[60] hidden peer-checked:flex items-center justify-center p-4 text-left font-normal">
                                        <label for="edit-toggle-{{ $matiere->code }}" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cursor-pointer animate-fade-in"></label>
                                        
                                        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-modal-pop">
                                            <div class="p-8">
                                                <div class="flex items-center justify-between mb-8">
                                                    <div>
                                                        <h3 class="text-xl font-bold text-slate-900 text-left">Modifier la Matière</h3>
                                                        <p class="text-sm text-slate-500 mt-1 text-left">Édition de <span class="font-mono text-emerald-600 font-bold">{{ $matiere->code }}</span></p>
                                                    </div>
                                                    <label for="edit-toggle-{{ $matiere->code }}" class="p-2 hover:bg-slate-100 rounded-full cursor-pointer text-slate-400 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </label>
                                                </div>

                                                <form action="{{ route('matieres-premieres.update', $matiere->id) }}" method="POST" class="space-y-6">
                                                    @csrf @method('PUT')

                                                    <div class="space-y-4">
                                                        <div>
                                                            <label for="libelle-{{ $matiere->code }}" class="block text-sm font-bold text-slate-700 mb-2 text-left">Libellé du produit</label>
                                                            <input type="text" name="libelle" id="libelle-{{ $matiere->code }}" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-slate-900" value="{{ $matiere->libelle }}" />
                                                        </div>

                                                        <div>
                                                            <label for="variete-{{ $matiere->code }}" class="block text-sm font-bold text-slate-700 mb-2 text-left">Variété / Type</label>
                                                            <input type="text" name="variete" id="variete-{{ $matiere->code }}" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-slate-900" value="{{ $matiere->variete }}" />
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                            <div>
                                                                <label for="qte-{{ $matiere->code }}" class="block text-sm font-bold text-slate-700 mb-2 text-left">Stock Actuel</label>
                                                                <input type="number" step="0.01" name="qte_en_stock" id="qte-{{ $matiere->code }}" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-bold text-slate-900" value="{{ $matiere->qte_en_stock }}" />
                                                            </div>
                                                            <div>
                                                                <label for="unite-{{ $matiere->code }}" class="block text-sm font-bold text-slate-700 mb-2 text-left">Unité</label>
                                                                <select name="unite_mesure" id="unite-{{ $matiere->code }}" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-slate-900">
                                                                    <option value="Kg" {{ $matiere->unite_mesure == 'Kg' ? 'selected' : '' }}>Kg</option>
                                                                    <option value="Litres" {{ $matiere->unite_mesure == 'Litres' ? 'selected' : '' }}>Litres</option>
                                                                    <option value="Tonnes" {{ $matiere->unite_mesure == 'Tonnes' ? 'selected' : '' }}>Tonnes</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label for="seuil-{{ $matiere->code }}" class="block text-sm font-bold text-slate-700 mb-2 text-left">Seuil de Sécurité (Alerte)</label>
                                                            <input type="number" step="0.01" name="seuil_securite" id="seuil-{{ $matiere->code }}" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-slate-900" value="{{ $matiere->seuil_securite }}" />
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3 pt-4">
                                                        <label for="edit-toggle-{{ $matiere->code }}" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-all cursor-pointer text-center">
                                                            Annuler
                                                        </label>
                                                        <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">
                                                            Mettre à jour
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-12 text-center text-slate-400 font-medium italic">Aucune donnée trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($matieres->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $matieres->links() }}
                </div>
            @endif
        </div>

        <input type="checkbox" id="modal-toggle" class="peer hidden" {{ $errors->any() ? 'checked' : '' }} />
        <div class="fixed inset-0 z-[60] hidden peer-checked:flex items-center justify-center p-4">
            <label for="modal-toggle" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cursor-pointer animate-fade-in"></label>
            
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-modal-pop">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Nouvelle Matière</h3>
                            <p class="text-sm text-slate-500 mt-1">Le code identifiant sera généré automatiquement.</p>
                        </div>
                        <label for="modal-toggle" class="p-2 hover:bg-slate-100 rounded-full cursor-pointer text-slate-400 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </label>
                    </div>

                    <form action="{{ route('matieres-premieres.store') }}" method="POST" class="space-y-6">
                        @csrf
                        @if($errors->any())
                            <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm font-semibold text-rose-700">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Libellé du produit <span class="text-rose-500">*</span></label>
                                <input type="text" name="libelle" required placeholder="ex: Manioc frais" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('libelle') }}" />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Variété / Type</label>
                                <input type="text" name="variete" placeholder="ex: Belombo" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('variete') }}" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Unité de mesure</label>
                                    <select name="unite_mesure" required class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                        <option value="Kg">Kg</option>
                                        <option value="Litres">Litres</option>
                                        <option value="Tonnes">Tonnes</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Seuil d'alerte</label>
                                    <input type="number" step="0.01" name="seuil_securite" required placeholder="100" class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all" value="{{ old('seuil_securite') }}" />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <label for="modal-toggle" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold hover:bg-slate-50 transition-all cursor-pointer text-center">
                                Annuler
                            </label>
                            <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">
                                Créer la fiche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>