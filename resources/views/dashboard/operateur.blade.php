<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tableau de Bord Terrain</h1>
            @if ($equipe)
                <p class="text-sm text-slate-500 mt-1">Espace opérationnel : <strong class="text-slate-800 font-semibold">{{ $equipe->nom }}</strong></p>
            @else
                <p class="text-sm text-slate-500 mt-1">Aucune équipe assignée à votre compte.</p>
            @endif
        </div>
    </x-slot>

    <div class="space-y-8 max-w-4xl mx-auto">
        <!-- Section Scanner / Recherche rapide -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 rounded-2xl shadow-lg shadow-emerald-600/10 text-white">
            <h2 class="text-lg font-bold">Accès rapide par Fiche Production</h2>
            <p class="text-emerald-100 text-xs mt-1">Entrez le code de l'Ordre de Production (ex: OP-2026-0001) ou scannez la fiche physique.</p>
            
            <form action="{{ route('ordre-productions.scan') }}" method="POST" class="mt-4 flex gap-2">
                @csrf
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 16h.01M9 16h.01M8 12h.01M8 8h.01M16 8h.01" />
                        </svg>
                    </span>
                    <input type="text" name="code_op" required placeholder="Saisir le Code OP..." class="w-full pl-10 pr-4 py-3 bg-white text-slate-800 text-sm font-semibold rounded-xl border border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white placeholder-slate-400" />
                </div>
                <button type="submit" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 font-bold text-sm rounded-xl text-white transition-colors">
                    Ouvrir la tâche
                </button>
            </form>
        </div>

        @if ($equipe)
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rapports terrain</h3>
                    <p class="text-sm text-slate-500 mt-1">Rédigez un rapport pour l’administration et consultez vos messages envoyés.</p>
                </div>
                <a href="{{ route('rapports.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Rédiger / voir les rapports
                </a>
            </div>

            <!-- Section Tâches Actives (En Cours) -->
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                    Tâches en cours d'exécution ({{ $tachesEnCours->count() }})
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($tachesEnCours as $tache)
                        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="px-2.5 py-0.5 text-xxs font-bold uppercase tracking-wider rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/10">
                                        {{ $tache->numero_phase }}
                                    </span>
                                    <span class="text-xxs text-slate-400 font-medium">{{ $tache->ordreProduction->code }}</span>
                                </div>
                                <h4 class="text-base font-bold text-slate-800 mt-2">{{ $tache->transformation->designation }}</h4>
                                <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                                    <p>Machine : <strong>{{ $tache->machine->designation }}</strong></p>
                                    <p>Produit visé : {{ $tache->ordreProduction->produitFini->designation }}</p>
                                    <p>Lot consommé : {{ $tache->ordreProduction->numero_lot }}</p>
                                    <p>Débuté le : {{ $tache->date_debut->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-5 pt-4 border-t border-slate-100 flex gap-2">
                                <form action="{{ route('phase-productions.terminer', $tache) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Marquer comme terminé
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 py-6 text-center text-slate-400 bg-slate-50 border border-dashed border-slate-300 rounded-2xl text-sm">
                            Aucune tâche en cours pour l'instant. Démarrez-en une ci-dessous !
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Section Tâches Prêtes à Démarrer (En Attente) -->
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-300 mr-2"></span>
                    Tâches prêtes à démarrer ({{ $tachesAfaire->count() }})
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($tachesAfaire as $tache)
                        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="px-2.5 py-0.5 text-xxs font-bold uppercase tracking-wider rounded-full bg-slate-100 text-slate-700">
                                        {{ $tache->numero_phase }}
                                    </span>
                                    <span class="text-xxs text-slate-400 font-medium">{{ $tache->ordreProduction->code }}</span>
                                </div>
                                <h4 class="text-base font-bold text-slate-800 mt-2">{{ $tache->transformation->designation }}</h4>
                                <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                                    <p>Machine : <strong>{{ $tache->machine->designation }}</strong></p>
                                    <p>Produit visé : {{ $tache->ordreProduction->produitFini->designation }}</p>
                                    <p>Lot consommé : {{ $tache->ordreProduction->numero_lot }}</p>
                                    <p>Quantité MP : {{ $tache->ordreProduction->quantite_mp_injectee }} {{ $tache->ordreProduction->matierePremiere->unite_mesure }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-5 pt-4 border-t border-slate-100">
                                <form action="{{ route('phase-productions.demarrer', $tache) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Démarrer la tâche
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 py-6 text-center text-slate-400 bg-slate-50 border border-dashed border-slate-300 rounded-2xl text-sm">
                            Aucune tâche en attente pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- ─── Section Historique des tâches terminées / validées ─── --}}
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="text-lg">📋</span>
                    Historique des tâches de l'équipe ({{ $historique->count() }})
                </h3>

                @if($historique->isEmpty())
                    <div class="py-6 text-center text-slate-400 bg-slate-50 border border-dashed border-slate-300 rounded-2xl text-sm">
                        Aucune tâche terminée pour l'instant.
                    </div>
                @else
                    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold border-b border-slate-200">
                                        <th class="py-3 px-4">Ordre de Production</th>
                                        <th class="py-3 px-4">Produit Fini</th>
                                        <th class="py-3 px-4">Transformation</th>
                                        <th class="py-3 px-4">Phase</th>
                                        <th class="py-3 px-4">Machine</th>
                                        <th class="py-3 px-4">Date fin</th>
                                        <th class="py-3 px-4 text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($historique as $h)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-4 font-bold text-slate-800">
                                                <a href="{{ route('ordre-productions.show', $h->ordreProduction) }}" class="hover:text-emerald-600 transition-colors">
                                                    {{ $h->ordreProduction->code }}
                                                </a>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600">{{ $h->ordreProduction->produitFini->designation }}</td>
                                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $h->transformation->designation }}</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 capitalize">
                                                    {{ $h->numero_phase }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600">{{ $h->machine->designation }}</td>
                                            <td class="py-3 px-4 text-slate-500 text-xs">
                                                {{ $h->date_fin ? $h->date_fin->format('d/m/Y H:i') : '–' }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($h->statut === 'valide')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                                        ✓ Validée
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                                        🔔 En attente de validation
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="px-4 py-4 border-t border-slate-100">
                        {{ $historique->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-5 rounded-2xl text-center text-sm font-medium">
                Veuillez demander à l'administrateur de lier votre utilisateur à une équipe pour pouvoir visualiser et exécuter vos tâches terrain.
            </div>
        @endif
    </div>
</x-app-layout>
