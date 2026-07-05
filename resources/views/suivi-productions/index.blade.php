<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between w-full gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Suivi de Production</h1>
                <p class="text-sm text-slate-500 mt-1">Tableau de bord temps réel des ordres de production et pilotage des phases.</p>
            </div>
            <div class="flex gap-2 w-full md:w-auto mt-3 md:mt-0">
                <a href="{{ request()->fullUrlWithQuery(['print' => 'true']) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Statistiques des phases -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-5">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-4 transition-colors">
                <p class="text-xs font-semibold text-emerald-700 uppercase">Productions Actives</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $totalActives }}</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-sm p-4 transition-colors">
                <p class="text-xs font-semibold text-slate-700 uppercase">Phases en Attente</p>
                <p class="text-3xl font-bold text-slate-600 mt-2">{{ $phasesEnAttente }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-4 transition-colors">
                <p class="text-xs font-semibold text-amber-700 uppercase">Phases en Cours</p>
                <p class="text-3xl font-bold text-amber-600 mt-2">{{ $phasesEnCours }}</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl shadow-sm p-4 transition-colors">
                <p class="text-xs font-semibold text-orange-700 uppercase">À Valider</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">{{ $phasesTerminees }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl shadow-sm p-4 transition-colors">
                <p class="text-xs font-semibold text-blue-700 uppercase">Validées</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $phasesValidees }}</p>
            </div>
        </div>

        <!-- Phases en attente de validation (Priorité Admin) -->
        @if ($phasesAValider->isNotEmpty())
            <div class="bg-orange-50 border border-orange-200 rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-orange-900 mb-4">⚠️ Phases en Attente de Validation</h2>
                <div class="space-y-3">
                    @foreach ($phasesAValider as $phase)
                        <div class="bg-white rounded-lg p-4 border border-orange-200 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $phase->ordreProduction->code }} - {{ $phase->transformation->designation }}</p>
                                <p class="text-sm text-slate-600">Équipe: <strong>{{ $phase->equipe->nom }}</strong> | Produit: {{ $phase->ordreProduction->produitFini->designation }}</p>
                                <p class="text-xs text-slate-500 mt-1">Terminé le: {{ $phase->date_fin->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('ordre-productions.show', $phase->ordreProduction) }}" class="inline-flex items-center px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                                    Voir Fiche
                                </a>
                                @if(Auth::user()->hasPermission('production.valider-phase'))
                                    <form action="{{ route('ordre-productions.valider-phase', ['ordre_production' => $phase->ordreProduction, 'phase' => $phase]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                                            Valider
                                        </button>
                                    </form>

                                    <form id="form-invalider-{{ $phase->id }}" action="{{ route('ordre-productions.invalider-phase', ['ordre_production' => $phase->ordreProduction, 'phase' => $phase]) }}" method="POST" class="hidden">
                                        @csrf
                                        <input type="hidden" name="motif" id="motif-invalider-{{ $phase->id }}">
                                    </form>
                                    <button type="button" onclick="triggerInvalidation({{ $phase->id }}, '{{ addslashes($phase->equipe?->nom ?? 'Inconnue') }}')" class="inline-flex items-center px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-colors">
                                        Invalider
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Productions ACTIVES -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-slate-200 px-6 py-4">
                <h2 class="text-xl font-bold text-slate-900">🚀 Productions en Cours</h2>
            </div>

            @if ($opsActives->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-xxs font-semibold border-b border-slate-200">
                                <th class="py-3 px-6">Code OP</th>
                                <th class="py-3 px-6">Produit</th>
                                <th class="py-3 px-6">Phase Initiale</th>
                                <th class="py-3 px-6">Phase Intermédiaire</th>
                                <th class="py-3 px-6">Phase Finale</th>
                                <th class="py-3 px-6">Responsable</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($opsActives as $op)
                                @php
                                    $phaseInitiale = $op->phaseProductions->where('numero_phase', 'initiale')->first();
                                    $phaseIntermediaire = $op->phaseProductions->where('numero_phase', 'intermediaire')->first();
                                    $phaseFinale = $op->phaseProductions->where('numero_phase', 'finale')->first();
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-900">{{ $op->code }}</td>
                                    <td class="py-4 px-6">
                                        <div class="font-semibold text-slate-900">{{ $op->produitFini->designation }}</div>
                                        <div class="text-xs text-slate-500">Lot: {{ $op->numero_lot }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($phaseInitiale)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-semibold
                                                {{ $phaseInitiale->statut === 'en_attente' ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $phaseInitiale->statut === 'en_cours' ? 'bg-amber-50 text-amber-700' : '' }}
                                                {{ $phaseInitiale->statut === 'termine' ? 'bg-orange-50 text-orange-700' : '' }}
                                                {{ $phaseInitiale->statut === 'valide' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            ">
                                                {{ str_replace('_', ' ', ucfirst($phaseInitiale->statut)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($phaseIntermediaire)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-semibold
                                                {{ $phaseIntermediaire->statut === 'en_attente' ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $phaseIntermediaire->statut === 'en_cours' ? 'bg-amber-50 text-amber-700' : '' }}
                                                {{ $phaseIntermediaire->statut === 'termine' ? 'bg-orange-50 text-orange-700' : '' }}
                                                {{ $phaseIntermediaire->statut === 'valide' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            ">
                                                {{ str_replace('_', ' ', ucfirst($phaseIntermediaire->statut)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($phaseFinale)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xxs font-semibold
                                                {{ $phaseFinale->statut === 'en_attente' ? 'bg-slate-100 text-slate-700' : '' }}
                                                {{ $phaseFinale->statut === 'en_cours' ? 'bg-amber-50 text-amber-700' : '' }}
                                                {{ $phaseFinale->statut === 'termine' ? 'bg-orange-50 text-orange-700' : '' }}
                                                {{ $phaseFinale->statut === 'valide' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            ">
                                                {{ str_replace('_', ' ', ucfirst($phaseFinale->statut)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-slate-600">{{ $op->employe->nom }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <a href="{{ route('ordre-productions.show', $op) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors">
                                            Voir détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Aucune production en cours pour l'instant.</p>
                </div>
            @endif
        </div>

        <!-- Productions CLÔTURÉES (Historique) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200 px-6 py-4">
                <h2 class="text-xl font-bold text-slate-900">✓ Productions Clôturées (Dernières 7)</h2>
            </div>

            @if ($opsTerminees->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-xxs font-semibold border-b border-slate-200">
                                <th class="py-3 px-6">Code OP</th>
                                <th class="py-3 px-6">Produit</th>
                                <th class="py-3 px-6">Matière Première</th>
                                <th class="py-3 px-6">Lot</th>
                                <th class="py-3 px-6">Responsable</th>
                                <th class="py-3 px-6">Date Début</th>
                                <th class="py-3 px-6 text-center">Voir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($opsTerminees as $op)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-900">{{ $op->code }}</td>
                                    <td class="py-4 px-6">{{ $op->produitFini->designation }}</td>
                                    <td class="py-4 px-6 text-slate-600">{{ $op->matierePremiere->libelle }}</td>
                                    <td class="py-4 px-6"><strong>{{ $op->numero_lot }}</strong></td>
                                    <td class="py-4 px-6">{{ $op->employe->nom }}</td>
                                    <td class="py-4 px-6 text-slate-500">{{ $op->date_debut->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <a href="{{ route('ordre-productions.show', $op) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <p>Aucune production clôturée pour l'instant.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
