<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestion des Lots de Matières Premières</h1>
            <p class="text-sm text-slate-500 mt-1">Chaque réception de MP est enregistrée comme un lot distinct pour un suivi de qualité FIFO.</p>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">

        @if(session('status'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl px-5 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

            {{-- ── FORM CRÉATION LOT ── --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-800 to-slate-700">
                    <h2 class="text-sm font-bold text-white">Enregistrer une Nouvelle Réception</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Chaque livraison = un nouveau lot</p>
                </div>
                <form action="{{ route('lots.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-xs font-semibold text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Matière Première <span class="text-rose-500">*</span></label>
                        <select name="matiere_premiere_id" required
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="">— Sélectionnez —</option>
                            @foreach($matieres as $mp)
                                <option value="{{ $mp->id }}" {{ old('matiere_premiere_id')==$mp->id?'selected':'' }}>
                                    {{ $mp->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Fournisseur</label>
                        <select name="fournisseur_id"
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="">— Aucun —</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}" {{ old('fournisseur_id')==$f->id?'selected':'' }}>{{ $f->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">N° de commande</label>
                        <input type="text" name="numero_commande" value="{{ old('numero_commande') }}"
                               placeholder="CMD-2026-001"
                               class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Date de réception <span class="text-rose-500">*</span></label>
                            <input type="date" name="date_reception" value="{{ old('date_reception', today()->format('Y-m-d')) }}" required
                                   class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Date de péremption</label>
                            <input type="date" name="date_peremption" value="{{ old('date_peremption') }}"
                                   class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Quantité reçue <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.001" name="quantite_initiale" value="{{ old('quantite_initiale') }}"
                               placeholder="500" required
                               class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Observations, conditions de livraison…"
                                  class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm shadow-emerald-600/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Enregistrer la réception
                    </button>
                </form>
            </div>

            {{-- ── LISTE DES LOTS ── --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- Search + stats --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <form method="GET" action="{{ route('lots.index') }}" class="flex-1">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Chercher un lot ou une MP…"
                                   class="pl-10 pr-4 py-2.5 w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>
                    </form>
                    <span class="text-sm text-slate-500">{{ $lots->total() }} lot(s)</span>
                </div>

                @forelse($lots as $lot)
                @php
                    $qualite = $lot->qualite;
                    $age     = $lot->age_jours;
                    $bgLot   = match($qualite['score']) {
                        5,4 => 'border-emerald-200',
                        3   => 'border-yellow-200',
                        2   => 'border-orange-200',
                        1   => 'border-amber-200',
                        default => 'border-red-300',
                    };
                @endphp
                <div class="bg-white border-2 {{ $bgLot }} rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-bold text-slate-900 text-base">{{ $lot->code_lot }}</p>
                                    <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full
                                        {{ $lot->statut === 'disponible' ? 'bg-emerald-100 text-emerald-700' :
                                           ($lot->statut === 'epuise' ? 'bg-slate-100 text-slate-500' :
                                           ($lot->statut === 'quarantaine' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')) }}">
                                        {{ $lot->statut }}
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">{{ $lot->matierePremiere->libelle }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Réceptionné le {{ \Carbon\Carbon::parse($lot->date_reception)->format('d/m/Y') }}
                                    @if($lot->fournisseur) — {{ $lot->fournisseur->nom }} @endif
                                    @if($lot->numero_commande) — Cmd : {{ $lot->numero_commande }} @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-lg font-black text-slate-900">{{ number_format($lot->quantite_disponible, 2) }}</p>
                                <p class="text-xs text-slate-400">/ {{ number_format($lot->quantite_initiale, 2) }} {{ $lot->matierePremiere->unite_mesure }}</p>
                                <div class="mt-1 h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden ml-auto">
                                    @php $pct = $lot->quantite_initiale > 0 ? min(100, $lot->quantite_disponible / $lot->quantite_initiale * 100) : 0; @endphp
                                    <div class="h-full rounded-full {{ $pct > 50 ? 'bg-emerald-500' : ($pct > 20 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Quality section --}}
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1">
                                    @for($s=1;$s<=5;$s++)
                                        <div class="w-3.5 h-3.5 rounded-full {{ $s <= $qualite['score'] ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold
                                    {{ $qualite['score'] >= 4 ? 'text-emerald-600' : ($qualite['score'] === 3 ? 'text-yellow-600' : ($qualite['score'] >= 1 ? 'text-orange-600' : 'text-red-600')) }}">
                                    {{ $qualite['label'] }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $age }} jour(s)</span>
                                <span class="text-xs text-rose-500 font-semibold">Perte : {{ number_format($qualite['lossRate'] * 100, 1) }}%</span>
                            </div>
                            @if($lot->date_peremption)
                                <span class="text-xs text-slate-400">Péremption : {{ \Carbon\Carbon::parse($lot->date_peremption)->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        @if($lot->notes)
                            <p class="mt-2 text-xs text-slate-400 italic">{{ $lot->notes }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">
                            Utilisé dans {{ $lot->ordreProductions->count() }} OP(s)
                        </span>
                        @if($lot->ordreProductions->count() === 0)
                        <form action="{{ route('lots.destroy', $lot) }}" method="POST"
                              onsubmit="return confirm('Supprimer le lot {{ $lot->code_lot }} ? Le stock MP sera décrémenté.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-rose-600 font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Supprimer
                            </button>
                        </form>
                        @else
                            <span class="text-xs text-slate-300">Lot en cours d'utilisation</span>
                        @endif
                    </div>
                </div>
                @empty
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-12 text-center text-slate-400">
                        <p class="text-5xl mb-3">📦</p>
                        <p class="font-semibold text-slate-500">Aucun lot enregistré</p>
                        <p class="text-sm mt-1">Utilisez le formulaire pour enregistrer votre première réception.</p>
                    </div>
                @endforelse

                {{ $lots->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
