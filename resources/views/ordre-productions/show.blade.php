<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Fiche de Production</h1>
            <p class="text-sm text-slate-500 mt-1">
                Suivi des phases : Lancement → Transformations → Conditionnement.
                @if($op->statut === 'interrompu')
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">⛔ Interrompu</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if(Auth::user()->hasAdminInterface())
            <a href="{{ route('ordre-productions.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                Retour à la liste
            </a>
            {{-- Interrompre / Reprendre --}}
            @if(Auth::user()->hasPermission('production.interrompre') && in_array($op->statut, ['en_cours', 'conditionne']))
                <button onclick="document.getElementById('modal-interrompre').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    ⛔ Interrompre
                </button>
            @elseif(Auth::user()->hasPermission('production.reprendre') && $op->statut === 'interrompu')
                <form action="{{ route('ordre-productions.reprendre', $op->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        ▶ Reprendre
                    </button>
                </form>
            @endif
            @endif
            @if ($op->qr_code_path)
                <label for="modal-qr-toggle" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-emerald-600/10 transition-colors cursor-pointer">
                    QR Code
                </label>
            @endif
        </div>
    </x-slot>

    @php
        $op->load(['produitFini', 'matierePremiere', 'employe', 'lots.matierePremiere',
                   'phaseProductions.transformation', 'phaseProductions.equipe', 'phaseProductions.machine',
                   'conditionnement.equipe']);
        $prochainePhase    = $op->prochainePhaseAAssigner();
        $toutesValidees    = $op->toutesLesPhasesSontValidees();
        $totalPhases       = $op->phaseProductions->count();

        $statusColors = [
            'en_attente'  => 'bg-slate-100 text-slate-600',
            'en_cours'    => 'bg-amber-100 text-amber-700',
            'termine'     => 'bg-orange-100 text-orange-700',
            'valide'      => 'bg-emerald-100 text-emerald-700',
            'interrompu'  => 'bg-rose-100 text-rose-700',
        ];
        $statusLabels = [
            'en_attente'  => 'En attente',
            'en_cours'    => 'En cours',
            'termine'     => 'Terminée – En attente de validation',
            'valide'      => '✓ Validée',
            'interrompu'  => '⛔ Interrompue',
        ];
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Bannière interruption --}}
        @if($op->statut === 'interrompu')
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 text-xl">⛔</div>
            <div class="flex-1">
                <p class="font-bold text-rose-800">Production interrompue le {{ $op->date_interruption?->format('d/m/Y à H:i') }}</p>
                @if($op->motif_interruption)
                    <p class="text-sm text-rose-600 mt-1">Motif : {{ $op->motif_interruption }}</p>
                @endif
            </div>
            @if(Auth::user()->hasPermission('production.reprendre'))
            <form action="{{ route('ordre-productions.reprendre', $op->id) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition-colors">
                    ▶ Reprendre la production
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Notifications --}}
        @if(session('status'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl px-5 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div>
        @endif

        {{-- ───────────── ÉTAPE 1 : RÉSUMÉ LANCEMENT ───────────── --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-slate-900 font-bold text-sm">1</span>
                <div>
                    <h3 class="text-white font-bold">Lancement de la Production</h3>
                    <p class="text-slate-300 text-xs">Informations générales de l'ordre de production</p>
                </div>
                <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 text-white">✓ Lancé</span>
            </div>
            <div class="p-6 space-y-5">
                {{-- Info principale --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Code OP</p>
                        <p class="mt-1 text-slate-900 font-bold text-lg">{{ $op->code }}</p>
                        <p class="text-xs text-slate-500 mt-1">Lot : <strong>{{ $op->numero_lot }}</strong></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Produit Fini</p>
                        <p class="mt-1 text-slate-900 font-semibold">{{ $op->produitFini->designation }}</p>
                        <p class="text-xs text-slate-500 mt-1">Stock actuel : {{ number_format($op->produitFini->qte_en_stock, 2) }} {{ $op->produitFini->unite_mesure }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">MP Total injectée</p>
                        <p class="mt-1 text-slate-900 font-semibold">{{ number_format($op->quantite_mp_injectee, 2) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $op->lots->count() }} lot(s) de MP</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Responsable</p>
                        <p class="mt-1 text-slate-900 font-semibold">{{ $op->employe->nom }} {{ $op->employe->prenom ?? '' }}</p>
                        <p class="text-xs text-slate-500 mt-1">Démarré le {{ $op->date_debut->format('d/m/Y') }}</p>
                    </div>
                </div>

                {{-- Estimations IA --}}
                @if($op->quantite_pf_estimee || $op->duree_estimee_min)
                <div class="bg-slate-900 rounded-2xl p-4">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="text-emerald-400">✦</span> Estimations IA pré-production
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @if($op->quantite_pf_cible)
                        <div>
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide">Cible PF</p>
                            <p class="text-base font-black text-white">{{ number_format($op->quantite_pf_cible, 1) }} <span class="text-xs font-normal text-slate-400">{{ $op->produitFini->unite_mesure }}</span></p>
                        </div>
                        @endif
                        @if($op->quantite_pf_estimee)
                        <div>
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide">PF estimé</p>
                            <p class="text-base font-black text-emerald-400">{{ number_format($op->quantite_pf_estimee, 1) }} <span class="text-xs font-normal text-slate-500">{{ $op->produitFini->unite_mesure }}</span></p>
                        </div>
                        @endif
                        @if($op->taux_perte_estime)
                        <div>
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide">Perte estimée</p>
                            <p class="text-base font-black text-amber-400">{{ number_format($op->taux_perte_estime, 1) }}%</p>
                        </div>
                        @endif
                        @if($op->duree_estimee_min)
                        <div>
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide">Durée estimée</p>
                            <p class="text-base font-black text-violet-400">
                                {{ $op->duree_estimee_min >= 60 ? floor($op->duree_estimee_min/60) . 'h ' . ($op->duree_estimee_min%60) . 'min' : $op->duree_estimee_min . ' min' }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Lots de MP utilisés --}}
                @if($op->lots->count() > 0)
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Lots de Matières Premières utilisés</p>
                    <div class="grid gap-2">
                        @foreach($op->lots as $lot)
                        @php $qualite = $lot->qualite; @endphp
                        <div class="flex items-center gap-4 p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800">{{ $lot->code_lot }}</p>
                                <p class="text-xs text-slate-500">{{ $lot->matierePremiere->libelle }} — Réception : {{ \Carbon\Carbon::parse($lot->date_reception)->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                @for($s=1;$s<=5;$s++)
                                    <div class="w-2.5 h-2.5 rounded-full {{ $s <= $qualite['score'] ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                                @endfor
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $qualite['score'] >= 4 ? 'bg-emerald-100 text-emerald-700' : ($qualite['score'] === 3 ? 'bg-yellow-100 text-yellow-700' : ($qualite['score'] >= 1 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')) }}">
                                {{ $qualite['label'] }}
                            </span>
                            <span class="text-sm font-bold text-slate-700 w-28 text-right">
                                {{ number_format($lot->pivot->quantite_utilisee, 2) }} {{ $lot->matierePremiere->unite_mesure }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ───────────── ÉTAPE 2 : TRANSFORMATIONS ───────────── --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white text-blue-700 font-bold text-sm">2</span>
                <div>
                    <h3 class="text-white font-bold">Transformations (MP → PF)</h3>
                    <p class="text-blue-200 text-xs">Les étapes s'enchaînent séquentiellement. Chaque équipe doit marquer son étape comme terminée pour débloquer la suivante.</p>
                </div>
                @if($toutesValidees)
                    <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 text-white">✓ Complète</span>
                @else
                    <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-400 text-white">En cours…</span>
                @endif
            </div>

            <div class="p-6 space-y-4">
                {{-- Affichage des phases déjà assignées --}}
                @forelse ($op->phaseProductions as $phase)
                    @php
                        $borderClass = match($phase->statut) {
                            'valide'     => 'border-emerald-200 bg-emerald-50/50',
                            'en_cours'   => 'border-amber-200 bg-amber-50/50',
                            'interrompu' => 'border-rose-200 bg-rose-50/50',
                            default      => 'border-slate-200 bg-slate-50',
                        };
                    @endphp
                    <div class="rounded-2xl border-2 {{ $borderClass }} p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">
                                    @if($phase->statut === 'valide') ✅
                                    @elseif($phase->statut === 'en_cours') ⚙️
                                    @elseif($phase->statut === 'termine') 🔔
                                    @elseif($phase->statut === 'interrompu') ⛔
                                    @else ⏳ @endif
                                </span>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$phase->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$phase->statut] ?? $phase->statut }}
                                    </span>
                                    <h4 class="text-base font-bold text-slate-900 mt-1">{{ $phase->label }}</h4>
                                    <p class="text-sm text-slate-600 mt-0.5">{{ $phase->transformation?->designation ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 space-y-1 sm:text-right">
                                <p>Équipe : <strong class="text-slate-700">{{ $phase->equipe?->nom ?? '—' }}</strong></p>
                                <p>Machine : <strong class="text-slate-700">{{ $phase->machine?->designation ?? '—' }}</strong></p>
                                @if($phase->duree_estimee_min)
                                    <p class="text-xs text-violet-600">Durée IA : ~{{ $phase->duree_estimee_min }} min</p>
                                @endif
                                @if($phase->date_debut)<p>Début : {{ $phase->date_debut->format('d/m/Y H:i') }}</p>@endif
                                @if($phase->date_fin)<p>Fin : {{ $phase->date_fin->format('d/m/Y H:i') }}</p>@endif
                            </div>
                        </div>

                        {{-- Actions selon rôle et statut --}}
                        @if($op->statut !== 'interrompu' && $phase->statut !== 'interrompu')
                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex flex-wrap gap-2 justify-end">
                            {{-- Opérateur : bouton Démarrer ou Marquer Terminé --}}
                            @if(Auth::user()->isOperateur() && Auth::user()->employe && Auth::user()->employe->equipe_id === $phase->equipe_id)
                                @if($phase->statut === 'en_attente' && $phase->phasePrecedenteEstValidee())
                                    <form action="{{ route('phase-productions.demarrer', $phase->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors">
                                            ▶ Démarrer cette étape
                                        </button>
                                    </form>
                                @elseif($phase->statut === 'en_cours')
                                    <form action="{{ route('phase-productions.terminer', $phase->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl transition-colors">
                                            ✓ Marquer comme terminée
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- Admin : bouton Valider --}}
                            @if(Auth::user()->hasPermission('production.valider-phase') && $phase->statut === 'termine')
                                <form action="{{ route('ordre-productions.valider-phase', ['ordre_production' => $op->id, 'phase' => $phase->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                                        ✓ Valider et débloquer l'étape suivante
                                    </button>
                                </form>
                            @endif

                            {{-- Message d'attente --}}
                            @if($phase->statut === 'en_attente' && !$phase->phasePrecedenteEstValidee())
                                <span class="text-xs text-slate-400 italic">⏳ En attente que l'étape précédente soit validée.</span>
                            @endif
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <p class="text-4xl mb-2">🏭</p>
                        <p class="text-sm">Aucune transformation assignée pour l'instant.</p>
                    </div>
                @endforelse

                {{-- ──── Formulaire pour ajouter une phase supplémentaire (Admin seulement) ──── --}}
                @if(Auth::user()->hasPermission('production.creer') && !$toutesValidees && $op->statut === 'en_cours')
                    @php
                        $dernierePhase = $op->phaseProductions->last();
                        $peutAjouter   = !$dernierePhase || in_array($dernierePhase->statut, ['termine', 'valide']);
                    @endphp

                    @if($peutAjouter)
                    <div class="rounded-2xl border-2 border-dashed border-blue-300 bg-blue-50/60 p-5">
                        <h4 class="text-sm font-bold text-blue-800 mb-4 flex items-center gap-2">
                            <span class="text-lg">➕</span>
                            Ajouter une phase intermédiaire ou finale
                        </h4>
                        <form action="{{ route('ordre-productions.add-transformation', $op->id) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Type de Transformation</label>
                                    <select name="transformation_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Sélectionnez…</option>
                                        @foreach($transformations as $t)
                                            <option value="{{ $t->id }}">{{ $t->designation }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Équipe assignée</label>
                                    <select name="equipe_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Sélectionnez…</option>
                                        @foreach($equipes as $eq)
                                            <option value="{{ $eq->id }}">{{ $eq->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Machine</label>
                                    <select name="machine_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Sélectionnez…</option>
                                        @foreach($machines as $m)
                                            <option value="{{ $m->id }}">{{ $m->designation }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-sm text-blue-700 cursor-pointer">
                                    <input type="checkbox" name="is_finale" value="1" class="rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                                    Marquer comme phase finale (clôture les transformations)
                                </label>
                                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                                    Ajouter la phase
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 flex items-center gap-2">
                        <span class="text-xl">⏳</span>
                        <p>La prochaine étape sera disponible une fois que l'équipe en cours aura marqué son étape comme <strong>terminée</strong>.</p>
                    </div>
                    @endif
                @endif

                {{-- Message : toutes les phases validées --}}
                @if($toutesValidees)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <p>Toutes les étapes de transformation ont été complétées et validées. Vous pouvez maintenant passer au conditionnement.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ───────────── ÉTAPE 3 : CONDITIONNEMENT ───────────── --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 {{ $toutesValidees ? 'bg-gradient-to-r from-emerald-700 to-emerald-600' : 'bg-slate-200' }}">
                <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $toutesValidees ? 'bg-white text-emerald-700' : 'bg-slate-400 text-white' }} font-bold text-sm">3</span>
                <div>
                    <h3 class="{{ $toutesValidees ? 'text-white' : 'text-slate-500' }} font-bold">Conditionnement des Produits Finis</h3>
                    <p class="{{ $toutesValidees ? 'text-emerald-200' : 'text-slate-400' }} text-xs">
                        {{ $toutesValidees ? 'Toutes les étapes sont validées. Vous pouvez enregistrer le conditionnement.' : 'Disponible uniquement après validation complète de toutes les transformations.' }}
                    </p>
                </div>
                @if($op->conditionnement)
                    <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 text-white">✓ Enregistré</span>
                @endif
            </div>

            <div class="p-6">
                @if($op->conditionnement)
                    {{-- Conditionnement déjà enregistré : affichage --}}
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Équipe</p>
                                <p class="font-bold text-slate-900 mt-1">{{ $op->conditionnement->equipe->nom }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Type d'emballage</p>
                                <p class="font-bold text-slate-900 mt-1">{{ $op->conditionnement->type_emballage }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Statut</p>
                                <p class="font-bold mt-1 {{ $op->conditionnement->statut === 'valide' ? 'text-emerald-600' : 'text-orange-600' }}">
                                    {{ $op->conditionnement->statut === 'valide' ? '✓ Validé – OP Clôturé' : '🔔 En attente de validation' }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Qté produite</p>
                                <p class="font-bold text-slate-900 mt-1">{{ number_format($op->conditionnement->quantite_produite, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Qté MP consommée</p>
                                <p class="font-bold text-slate-900 mt-1">{{ number_format($op->conditionnement->quantite_mp_consommee, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                                <p class="text-xs text-slate-500 uppercase font-semibold">Perte</p>
                                <p class="font-bold text-slate-900 mt-1">{{ number_format($op->conditionnement->perte, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-6 text-sm text-slate-600">
                            <span>Fabrication : <strong>{{ $op->conditionnement->date_fabrication->format('d/m/Y') }}</strong></span>
                            <span>Péremption : <strong>{{ $op->conditionnement->date_peremption->format('d/m/Y') }}</strong></span>
                        </div>

                        @if(Auth::user()->hasPermission('production.valider-conditionnement') && $op->conditionnement->statut === 'termine')
                            <form action="{{ route('ordre-productions.valider-conditionnement', $op->id) }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                                    ✓ Valider le conditionnement et clôturer l'OP
                                </button>
                            </form>
                        @endif
                    </div>

                @elseif($toutesValidees)
                    {{-- Formulaire de conditionnement --}}
                    <form action="{{ route('ordre-productions.conditionner', $op->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Équipe de conditionnement <span class="text-rose-500">*</span></label>
                                <select name="equipe_id" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    <option value="">Sélectionnez une équipe</option>
                                    @foreach ($equipes as $equipe)
                                        <option value="{{ $equipe->id }}">{{ $equipe->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Type d'emballage <span class="text-rose-500">*</span></label>
                                <input type="text" name="type_emballage" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Ex: Sachet kraft 500g" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Quantité PF produite <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="quantite_produite" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Ex: 180" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Quantité MP consommée <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="quantite_mp_consommee" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" placeholder="Ex: 500" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Date de fabrication <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_fabrication" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Date de péremption <span class="text-rose-500">*</span></label>
                                <input type="date" name="date_peremption" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm shadow-emerald-600/20 transition-colors">
                                Enregistrer le conditionnement
                            </button>
                        </div>
                    </form>

                @else
                    {{-- Conditionnement verrouillé --}}
                    <div class="text-center py-10 text-slate-400">
                        <span class="text-5xl">🔒</span>
                        <p class="mt-3 text-sm font-medium">Conditionnement verrouillé</p>
                        <p class="text-xs mt-1">Accessible uniquement après la validation complète de toutes les étapes de transformation.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Modal interrompre --}}
    @if(Auth::user()->hasPermission('production.interrompre') && in_array($op->statut, ['en_cours', 'conditionne']))
    <div id="modal-interrompre" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Interrompre la production</h3>
                <p class="text-sm text-slate-500 mt-1">Cette action suspendra toutes les phases en cours.</p>
            </div>
            <form action="{{ route('ordre-productions.interrompre', $op->id) }}" method="POST">
                @csrf
                <div class="px-6 py-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Motif (optionnel)</label>
                    <textarea name="motif" rows="3" placeholder="Ex : Panne machine, attente approvisionnement…"
                              class="w-full rounded-xl border-slate-200 shadow-sm focus:border-rose-400 focus:ring-rose-400 text-sm"></textarea>
                </div>
                <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-interrompre').classList.add('hidden')"
                            class="px-5 py-2.5 text-slate-700 text-sm font-semibold border border-slate-300 rounded-xl hover:bg-white transition-colors">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl transition-colors">
                        ⛔ Confirmer l'interruption
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($op->qr_code_path)
        <input type="checkbox" id="modal-qr-toggle" class="peer hidden" />
        <div class="fixed inset-0 z-[60] hidden peer-checked:flex items-center justify-center p-4">
            <label for="modal-qr-toggle" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm cursor-pointer animate-fade-in"></label>
            
            <div class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-modal-pop text-center p-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-slate-900">QR Code de l'OP</h3>
                    <label for="modal-qr-toggle" class="p-2 hover:bg-slate-100 rounded-full cursor-pointer text-slate-400 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </label>
                </div>
                
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-xl inline-block border border-slate-200">
                        <img src="{{ $op->qr_code_path }}" alt="QR Code" class="w-48 h-48 object-contain">
                    </div>
                </div>
                
                <p class="text-sm text-slate-500 mb-6">Code OP : <span class="font-bold text-slate-900">{{ $op->code }}</span></p>
                
                <a href="{{ $op->qr_code_path }}" download="QR_Code_{{ $op->code }}.png" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                    Télécharger
                </a>
            </div>
        </div>
    @endif
</x-app-layout>
