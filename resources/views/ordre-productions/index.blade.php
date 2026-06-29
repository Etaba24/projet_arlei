<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ordres de Production</h1>
                <p class="text-sm text-slate-500 mt-1">Gestion des productions en parallèle, suivi des phases et clôture par conditionnement.</p>
            </div>
            <form method="GET" action="{{ route('ordre-productions.index') }}" class="relative w-full md:w-80 ml-0 md:ml-8">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher dans le tableau..."
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </form>
            <div class="ml-auto">
                <a href="{{ route('ordre-productions.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-emerald-600/10 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau Lancement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-emerald-600 text-white uppercase text-xxs font-semibold border-b border-emerald-700">
                        <th class="py-3 px-6">Code OP</th>
                        <th class="py-3 px-6">Produit</th>
                        <th class="py-3 px-6">Matière première</th>
                        <th class="py-3 px-6">Quantité MP</th>
                        <th class="py-3 px-6">Statut</th>
                        <th class="py-3 px-6">Démarré le</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($ops as $op)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 font-semibold text-slate-900">{{ $op->code }}</td>
                            <td class="py-4 px-6 text-slate-700">{{ $op->produitFini->designation }}</td>
                            <td class="py-4 px-6 text-slate-700">{{ $op->matierePremiere->libelle }}</td>
                            <td class="py-4 px-6 font-bold text-slate-900">{{ number_format($op->quantite_mp_injectee, 2) }} {{ $op->matierePremiere->unite_mesure }}</td>
                            <td class="py-4 px-6">
                                @php
                                    $statusClasses = [
                                        'en_attente' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                        'en_cours' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                                        'conditionne' => 'bg-sky-50 text-sky-700 ring-sky-600/10',
                                        'termine' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-semibold ring-1 ring-inset {{ $statusClasses[$op->statut] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                                    {{ str_replace('_', ' ', ucfirst($op->statut)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500">{{ $op->date_debut->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('ordre-productions.show', $op->id) }}" class="inline-flex items-center justify-center px-3 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-xs font-semibold transition-colors">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Aucun ordre de production lancé pour l'instant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ops->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $ops->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
