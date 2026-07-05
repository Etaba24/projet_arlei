<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start md:items-center flex-col md:flex-row w-full gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Nouveau Lancement de Production</h1>
                <p class="text-sm text-slate-500 mt-1">Configurez les lots, les phases, puis lancez la simulation IA avant de confirmer.</p>
            </div>
            <div class="ml-auto">
                <a href="{{ route('ordre-productions.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </x-slot>

    @php
    // Pass PHP data to Alpine.js as JSON
    $lotsFlat = [];
    foreach ($lotsGrouped as $mpId => $lots) {
        $mp = $lots->first()->matierePremiere;
        foreach ($lots as $lot) {
            $lotsFlat[] = [
                'id'                  => $lot->id,
                'code_lot'            => $lot->code_lot,
                'matiere_premiere_id' => $mpId,
                'matiere_libelle'     => $mp->libelle,
                'matiere_unite'       => $mp->unite_mesure,
                'date_reception'      => $lot->date_reception->format('Y-m-d'),
                'quantite_disponible' => (float)$lot->quantite_disponible,
                'qualite_label'       => $lot->qualite['label'],
                'qualite_loss'        => $lot->qualite['lossRate'],
                'qualite_score'       => $lot->qualite['score'],
            ];
        }
    }
    $datasim = [
        'produits'        => $produits->map(fn($p) => ['id'=>$p->id,'designation'=>$p->designation,'unite'=>$p->unite_mesure])->values(),
        'lots'            => $lotsFlat,
        'employes'        => $employes->map(fn($e) => ['id'=>$e->id,'nom'=>trim($e->nom.' '.($e->prenom??''))])->values(),
        'transformations' => $transformations->map(fn($t) => ['id'=>$t->id,'designation'=>$t->designation,'description'=>$t->description??''])->values(),
        'equipes'         => $equipes->map(fn($e) => ['id'=>$e->id,'nom'=>$e->nom])->values(),
        'machines'        => $machines->map(fn($m) => ['id'=>$m->id,'designation'=>$m->designation,'code'=>$m->code,'etat'=>$m->etat])->values(),
        'produitsSemiFinis' => $produitsSemiFinis->map(fn($p) => ['id'=>$p->id,'designation'=>$p->designation])->values(),
    ];
    @endphp

    <style>
        @keyframes orbit { 0%{transform:rotate(0deg) translateX(14px) rotate(0deg)}100%{transform:rotate(360deg) translateX(14px) rotate(-360deg)} }
        @keyframes particleGlow { 0%,100%{box-shadow:0 0 6px 2px #10b981,0 0 20px 4px #10b98155}50%{box-shadow:0 0 14px 6px #10b981,0 0 40px 10px #10b98133} }
        @keyframes logIn { from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)} }
        @keyframes termBlink { 0%,49%{opacity:1}50%,100%{opacity:0} }
        @keyframes scoreBar { from{width:0}to{width:var(--w)} }
        .animate-orbit { animation: orbit 2s linear infinite; }
        .animate-pglow { animation: particleGlow 1.2s ease-in-out infinite; }
        .animate-log-in { animation: logIn 0.35s ease-out forwards; }
        .animate-tblink { animation: termBlink 1s step-end infinite; }
    </style>

    <div x-data="simulateur({{ json_encode($datasim) }})" class="max-w-5xl mx-auto space-y-5">

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
            <p class="text-sm font-bold text-rose-700">{{ $errors->first() }}</p>
        </div>
        @endif

        <form x-ref="mainForm" action="{{ route('ordre-productions.store') }}" method="POST">
            @csrf

            {{-- HIDDEN fields for AI simulation results --}}
            <input type="hidden" name="quantite_pf_estimee" x-bind:value="simResults ? simResults.estimatedPF.toFixed(3) : ''">
            <input type="hidden" name="taux_perte_estime"   x-bind:value="simResults ? (simResults.totalLossRate * 100).toFixed(2) : ''">
            <input type="hidden" name="duree_estimee_min"   x-bind:value="simResults ? simResults.totalDurationMin : ''">

            {{-- ══ 1 — CIBLE ══ --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">1</span>
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Responsable, Date & Produit Cible</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Responsable du lancement <span class="text-rose-500">*</span></label>
                        <select name="employe_id" x-model="employeId" required class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="">Sélectionnez un employé</option>
                            @foreach($employes as $e)
                                <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Date de début <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="date_debut" x-model="dateDebut"
                               value="{{ old('date_debut', now()->format('Y-m-d\TH:i')) }}" required
                               class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Produit fini à produire <span class="text-rose-500">*</span></label>
                        <select name="produit_fini_id" x-model="produitFiniId" required class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="">Sélectionnez un produit fini</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" {{ old('produit_fini_id')==$p->id?'selected':'' }}>{{ $p->designation }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Quantité cible à produire</label>
                        <div class="relative">
                            <input type="number" step="0.001" name="quantite_pf_cible" x-model.number="quantitePfCible"
                                   placeholder="Ex : 480" class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm pr-16">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium pointer-events-none"
                                  x-text="produit ? produit.unite : ''"></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Optionnel — utilisé par la simulation pour calculer le rendement</p>
                    </div>
                </div>
            </div>

            {{-- ══ 2 — LOTS DE MATIÈRES PREMIÈRES ══ --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">2</span>
                        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Lots de Matières Premières</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-500">Total MP : <strong class="text-slate-900" x-text="totalMpQte.toLocaleString('fr-FR',{minimumFractionDigits:2})"></strong></span>
                        <button type="button" @click="addLot()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Ajouter un lot
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <template x-for="(sel, i) in selectedLots" :key="i">
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600" x-text="'Lot ' + (i+1)"></span>
                                <button type="button" @click="removeLot(i)" x-show="selectedLots.length > 1"
                                        class="p-1 text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Select lot --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Lot de matière première <span class="text-rose-500">*</span></label>
                                    <select :name="'lots[' + i + '][lot_id]'"
                                            x-model="sel.lotId"
                                            @change="sel.quantite = 0"
                                            required
                                            class="block w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">— Sélectionnez un lot —</option>
                                        <template x-for="lot in allLots" :key="lot.id">
                                            <option :value="lot.id"
                                                    :selected="sel.lotId == lot.id"
                                                    :disabled="selectedLots.some((other, idx) => idx !== i && other.lotId == lot.id)"
                                                    x-text="lot.code_lot + ' — ' + lot.matiere_libelle + ' (' + lot.quantite_disponible.toLocaleString('fr-FR') + ' ' + lot.matiere_unite + ') — ' + lot.qualite_label"></option>
                                        </template>
                                    </select>
                                    {{-- Quality badge for selected lot --}}
                                    <template x-if="getLot(sel.lotId)">
                                        <div class="mt-2 flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <template x-for="n in 5" :key="n">
                                                    <div class="w-3 h-3 rounded-full transition-colors" :class="n <= getLot(sel.lotId).qualite_score ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                                                </template>
                                            </div>
                                            <span class="text-xs font-semibold" :class="{
                                                'text-emerald-600': getLot(sel.lotId).qualite_score >= 4,
                                                'text-amber-600': getLot(sel.lotId).qualite_score === 3,
                                                'text-orange-600': getLot(sel.lotId).qualite_score === 2,
                                                'text-red-600': getLot(sel.lotId).qualite_score <= 1,
                                            }" x-text="'Qualité : ' + getLot(sel.lotId).qualite_label"></span>
                                            <span class="text-xs text-slate-400" x-text="'Réceptionné le ' + new Date(getLot(sel.lotId).date_reception + 'T00:00:00').toLocaleDateString('fr-FR')"></span>
                                        </div>
                                    </template>
                                </div>
                                {{-- Quantity --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Quantité à injecter <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" step="0.001"
                                               :name="'lots[' + i + '][quantite]'"
                                               x-model.number="sel.quantite"
                                               :max="getLot(sel.lotId) ? getLot(sel.lotId).quantite_disponible : undefined"
                                               placeholder="0"
                                               required
                                               class="block w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 pr-12">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"
                                              x-text="getLot(sel.lotId) ? getLot(sel.lotId).matiere_unite : ''"></span>
                                    </div>
                                    <p class="mt-1 text-xs text-rose-600 font-semibold"
                                       x-show="getLot(sel.lotId) && sel.quantite > getLot(sel.lotId).quantite_disponible">
                                        ⚠ Dépasse le stock disponible
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <p x-show="allLots.length === 0" class="text-sm text-slate-400 italic text-center py-4">
                        Aucun lot disponible. <a href="{{ route('lots.index') }}" class="text-emerald-600 font-semibold underline">Créer un lot</a>
                    </p>
                </div>
            </div>

            {{-- ══ 3 — PHASES DYNAMIQUES ══ --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">3</span>
                        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Phases de Transformation</h2>
                        <span class="text-xs text-slate-400">Obligatoire — la 1ère est toujours initiale, la dernière finale</span>
                    </div>
                    <button type="button" @click="addPhase()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Ajouter une phase
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid gap-4" :class="phases.length <= 3 ? 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3' : 'grid-cols-1 md:grid-cols-2'">
                        <template x-for="(phase, i) in phases" :key="i">
                            <div class="border-2 rounded-xl overflow-hidden transition-all"
                                 :class="phase.transformationId && phase.equipeId && phase.machineId ? 'border-emerald-300' : 'border-slate-200'">
                                {{-- Phase header --}}
                                <div class="px-4 py-2.5 flex items-center justify-between"
                                     :class="{
                                         'bg-emerald-500': i === 0,
                                         'bg-violet-500': i > 0 && i < phases.length - 1,
                                         'bg-sky-500': i === phases.length - 1 && phases.length > 1,
                                     }">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-white text-[10px] font-black" x-text="i+1"></span>
                                        <span class="text-xs font-black text-white" x-text="phases.length === 1 ? 'Phase Unique (Initiale + Finale)' : (i === 0 ? 'Phase Initiale' : (i === phases.length-1 ? 'Phase Finale' : 'Phase Intermédiaire ' + i))"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg x-show="phase.transformationId && phase.equipeId && phase.machineId" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <button type="button" @click="removePhase(i)" x-show="i > 0 && i < phases.length - 1"
                                                class="text-white/70 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                {{-- Phase fields --}}
                                <div class="p-4 space-y-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Transformation <span class="text-rose-500">*</span></label>
                                        <select :name="'phases[' + i + '][transformation_id]'"
                                                x-model="phase.transformationId"
                                                required
                                                class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">— Choisir —</option>
                                            @foreach($transformations as $t)
                                                <option value="{{ $t->id }}">{{ $t->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Équipe <span class="text-rose-500">*</span></label>
                                        <select :name="'phases[' + i + '][equipe_id]'"
                                                x-model="phase.equipeId"
                                                required
                                                class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">— Choisir —</option>
                                            @foreach($equipes as $e)
                                                <option value="{{ $e->id }}">{{ $e->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Machine <span class="text-rose-500">*</span></label>
                                        <select :name="'phases[' + i + '][machine_id]'"
                                                x-model="phase.machineId"
                                                required
                                                class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">— Choisir —</option>
                                            @foreach($machines as $m)
                                                <option value="{{ $m->id }}"
                                                        class="{{ $m->etat==='en_panne' ? 'text-rose-500' : ($m->etat==='arret' ? 'text-amber-600' : '') }}">
                                                    {{ $m->designation }}
                                                    @if($m->etat !== 'en_marche') ({{ $m->etat }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Date et Heure d'Attribution <span class="text-rose-500">*</span></label>
                                        <input type="datetime-local" :name="'phases[' + i + '][date_attribution]'"
                                               x-model="phase.date_attribution"
                                               required
                                               @input="validerDatesPhases()"
                                               class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Matière / État à transformer</label>
                                        <select @change="setPhaseInputSource(i, $event.target.value)"
                                                class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="" :selected="!phase.matierePremiereId && !phase.produitSemiFiniId">— Héritée des lots principaux —</option>
                                            <template x-for="src in getAvailableInputMatieres(i)" :key="src.type + '-' + src.id">
                                                <option :value="src.type + ':' + src.id"
                                                        :selected="(src.type==='mp' && phase.matierePremiereId==src.id) || (src.type==='psf' && phase.produitSemiFiniId==src.id)"
                                                        x-text="src.libelle"></option>
                                            </template>
                                        </select>
                                        <input type="hidden" :name="'phases[' + i + '][matiere_premiere_id]'" x-model="phase.matierePremiereId">
                                        <input type="hidden" :name="'phases[' + i + '][produit_semi_fini_id]'" x-model="phase.produitSemiFiniId">
                                    </div>
                                    <div x-show="phase.matierePremiereId || phase.produitSemiFiniId" class="col-span-full">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Quantité à transformer (Optionnel)</label>
                                        <input type="number" step="0.001" :name="'phases[' + i + '][quantite_mp_phase]'"
                                               x-model="phase.quantiteMpPhase" placeholder="Quantité pour cette phase"
                                               class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>

                                    <!-- État / Produit Semi-Fini Obtenu (pour phases intermédiaires) -->
                                    <div x-show="i < phases.length - 1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">État / Produit Semi-Fini Obtenu</label>
                                        <div class="flex gap-1.5">
                                            <select :name="'phases[' + i + '][produit_semi_fini_obtenu_id]'"
                                                    x-model="phase.produitSemiFiniObtenuId"
                                                    class="block flex-1 rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                                <option value="">— Aucun (Pas de nouvel état) —</option>
                                                <template x-for="psf in allProduitsSemiFinis" :key="psf.id">
                                                    <option :value="psf.id" x-text="psf.designation"></option>
                                                </template>
                                            </select>
                                            <button type="button" @click="quickAddPsf(i)"
                                                    class="inline-flex items-center justify-center p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition-colors border border-emerald-200"
                                                    title="Créer un nouveau produit semi-fini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quantité Obtenue (pour phases intermédiaires) -->
                                    <div x-show="i < phases.length - 1 && phase.produitSemiFiniObtenuId" class="col-span-full">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Quantité Obtenue (Optionnelle)</label>
                                        <input type="number" step="0.001" :name="'phases[' + i + '][quantite_obtenue]'"
                                                x-model="phase.quantiteObtenue" placeholder="Quantité de produit semi-fini obtenue"
                                                class="block w-full rounded-lg border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>

                                    <!-- Produit Fini Obtenu (pour phase finale) -->
                                    <div x-show="i === phases.length - 1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Produit Fini Obtenu <span class="text-rose-500">*</span></label>
                                        <input type="text" readonly :value="produit ? produit.designation : 'Veuillez sélectionner un produit fini en haut'"
                                               class="block w-full rounded-lg border-slate-200 bg-slate-50 text-xs text-slate-600 font-bold border border-slate-300">
                                        <input type="hidden" :name="'phases[' + i + '][produit_semi_fini_obtenu_id]'" value="">
                                    </div>

                                    <!-- Quantité Obtenue / Cible (pour phase finale) -->
                                    <div x-show="i === phases.length - 1" class="col-span-full">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Quantité Cible <span class="text-slate-400 font-normal">(héritée)</span></label>
                                        <input type="text" readonly :value="produit ? (quantitePfCible ? quantitePfCible + ' ' + (produit.unite_mesure || 'Kg') : 'Non spécifiée') : '—'"
                                               class="block w-full rounded-lg border-slate-200 bg-slate-50 text-xs text-slate-500 font-medium">
                                        <input type="hidden" :name="'phases[' + i + '][quantite_obtenue]'" value="">
                                    </div>
                                    </div>
                                    <div class="col-span-full">
                                        {{-- Machine efficiency badge --}}
                                        <template x-if="getMachine(phase.machineId)">
                                            <p class="mt-1 text-[10px] font-semibold"
                                               :class="getMachine(phase.machineId).etat === 'en_marche' ? 'text-emerald-600' : (getMachine(phase.machineId).etat === 'en_panne' ? 'text-rose-600' : 'text-amber-600')"
                                               x-text="'Efficacité : ' + Math.round(machineEfficiency(getMachine(phase.machineId).etat)*100) + '%' + (getMachine(phase.machineId).etat==='en_panne' ? ' — INUTILISABLE' : '')">
                                            </p>
                                        </template>
                                    </div>
                                    {{-- Estimated duration for this phase (AI) --}}
                                    <input type="hidden" :name="'phases[' + i + '][duree_estimee_min]'"
                                           :value="phase.machineId ? Math.round(90 / Math.max(0.1, machineEfficiency(getMachine(phase.machineId) ? getMachine(phase.machineId).etat : 'en_marche'))) : ''">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-between pb-4">
                <a href="{{ route('ordre-productions.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-medium">Annuler</a>
                <div class="flex items-center gap-3">
                    <p x-show="stockErrors.length > 0" class="text-xs text-rose-600 font-semibold">⚠ Stock insuffisant sur certains lots</p>
                    <button type="button" @click="lancerSimulation()"
                            class="inline-flex items-center gap-2.5 px-7 py-3 bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-slate-900/20 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Lancer la Simulation IA
                    </button>
                </div>
            </div>

        </form>


        {{-- ═══════════════ SIMULATION OVERLAY ═══════════════ --}}
        <div x-show="showSimulation"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[90] bg-slate-950/90 backdrop-blur-sm overflow-y-auto flex items-start justify-center p-4 py-8"
             style="display:none">

            {{-- ── PHASE ANIMATION ── --}}
            <div x-show="simPhase === 'running'"
                 x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-105"
                 class="w-full max-w-4xl bg-slate-900 rounded-3xl border border-slate-700 shadow-2xl overflow-hidden">

                {{-- Header --}}
                <div class="px-8 py-5 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="relative w-10 h-10 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full border-2 border-emerald-500/20"></div>
                            <div class="absolute w-2.5 h-2.5 rounded-full bg-emerald-400 animate-orbit" style="top:calc(50% - 5px);left:calc(50% - 5px)"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-bold text-white">SIMULATION IA — ANALYSE EN COURS</p>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    LIVE
                                </span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5 font-mono" x-text="animCurrentLabel"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-500 font-mono tracking-widest">ÉTAPE</p>
                        <p class="text-2xl font-black font-mono text-white"><span x-text="animStep"></span><span class="text-slate-600">/9</span></p>
                    </div>
                </div>

                {{-- PIPELINE --}}
                <div class="px-8 pt-6 pb-4 bg-slate-950/50">
                    <div class="mx-5">
                        {{-- Labels --}}
                        <div class="relative h-9 mb-2">
                            <div class="absolute -translate-x-1/2 text-center" style="left:0%">
                                <p class="text-[9px] font-black uppercase tracking-widest transition-colors duration-500" :class="animStep>=3?'text-emerald-400':'text-slate-600'">Lots MP</p>
                            </div>
                            <div class="absolute -translate-x-1/2 text-center" style="left:25%">
                                <p class="text-[9px] font-black uppercase tracking-widest transition-colors duration-500" :class="animStep>=4?'text-emerald-400':'text-slate-600'">Phase 1</p>
                                <p class="text-[9px] text-slate-600 truncate max-w-[5rem]" x-text="phases[0] && getTransformation(phases[0].transformationId) ? getTransformation(phases[0].transformationId).designation : 'Initiale'"></p>
                            </div>
                            <div class="absolute -translate-x-1/2 text-center" style="left:50%">
                                <p class="text-[9px] font-black uppercase tracking-widest transition-colors duration-500" :class="animStep>=5?'text-amber-400':'text-slate-600'">Phases Int.</p>
                                <p class="text-[9px] text-slate-600" x-text="phases.length > 2 ? (phases.length - 2) + ' phase(s)' : '—'"></p>
                            </div>
                            <div class="absolute -translate-x-1/2 text-center" style="left:75%">
                                <p class="text-[9px] font-black uppercase tracking-widest transition-colors duration-500" :class="animStep>=6?'text-violet-400':'text-slate-600'"
                                   x-text="'Phase ' + phases.length"></p>
                                <p class="text-[9px] text-slate-600 truncate max-w-[5rem]" x-text="phases[phases.length-1] && getTransformation(phases[phases.length-1].transformationId) ? getTransformation(phases[phases.length-1].transformationId).designation : 'Finale'"></p>
                            </div>
                            <div class="absolute -translate-x-1/2 text-center" style="left:100%">
                                <p class="text-[9px] font-black uppercase tracking-widest transition-colors duration-500" :class="animStep>=8?'text-sky-400':'text-slate-600'">PF</p>
                                <p class="text-[9px] text-slate-600 truncate max-w-[5rem]" x-text="produit ? produit.designation : ''"></p>
                            </div>
                        </div>
                        {{-- Pipeline track --}}
                        <div class="relative h-14" style="overflow:visible">
                            <div class="absolute top-1/2 left-0 right-0 h-[3px] -translate-y-1/2 bg-slate-800 rounded-full"></div>
                            <div class="absolute top-1/2 left-0 h-[3px] -translate-y-1/2 rounded-full transition-[width] duration-700 ease-out"
                                 style="background:linear-gradient(90deg,#10b981 0%,#f59e0b 50%,#8b5cf6 100%)"
                                 :style="'width:'+particleProgress+'%'"></div>
                            {{-- Nodes --}}
                            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10" style="left:0%">
                                <div class="w-11 h-11 rounded-full border-2 flex items-center justify-center transition-all duration-500"
                                     :class="animStep>=3?'border-emerald-500 bg-emerald-950 shadow-[0_0_16px_4px_rgba(16,185,129,0.35)]':'border-slate-700 bg-slate-900'">
                                    <svg class="w-5 h-5 transition-colors" :class="animStep>=3?'text-emerald-400':'text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <div x-show="animStep===3" class="absolute -inset-2.5 rounded-full border border-emerald-500/40 animate-ping pointer-events-none"></div>
                            </div>
                            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10" style="left:25%">
                                <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center transition-all duration-500"
                                     :class="animStep>=4?'border-emerald-500 bg-emerald-950 shadow-[0_0_20px_6px_rgba(16,185,129,0.4)] scale-110':'border-slate-700 bg-slate-900'">
                                    <svg class="w-5 h-5 transition-colors" :class="animStep>=4?'text-emerald-400':'text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                                </div>
                                <div x-show="animStep===4" class="absolute -inset-3 rounded-full border border-emerald-400/50 animate-ping pointer-events-none"></div>
                            </div>
                            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10" style="left:50%">
                                <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center transition-all duration-500"
                                     :class="animStep>=5?'border-amber-500 bg-amber-950 shadow-[0_0_20px_6px_rgba(245,158,11,0.4)] scale-110':'border-slate-700 bg-slate-900'">
                                    <svg class="w-5 h-5 transition-colors" :class="animStep>=5?'text-amber-400':'text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div x-show="animStep===5" class="absolute -inset-3 rounded-full border border-amber-400/50 animate-ping pointer-events-none"></div>
                            </div>
                            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10" style="left:75%">
                                <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center transition-all duration-500"
                                     :class="animStep>=6?'border-violet-500 bg-violet-950 shadow-[0_0_20px_6px_rgba(139,92,246,0.4)] scale-110':'border-slate-700 bg-slate-900'">
                                    <svg class="w-5 h-5 transition-colors" :class="animStep>=6?'text-violet-400':'text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/></svg>
                                </div>
                                <div x-show="animStep===6" class="absolute -inset-3 rounded-full border border-violet-400/50 animate-ping pointer-events-none"></div>
                            </div>
                            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-10" style="left:100%">
                                <div class="w-11 h-11 rounded-full border-2 flex items-center justify-center transition-all duration-500"
                                     :class="animStep>=8?'border-sky-500 bg-sky-950 shadow-[0_0_16px_4px_rgba(14,165,233,0.4)]':'border-slate-700 bg-slate-900'">
                                    <svg class="w-5 h-5 transition-colors" :class="animStep>=8?'text-sky-400':'text-slate-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </div>
                                <div x-show="animStep>=8" class="absolute -inset-2.5 rounded-full border border-sky-400/40 animate-ping pointer-events-none"></div>
                            </div>
                            {{-- Particle --}}
                            <div x-show="animStep>=3" style="display:none"
                                 class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-20 w-6 h-6 rounded-full animate-pglow"
                                 :style="'left:'+particleProgress+'%; transition:left 0.75s cubic-bezier(0.4,0,0.2,1)'">
                                <div class="absolute inset-0 rounded-full bg-emerald-400"></div>
                                <div class="absolute inset-0 rounded-full bg-emerald-300/70 animate-ping"></div>
                            </div>
                        </div>
                    </div>
                    {{-- Global progress --}}
                    <div class="mt-5 px-2">
                        <div class="flex justify-between text-[10px] text-slate-500 font-mono mb-1.5">
                            <span class="tracking-widest">ANALYSE GLOBALE</span>
                            <span x-text="Math.round(particleProgress) + '%'"></span>
                        </div>
                        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-[width] duration-700 ease-out"
                                 style="background:linear-gradient(90deg,#10b981 0%,#f59e0b 50%,#8b5cf6 100%)"
                                 :style="'width:'+particleProgress+'%'"></div>
                        </div>
                    </div>
                </div>

                {{-- LOG TERMINAL --}}
                <div class="px-8 py-5 border-t border-slate-800 bg-slate-950/80">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex gap-1.5"><div class="w-3 h-3 rounded-full bg-rose-500/60"></div><div class="w-3 h-3 rounded-full bg-amber-500/60"></div><div class="w-3 h-3 rounded-full bg-emerald-500/60"></div></div>
                        <span class="text-[10px] font-mono text-slate-500 ml-1">ia_simulation.log</span>
                    </div>
                    <div class="font-mono text-xs space-y-1.5 h-[130px] overflow-y-auto" id="simLog">
                        <template x-for="(entry, i) in animLog" :key="i">
                            <div class="flex items-start gap-3 animate-log-in">
                                <span class="text-slate-600 shrink-0 tabular-nums" x-text="entry.time"></span>
                                <span :class="{
                                    'text-sky-400':   entry.type==='info',
                                    'text-emerald-400': entry.type==='success'||entry.type==='phase',
                                    'text-amber-400': entry.type==='warn',
                                    'text-violet-400':entry.type==='ai',
                                    'text-rose-400':  entry.type==='error',
                                    'text-white font-bold': entry.type==='done',
                                }" x-text="entry.msg"></span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1 text-emerald-400" x-show="animStep < 9">
                            <span>›</span><span class="animate-tblink">_</span>
                        </div>
                    </div>
                </div>
            </div>{{-- /running --}}


            {{-- ── PHASE RÉSULTATS IA + VALIDATION ── --}}
            <div x-show="simPhase === 'complete'"
                 x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">

                <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-8 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-bold text-white">Rapport de Simulation IA</h2>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-400/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold">ANALYSÉ</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5" x-text="simResults && simResults.confiance !== undefined ? 'Modèle entraîné sur ' + simResults.nHistorique + ' phase(s) réelle(s) — Monte Carlo 1 500 scénarios' : 'Estimations basées sur la qualité des lots et l\'état des machines'"></p>
                        </div>
                    </div>
                    <button @click="resetSim()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-8 space-y-6">

                    {{-- IA METRICS (4 cards) --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-slate-900 rounded-2xl p-4">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Durée estimée</p>
                            <p class="text-2xl font-black text-white" x-text="simResults ? simResults.durationLabel : '—'"></p>
                            <p class="text-xs text-slate-500 mt-1" x-text="simResults ? simResults.totalDurationMin + ' min totales' : ''"></p>
                        </div>
                        <div class="rounded-2xl p-4" :class="simResults && simResults.totalLossRate > 0.15 ? 'bg-rose-50 border border-rose-200' : 'bg-amber-50 border border-amber-100'">
                            <p class="text-[9px] font-black uppercase tracking-widest mb-2" :class="simResults && simResults.totalLossRate > 0.15 ? 'text-rose-500' : 'text-amber-600'">Taux de perte IA</p>
                            <p class="text-2xl font-black" :class="simResults && simResults.totalLossRate > 0.15 ? 'text-rose-700' : 'text-amber-700'" x-text="simResults ? (simResults.totalLossRate*100).toFixed(1) + '%' : '—'"></p>
                            <p class="text-xs mt-1" :class="simResults && simResults.totalLossRate > 0.15 ? 'text-rose-400' : 'text-amber-400'" x-text="simResults ? 'Lot: ' + (simResults.lotLoss*100).toFixed(1) + '% + Machine: ' + (simResults.machineLoss*100).toFixed(1) + '%' : ''"></p>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-2">PF estimé</p>
                            <p class="text-2xl font-black text-emerald-700" x-text="simResults ? simResults.estimatedPF.toLocaleString('fr-FR',{maximumFractionDigits:1}) : '—'"></p>
                            <p class="text-xs text-emerald-500 mt-1" x-text="produit ? produit.unite : ''"></p>
                        </div>
                        <div class="bg-violet-50 border border-violet-100 rounded-2xl p-4">
                            <p class="text-[9px] font-black text-violet-600 uppercase tracking-widest mb-2">Rendement</p>
                            <p class="text-2xl font-black text-violet-700" x-text="simResults ? Math.round((1-simResults.totalLossRate)*100) + '%' : '—'"></p>
                            <p class="text-xs text-violet-400 mt-1" x-text="quantitePfCible && simResults ? 'Cible : ' + parseFloat(quantitePfCible).toLocaleString('fr-FR') + ' ' + (produit ? produit.unite : '') : 'Cible non définie'"></p>
                        </div>
                    </div>

                    {{-- INTELLIGENCE PRÉDICTIVE (moteur serveur) --}}
                    <div class="bg-slate-900 rounded-2xl p-5 space-y-5" x-show="simResults && simResults.confiance !== undefined">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Intelligence Prédictive — Analyse probabiliste</p>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                  :class="simResults && simResults.confiance >= 65 ? 'bg-emerald-400/15 border-emerald-400/30 text-emerald-300' : (simResults && simResults.confiance >= 40 ? 'bg-amber-400/15 border-amber-400/30 text-amber-300' : 'bg-rose-400/15 border-rose-400/30 text-rose-300')"
                                  x-text="simResults ? 'Confiance ' + simResults.confiance + '%' : ''"></span>
                        </div>

                        {{-- Intervalle de confiance P10–P90 --}}
                        <div>
                            <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                                <span>Scénario pessimiste (P10)</span>
                                <span class="font-bold text-white">Production probable (P50)</span>
                                <span>Scénario optimiste (P90)</span>
                            </div>
                            <div class="relative h-3 bg-slate-700 rounded-full overflow-hidden">
                                <div class="absolute inset-y-0 bg-gradient-to-r from-amber-400/60 via-emerald-500 to-emerald-400/60 rounded-full"
                                     :style="simResults && simResults.pfP90 ? 'left:8%; right:8%' : ''"></div>
                            </div>
                            <div class="flex justify-between mt-1.5" style="font-variant-numeric: tabular-nums">
                                <span class="text-sm font-black text-amber-300" x-text="simResults && simResults.pfP10 !== undefined ? simResults.pfP10.toLocaleString('fr-FR',{maximumFractionDigits:1}) : '—'"></span>
                                <span class="text-lg font-black text-emerald-400" x-text="simResults ? simResults.estimatedPF.toLocaleString('fr-FR',{maximumFractionDigits:1}) + ' ' + (produit ? produit.unite : '') : '—'"></span>
                                <span class="text-sm font-black text-emerald-300" x-text="simResults && simResults.pfP90 !== undefined ? simResults.pfP90.toLocaleString('fr-FR',{maximumFractionDigits:1}) : '—'"></span>
                            </div>
                        </div>

                        {{-- Probabilité d'atteindre la cible --}}
                        <template x-if="simResults && simResults.probaCible !== null && simResults.probaCible !== undefined">
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-slate-400">Probabilité d'atteindre la cible de <span class="text-white font-bold" x-text="parseFloat(quantitePfCible).toLocaleString('fr-FR')"></span></span>
                                    <span class="font-black" :class="simResults.probaCible >= 0.7 ? 'text-emerald-400' : (simResults.probaCible >= 0.4 ? 'text-amber-400' : 'text-rose-400')"
                                          x-text="Math.round(simResults.probaCible * 100) + '%'"></span>
                                </div>
                                <div class="h-2.5 bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-1000"
                                         :class="simResults.probaCible >= 0.7 ? 'bg-emerald-500' : (simResults.probaCible >= 0.4 ? 'bg-amber-400' : 'bg-rose-500')"
                                         :style="'width:' + Math.round(simResults.probaCible * 100) + '%'"></div>
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-500 pt-1 border-t border-slate-700/60">
                            <span x-text="simResults && simResults.durP90 !== undefined ? 'Durée : ' + simResults.totalDurationMin + ' min (P50) — jusqu\'à ' + simResults.durP90 + ' min (P90)' : ''"></span>
                            <span x-text="simResults && simResults.biais !== undefined && simResults.biais !== 1 ? 'Auto-calibration : biais correctif ×' + simResults.biais : ''"></span>
                        </div>
                    </div>

                    {{-- RISQUES DÉTECTÉS --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5" x-show="simResults && simResults.risques && simResults.risques.length">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Risques détectés par le moteur</p>
                        <div class="space-y-2">
                            <template x-for="(r, ri) in (simResults ? simResults.risques || [] : [])" :key="ri">
                                <div class="flex items-start gap-3 rounded-xl px-3.5 py-2.5 border"
                                     :class="r.niveau === 'critique' ? 'bg-rose-50 border-rose-200' : (r.niveau === 'attention' ? 'bg-amber-50 border-amber-200' : 'bg-sky-50 border-sky-200')">
                                    <span class="mt-0.5 shrink-0 w-2 h-2 rounded-full"
                                          :class="r.niveau === 'critique' ? 'bg-rose-500' : (r.niveau === 'attention' ? 'bg-amber-500' : 'bg-sky-500')"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold" :class="r.niveau === 'critique' ? 'text-rose-700' : (r.niveau === 'attention' ? 'text-amber-700' : 'text-sky-700')" x-text="r.titre"></p>
                                        <p class="text-xs text-slate-600 mt-0.5" x-text="r.detail"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- RECOMMANDATIONS --}}
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5" x-show="simResults && simResults.recommandations && simResults.recommandations.length">
                        <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest mb-3">Recommandations du moteur</p>
                        <ul class="space-y-2">
                            <template x-for="(reco, rci) in (simResults ? simResults.recommandations || [] : [])" :key="rci">
                                <li class="flex items-start gap-2.5 text-xs text-emerald-900">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    <span x-text="reco"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- RENDEMENTS APPRIS PAR PHASE --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5" x-show="simResults && simResults.phasesDetail && simResults.phasesDetail.length">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Rendements appris par phase</p>
                        <div class="space-y-2">
                            <template x-for="(pd, pdi) in (simResults ? simResults.phasesDetail || [] : [])" :key="pdi">
                                <div class="flex items-center gap-4 text-xs">
                                    <div class="flex-1 min-w-0 font-semibold text-slate-700 truncate" x-text="pd.label"></div>
                                    <div class="w-28 shrink-0">
                                        <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" :style="'width:' + Math.round(pd.rendement * 100) + '%'"></div>
                                        </div>
                                    </div>
                                    <span class="w-24 text-right font-bold text-slate-800 shrink-0" style="font-variant-numeric: tabular-nums" x-text="(pd.rendement * 100).toFixed(1) + '% ±' + (pd.ecartType * 100).toFixed(1)"></span>
                                    <span class="w-16 text-right text-slate-500 shrink-0" x-text="pd.dureeMin + ' min'"></span>
                                    <span class="w-40 text-right text-slate-400 shrink-0 truncate" x-text="pd.source + ' (n≈' + pd.nObs + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- LOT QUALITY ANALYSIS --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Analyse des Lots — Qualité & Impact
                        </p>
                        <div class="space-y-3">
                            <template x-for="sel in selectedLots.filter(s => s.lotId && s.quantite > 0)" :key="sel.lotId">
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800 text-xs" x-text="getLot(sel.lotId) ? getLot(sel.lotId).code_lot + ' — ' + getLot(sel.lotId).matiere_libelle : ''"></p>
                                        <p class="text-xs text-slate-500" x-text="getLot(sel.lotId) ? 'Réception : ' + new Date(getLot(sel.lotId).date_reception + 'T00:00:00').toLocaleDateString('fr-FR') + ' — ' + getLot(sel.lotId).qualite_label : ''"></p>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <template x-for="n in 5" :key="n">
                                            <div class="w-2.5 h-2.5 rounded-full" :class="getLot(sel.lotId) && n <= getLot(sel.lotId).qualite_score ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                        </template>
                                    </div>
                                    <div class="text-right shrink-0 w-24">
                                        <p class="text-xs font-bold" :class="getLot(sel.lotId) && getLot(sel.lotId).qualite_loss > 0.1 ? 'text-rose-600' : 'text-slate-600'"
                                           x-text="getLot(sel.lotId) ? 'Perte : ' + (getLot(sel.lotId).qualite_loss*100).toFixed(1) + '%' : ''"></p>
                                        <p class="text-xs text-slate-400" x-text="sel.quantite.toLocaleString('fr-FR') + (getLot(sel.lotId) ? ' ' + getLot(sel.lotId).matiere_unite : '')"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- MACHINE ANALYSIS --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5" x-show="phases.some(p => p.machineId)">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                            État des Machines par Phase
                        </p>
                        <div class="space-y-2">
                            <template x-for="(phase, i) in phases" :key="i">
                                <div class="flex items-center gap-4" x-show="phase.machineId">
                                    <div class="w-20 shrink-0">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="i===0?'Initiale':(i===phases.length-1?'Finale':'Interm. '+i)"></span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold text-slate-700" x-text="getMachine(phase.machineId) ? getMachine(phase.machineId).designation : ''"></p>
                                    </div>
                                    <div class="w-32 shrink-0">
                                        <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-700"
                                                 :class="getMachine(phase.machineId) && getMachine(phase.machineId).etat==='en_marche' ? 'bg-emerald-500' : (getMachine(phase.machineId) && getMachine(phase.machineId).etat==='en_panne' ? 'bg-rose-500' : 'bg-amber-400')"
                                                 :style="'width:'+Math.round(machineEfficiency(getMachine(phase.machineId) ? getMachine(phase.machineId).etat : 'en_marche') * 100)+'%'"></div>
                                        </div>
                                    </div>
                                    <div class="w-20 text-right shrink-0">
                                        <span class="text-xs font-bold"
                                              :class="getMachine(phase.machineId) && getMachine(phase.machineId).etat==='en_marche' ? 'text-emerald-600' : (getMachine(phase.machineId) && getMachine(phase.machineId).etat==='en_panne' ? 'text-rose-600' : 'text-amber-600')"
                                              x-text="Math.round(machineEfficiency(getMachine(phase.machineId) ? getMachine(phase.machineId).etat : 'en_marche')*100) + '% eff.'"></span>
                                    </div>
                                    <div class="w-20 text-right shrink-0 text-xs text-slate-500"
                                         x-text="getMachine(phase.machineId) && machineEfficiency(getMachine(phase.machineId).etat) > 0 ? Math.round(90 / machineEfficiency(getMachine(phase.machineId).etat)) + ' min' : '—'">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- OBJECTIF vs ESTIMATION --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5" x-show="simResults">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Verdict IA</p>
                        <div class="flex items-center gap-6">
                            <div>
                                <p class="text-xs text-slate-400">MP injectée</p>
                                <p class="text-xl font-black text-white" x-text="totalMpQte.toLocaleString('fr-FR',{maximumFractionDigits:2})"></p>
                            </div>
                            <svg class="w-6 h-6 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            <div>
                                <p class="text-xs text-slate-400">Pertes estimées</p>
                                <p class="text-xl font-black text-rose-400" x-text="simResults ? '−' + (simResults.totalLossRate * totalMpQte).toLocaleString('fr-FR',{maximumFractionDigits:2}) : '—'"></p>
                            </div>
                            <svg class="w-6 h-6 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            <div>
                                <p class="text-xs text-slate-400">PF produit estimé</p>
                                <p class="text-2xl font-black text-emerald-400" x-text="simResults ? simResults.estimatedPF.toLocaleString('fr-FR',{maximumFractionDigits:1}) + ' ' + (produit ? produit.unite : '') : '—'"></p>
                            </div>
                            <template x-if="quantitePfCible && simResults">
                                <div class="ml-auto text-right">
                                    <p class="text-xs text-slate-400">Cible atteinte</p>
                                    <p class="text-xl font-black" :class="simResults.estimatedPF >= parseFloat(quantitePfCible) ? 'text-emerald-400' : 'text-amber-400'"
                                       x-text="simResults ? Math.round(simResults.estimatedPF / parseFloat(quantitePfCible) * 100) + '%' : ''"></p>
                                    <p class="text-xs text-slate-500" x-text="'sur ' + parseFloat(quantitePfCible).toLocaleString('fr-FR') + ' ' + (produit ? produit.unite : '')"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button @click="resetSim()" class="inline-flex items-center gap-2 px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Modifier & Relancer
                    </button>
                    <div class="flex items-center gap-3">
                        <span x-show="simResults && simResults.totalLossRate > 0.20" class="text-xs text-amber-600 font-semibold">⚠ Pertes élevées — vérifiez les lots et machines</span>
                        <button type="button" @click="confirmerEtCreer()"
                                :disabled="stockErrors.length > 0"
                                :class="stockErrors.length > 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-700 shadow-lg shadow-emerald-600/30'"
                                class="inline-flex items-center gap-2.5 px-8 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Confirmer et Lancer l'OP
                        </button>
                    </div>
                </div>
            </div>{{-- /complete --}}

        </div>{{-- /overlay --}}

        {{-- ═══════════════ QUICK MATIERE/STATE MODAL ═══════════════ --}}
        <div x-show="showQuickMatiereModal"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-sm overflow-y-auto flex items-center justify-center p-4"
             style="display:none">
            <div @click.away="showQuickMatiereModal = false"
                 class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-950 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wide">Nouveau Produit Semi-Fini</h3>
                    <button type="button" @click="showQuickMatiereModal = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Désignation / État obtenu <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="quickMatiereLibelle" placeholder="Ex : Manioc Épluché, Pâte de Manioc"
                               class="block w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showQuickMatiereModal = false"
                            class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl hover:bg-white transition-colors">
                        Annuler
                    </button>
                    <button type="button" @click="submitQuickMatiere()"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-md">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>{{-- /x-data --}}


    <script>
    // ── Machine efficiency factors ──
    const MACHINE_EFF = { 'en_marche': 1.0, 'arret': 0.65, 'en_panne': 0.0 };
    const BASE_PHASE_MIN = 90;

    function simulateur(data) {
        return {
            allProduits:        data.produits,
            allLots:            data.lots,
            allEmployes:        data.employes,
            allTransformations: data.transformations,
            allEquipes:         data.equipes,
            allMachines:        data.machines,
            allProduitsSemiFinis: data.produitsSemiFinis || [],

            produitFiniId:  '',
            quantitePfCible: 0,
            employeId:      '',
            dateDebut:      '{{ now()->format('Y-m-d\TH:i') }}',

            selectedLots: [{ lotId: '', quantite: 0 }],

            phases: [
                { transformationId: '', equipeId: '', machineId: '', date_attribution: '{{ now()->format("Y-m-d\TH:i") }}', matierePremiereId: '', produitSemiFiniId: '', quantiteMpPhase: '', produitSemiFiniObtenuId: '', quantiteObtenue: '' },
                { transformationId: '', equipeId: '', machineId: '', date_attribution: '{{ now()->addHours(2)->format("Y-m-d\TH:i") }}', matierePremiereId: '', produitSemiFiniId: '', quantiteMpPhase: '', produitSemiFiniObtenuId: '', quantiteObtenue: '' },
            ],

            showSimulation:   false,
            simPhase:         'idle',
            animStep:         0,
            particleProgress: 0,
            animCurrentLabel: 'Initialisation...',
            animLog:          [],
            simResults:       null,
            _timers:          [],

            showQuickMatiereModal: false,
            quickMatiereLibelle: '',
            quickMatierePhaseIndex: null,

            // ── Lookups ──
            get produit()  { return this.allProduits.find(p => p.id == this.produitFiniId) || null; },
            get totalMpQte(){ return this.selectedLots.reduce((s, l) => s + (parseFloat(l.quantite) || 0), 0); },
            getLot(lotId)  { return this.allLots.find(l => l.id == lotId) || null; },
            getMachine(machineId) { return this.allMachines.find(m => m.id == machineId) || null; },
            getTransformation(tId){ return this.allTransformations.find(t => t.id == tId) || null; },
            machineEfficiency(etat){ return MACHINE_EFF[etat] ?? 0.8; },

            get selectedMatieres() {
                const mpMap = new Map();
                this.selectedLots.forEach(s => {
                    const lot = this.getLot(s.lotId);
                    if (lot && !mpMap.has(lot.matiere_premiere_id)) {
                        mpMap.set(lot.matiere_premiere_id, {
                            id: lot.matiere_premiere_id,
                            libelle: lot.matiere_libelle
                        });
                    }
                });
                return Array.from(mpMap.values());
            },

            getAvailableInputMatieres(index) {
                const sources = [];
                const seen = new Set();
                // 1. Add from main lots (matières premières)
                this.selectedLots.forEach(s => {
                    const lot = this.getLot(s.lotId);
                    if (lot && !seen.has('mp-' + lot.matiere_premiere_id)) {
                        seen.add('mp-' + lot.matiere_premiere_id);
                        sources.push({ type: 'mp', id: lot.matiere_premiere_id, libelle: lot.matiere_libelle });
                    }
                });
                // 2. Add from produits semi-finis obtenus lors des phases précédentes
                for (let k = 0; k < index; k++) {
                    const prevPhase = this.phases[k];
                    if (prevPhase && prevPhase.produitSemiFiniObtenuId) {
                        const psf = this.allProduitsSemiFinis.find(p => p.id == prevPhase.produitSemiFiniObtenuId);
                        if (psf && !seen.has('psf-' + psf.id)) {
                            seen.add('psf-' + psf.id);
                            sources.push({ type: 'psf', id: psf.id, libelle: psf.designation + " (Obtenu phase " + (k+1) + ")" });
                        }
                    }
                }
                return sources;
            },

            setPhaseInputSource(index, value) {
                if (!value) {
                    this.phases[index].matierePremiereId = '';
                    this.phases[index].produitSemiFiniId = '';
                    return;
                }
                const [type, id] = value.split(':');
                if (type === 'mp') {
                    this.phases[index].matierePremiereId = id;
                    this.phases[index].produitSemiFiniId = '';
                } else {
                    this.phases[index].produitSemiFiniId = id;
                    this.phases[index].matierePremiereId = '';
                }
            },

            quickAddPsf(index) {
                this.quickMatierePhaseIndex = index;
                this.quickMatiereLibelle = '';
                this.showQuickMatiereModal = true;
            },

            async submitQuickMatiere() {
                if (!this.quickMatiereLibelle) return;
                try {
                    const response = await fetch("{{ route('produits-semi-finis.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            designation: this.quickMatiereLibelle
                        })
                    });

                    if (!response.ok) {
                        const errData = await response.json();
                        if (errData.errors) {
                            const firstErr = Object.values(errData.errors)[0][0];
                            alert(firstErr);
                        } else {
                            alert("Erreur lors de la création de l'état.");
                        }
                        return;
                    }

                    const result = await response.json();
                    if (result.success) {
                        this.allProduitsSemiFinis.push(result.produit);
                        if (this.quickMatierePhaseIndex !== null) {
                            this.phases[this.quickMatierePhaseIndex].produitSemiFiniObtenuId = result.produit.id;
                        }
                        this.showQuickMatiereModal = false;
                    } else {
                        alert("Erreur lors de la création de l'état.");
                    }
                } catch (error) {
                    console.error(error);
                    alert("Erreur lors de la communication avec le serveur.");
                }
            },

            get stockErrors() {
                return this.selectedLots.filter(s => {
                    const lot = this.getLot(s.lotId);
                    return lot && s.quantite > lot.quantite_disponible;
                });
            },

            // ── Dynamic lot/phase management ──
            addLot()      { this.selectedLots.push({ lotId: '', quantite: 0 }); },
            removeLot(i)  { if (this.selectedLots.length > 1) this.selectedLots.splice(i, 1); },
            addPhase()    {
                const baseDate = this.phases[0].date_attribution || '{{ now()->format("Y-m-d\TH:i") }}';
                this.phases.splice(this.phases.length - 1, 0, {
                    transformationId: '',
                    equipeId: '',
                    machineId: '',
                    date_attribution: baseDate,
                    matierePremiereId: '',
                    produitSemiFiniId: '',
                    quantiteMpPhase: '',
                    produitSemiFiniObtenuId: '',
                    quantiteObtenue: ''
                });
            },
            removePhase(i){
                if (i > 0 && i < this.phases.length - 1) {
                    this.phases.splice(i, 1);
                }
            },

            // ── AI Simulation Computation ──
            computeAI() {
                const activeLots = this.selectedLots.filter(s => s.lotId && s.quantite > 0);
                if (!activeLots.length) return null;

                // 1. Weighted loss from lot quality
                let totalMp = 0, weightedLotLoss = 0;
                activeLots.forEach(sel => {
                    const lot = this.getLot(sel.lotId);
                    if (lot) {
                        weightedLotLoss += lot.qualite_loss * sel.quantite;
                        totalMp += sel.quantite;
                    }
                });
                const lotLoss = totalMp > 0 ? weightedLotLoss / totalMp : 0.05;

                // 2. Machine losses and duration per phase
                let totalDuration = 0, machineLoss = 0;
                const activePhasesWithMachine = this.phases.filter(p => p.machineId);
                activePhasesWithMachine.forEach(phase => {
                    const m = this.getMachine(phase.machineId);
                    const eff = this.machineEfficiency(m ? m.etat : 'en_marche');
                    machineLoss += (1 - eff) * 0.04 + 0.02; // machine inefficiency + base phase loss
                });

                // Phases with no machine configured: base duration + 5% loss each
                const phasesNoMachine = this.phases.length - activePhasesWithMachine.length;
                machineLoss   += phasesNoMachine * 0.05;

                // Calculate duration based on attribution dates if both initial and final phase dates are filled
                let dateInit = this.phases[0] ? this.phases[0].date_attribution : null;
                let dateFinal = this.phases[this.phases.length - 1] ? this.phases[this.phases.length - 1].date_attribution : null;

                if (dateInit && dateFinal) {
                    const diffMs = new Date(dateFinal) - new Date(dateInit);
                    const diffMin = Math.round(diffMs / 60000);
                    if (diffMin > 0) {
                        totalDuration = diffMin;
                    } else {
                        // fallback
                        activePhasesWithMachine.forEach(phase => {
                            const m = this.getMachine(phase.machineId);
                            const eff = this.machineEfficiency(m ? m.etat : 'en_marche');
                            totalDuration += eff > 0 ? Math.round(BASE_PHASE_MIN / eff) : BASE_PHASE_MIN * 2;
                        });
                        totalDuration += phasesNoMachine * BASE_PHASE_MIN;
                    }
                } else {
                    // fallback
                    activePhasesWithMachine.forEach(phase => {
                        const m = this.getMachine(phase.machineId);
                        const eff = this.machineEfficiency(m ? m.etat : 'en_marche');
                        totalDuration += eff > 0 ? Math.round(BASE_PHASE_MIN / eff) : BASE_PHASE_MIN * 2;
                    });
                    totalDuration += phasesNoMachine * BASE_PHASE_MIN;
                }

                const totalLossRate = Math.min(lotLoss + machineLoss, 0.45);
                const estimatedPF   = totalMp * (1 - totalLossRate);
                const hours         = Math.floor(totalDuration / 60);
                const mins          = totalDuration % 60;

                return {
                    totalMp, lotLoss, machineLoss, totalLossRate,
                    estimatedPF, totalDurationMin: totalDuration,
                    durationLabel: hours > 0 ? `${hours}h ${mins}min` : `${mins} min`,
                };
            },

            validerDatesPhases() {
                if (this.phases.length < 2) return;
                const dInit = this.phases[0].date_attribution;
                const dFinal = this.phases[this.phases.length - 1].date_attribution;

                if (dInit && dFinal && dInit > dFinal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Chronologie Invalide',
                        text: "La date d'attribution initiale ne peut pas dépasser la date d'attribution finale.",
                        confirmButtonColor: '#10b981',
                        background: '#0f172a',
                        color: '#fff',
                        customClass: {
                            popup: 'rounded-3xl border border-slate-700 shadow-2xl',
                            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-500 hover:bg-emerald-600 border-none'
                        }
                    });
                    this.phases[this.phases.length - 1].date_attribution = dInit;
                }

                // Check intermediate phases
                for (let i = 1; i < this.phases.length - 1; i++) {
                    const currentVal = this.phases[i].date_attribution;
                    if (currentVal) {
                        if (dInit && currentVal < dInit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Date Hors Limites',
                                html: `La date de la phase intermédiaire <strong>#${i}</strong> ne peut pas être antérieure à la phase initiale.`,
                                confirmButtonColor: '#10b981',
                                background: '#0f172a',
                                color: '#fff',
                                customClass: {
                                    popup: 'rounded-3xl border border-slate-700 shadow-2xl',
                                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-500 hover:bg-emerald-600 border-none'
                                }
                            });
                            this.phases[i].date_attribution = dInit;
                        }
                        if (dFinal && currentVal > dFinal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Date Hors Limites',
                                html: `La date de la phase intermédiaire <strong>#${i}</strong> ne peut pas dépasser la date de la phase finale.`,
                                confirmButtonColor: '#10b981',
                                background: '#0f172a',
                                color: '#fff',
                                customClass: {
                                    popup: 'rounded-3xl border border-slate-700 shadow-2xl',
                                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-500 hover:bg-emerald-600 border-none'
                                }
                            });
                            this.phases[i].date_attribution = dFinal;
                        }
                    }
                }
            },

            // ── Animation ──
            ts() { return new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); },
            log(msg, type='info') {
                this.animLog.push({ msg, type, time: this.ts() });
                this.$nextTick(() => { const el=document.getElementById('simLog'); if(el) el.scrollTop=el.scrollHeight; });
            },

            async lancerSimulation() {
                if (!this.$refs.mainForm.reportValidity()) {
                    return;
                }
                this._clearTimers();
                this._simRun          = (this._simRun || 0) + 1;
                const runId           = this._simRun;
                this.showSimulation   = true;
                this.simPhase         = 'running';
                this.animStep         = 0;
                this.particleProgress = 0;
                this.animLog          = [];
                this.simResults       = null;
                this.animCurrentLabel = 'Connexion au moteur prédictif...';

                const lots = this.selectedLots.filter(s => s.lotId && s.quantite > 0);

                // Animation pendant le calcul serveur réel
                const steps = [
                    { t:300,  step:1, progress:5,  label:'Chargement de l\'historique de production...', msg:'→ Connexion au moteur prédictif serveur', type:'info' },
                    { t:1300, step:2, progress:18, label:`Analyse de ${lots.length} lot(s) : qualité, âge, péremption...`, msg:`→ ${lots.length} lot(s) MP transmis pour analyse qualité/FIFO`, type:'info' },
                    { t:2400, step:4, progress:38, label:'Apprentissage des rendements réels (pondération bayésienne)...', msg:'→ Agrégation des rendements historiques par transformation × machine (demi-vie 90 j)', type:'ai' },
                    { t:3600, step:6, progress:58, label:'Auto-calibration : prédictions passées vs productions réelles...', msg:'→ Correction du biais systématique du modèle (conditionnements validés)', type:'ai' },
                    { t:4800, step:7, progress:78, label:'Simulation Monte Carlo : 1 500 scénarios stochastiques...', msg:'→ Propagation des incertitudes sur toute la chaîne de phases', type:'ai' },
                ];

                steps.forEach(s => {
                    const id = setTimeout(() => {
                        this.animStep         = s.step;
                        this.particleProgress = s.progress;
                        this.animCurrentLabel = s.label;
                        this.log(s.msg, s.type);
                    }, s.t);
                    this._timers.push(id);
                });

                // Appel du moteur serveur en parallèle de l'animation
                const payload = {
                    lots: lots.map(s => ({ lot_id: s.lotId, quantite: parseFloat(s.quantite) })),
                    phases: this.phases.map(p => ({
                        transformation_id: p.transformationId || null,
                        machine_id:        p.machineId || null,
                    })),
                    quantite_pf_cible: this.quantitePfCible ? parseFloat(this.quantitePfCible) : null,
                };

                const fetchPromise = fetch('{{ route('api.simulation-op') }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(payload),
                }).then(r => r.ok ? r.json() : null).catch(() => null);

                const minDelay = new Promise(res => {
                    const id = setTimeout(res, 6000);
                    this._timers.push(id);
                });

                const [server] = await Promise.all([fetchPromise, minDelay]);
                if (runId !== this._simRun) return; // simulation annulée/relancée entre-temps

                if (server && !server.error) {
                    (server.trace || []).forEach(l => this.log('→ ' + l.msg, l.type || 'info'));
                    this.log(`✔ Simulation terminée — indice de confiance ${server.confiance}% (historique : ${server.nHistorique} phase(s))`, 'done');
                    this.simResults = server;
                } else {
                    this.log('⚠ Moteur serveur indisponible — repli sur l\'estimation locale simplifiée', 'warn');
                    this.simResults = this.computeAI();
                }

                this.animStep         = 9;
                this.particleProgress = 100;
                this.animCurrentLabel = 'Rapport de simulation généré.';

                const doneId = setTimeout(() => { this.simPhase = 'complete'; }, 900);
                this._timers.push(doneId);
            },

            resetSim() {
                this._clearTimers();
                this.simPhase      = 'idle';
                this.showSimulation = false;
            },

            _clearTimers() {
                this._timers.forEach(id => clearTimeout(id));
                this._timers = [];
            },

            confirmerEtCreer() {
                if (!this.stockErrors.length) {
                    if (this.$refs.mainForm.reportValidity()) {
                        this.$refs.mainForm.submit();
                    }
                }
            },
        }
    }
    </script>

</x-app-layout>
