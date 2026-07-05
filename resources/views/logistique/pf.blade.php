<x-app-layout>
    <div class="space-y-6" x-data="{ openCommande: false, openExpedition: false }">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center justify-between w-full gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Flux Logistiques — Produits Finis</h1>
                    <p class="text-sm text-slate-500 mt-1">Gestion des commandes clients grossistes et des expéditions de produits finis.</p>
                </div>
                <div class="flex gap-2 w-full md:w-auto mt-3 md:mt-0">
                    <a href="{{ request()->fullUrlWithQuery(['print' => 'true']) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg border border-slate-200 shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimer
                    </a>
                </div>
            </div>
        </x-slot>

        @if (session('success'))
            <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-rose-900 mb-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Des erreurs de validation empêchent l'envoi :
                </div>
                <ul class="list-disc pl-5 space-y-1 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ───── Onglets ───── --}}
        <div class="flex gap-1 p-1 bg-slate-100 rounded-2xl w-fit">
            <a href="{{ route('logistique.pf') }}?tab=commandes"
               class="px-5 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ $tab === 'commandes' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Commandes Clients
            </a>
            <a href="{{ route('logistique.pf') }}?tab=livraisons"
               class="px-5 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ $tab === 'livraisons' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Expéditions
            </a>
        </div>

        {{-- ───── Onglet : Commandes Clients ───── --}}
        @if ($tab === 'commandes')
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Historique des Commandes Clients</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Ordres d'achats émis par les clients grossistes</p>
                </div>
                <div>
                    <button @click="openCommande = true" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all group">
                        <svg class="w-4 h-4 mr-1.5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvelle Commande Client
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="px-6 py-4 bg-white border-b border-slate-100">
                <form method="GET" action="{{ route('logistique.pf') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="tab" value="commandes">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (N°, Client, Produit...)" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white text-sm focus:ring-emerald-500 focus:border-emerald-500 text-slate-900 placeholder-slate-400">
                    </div>
                    <div class="w-full sm:w-48">
                        <select name="statut" class="block w-full pl-3 pr-10 py-2 border border-slate-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 text-slate-700 bg-white" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="en_preparation" {{ request('statut') === 'en_preparation' ? 'selected' : '' }}>En préparation</option>
                            <option value="livree" {{ request('statut') === 'livree' ? 'selected' : '' }}>Expédiée</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/20 text-slate-500 uppercase text-xxs font-bold tracking-widest border-b border-slate-200">
                            <th class="py-4 px-6">Code / Date</th>
                            <th class="py-4 px-6">Client Grossiste</th>
                            <th class="py-4 px-6">Produit Fini</th>
                            <th class="py-4 px-6">Quantité Commandée</th>
                            <th class="py-4 px-6">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($commandes as $cmd)
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-mono font-bold text-slate-900">{{ $cmd->numero }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-medium">{{ \Carbon\Carbon::parse($cmd->date_commande)->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-4 px-6 text-slate-700 font-medium">
                                    {{ $cmd->client->nom ?? '' }} {{ $cmd->client->prenom ?? '' }}
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-900">
                                    {{ $cmd->produitFini->designation ?? 'N/A' }}
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-800">
                                    {{ number_format($cmd->quantite_commandee, 2) }} <span class="text-xs font-normal text-slate-400">{{ $cmd->produitFini->unite_mesure ?? 'U' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($cmd->statut === 'en_attente')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">En attente</span>
                                    @elseif ($cmd->statut === 'en_preparation')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">En préparation</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">Expédiée</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 italic">Aucune commande enregistrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($commandes->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $commandes->links() }}
                </div>
            @endif
        </div>
        @endif

        {{-- ───── Onglet : Expéditions ───── --}}
        @if ($tab === 'livraisons')
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Historique des Expéditions de Produits Finis</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Sorties effectives du stock usine</p>
                </div>
                <div>
                    <button @click="openExpedition = true" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all group">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Nouvelle Expédition
                    </button>
                </div>
            </div>

            <!-- Filtre Expéditions -->
            <div class="px-6 py-4 bg-white border-b border-slate-100">
                <form method="GET" action="{{ route('logistique.pf') }}">
                    <input type="hidden" name="tab" value="livraisons">
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search_livraison" value="{{ request('search_livraison') }}" placeholder="Rechercher (N° Lot, Commande...)" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white text-sm focus:ring-emerald-500 focus:border-emerald-500 text-slate-900 placeholder-slate-400">
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/20 text-slate-500 uppercase text-xxs font-bold tracking-widest border-b border-slate-200">
                            <th class="py-4 px-6">N° Lot / Date</th>
                            <th class="py-4 px-6">Commande Réf</th>
                            <th class="py-4 px-6">Produit Fini</th>
                            <th class="py-4 px-6">Quantité Expédiée</th>
                            <th class="py-4 px-6">Conditionnement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($livraisons as $liv)
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-mono font-bold text-emerald-700">{{ $liv->numero_lot }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-medium">{{ \Carbon\Carbon::parse($liv->date_livraison)->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-mono text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md inline-block">{{ $liv->commandePf->numero ?? 'N/A' }}</div>
                                </td>
                                <td class="py-4 px-6 text-slate-900 font-medium">
                                    {{ $liv->commandePf->produitFini->designation ?? 'N/A' }}
                                </td>
                                <td class="py-4 px-6 font-bold text-rose-600">
                                    -{{ number_format($liv->quantite_expediee, 2) }}
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                    {{ $liv->type_emballage ?? 'Standard' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 italic">Aucune expédition enregistrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($livraisons->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $livraisons->links() }}
                </div>
            @endif
        </div>
        @endif

        {{-- ───── Modal : Nouvelle Commande Client ───── --}}
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openCommande" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="openCommande = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-slate-900">Enregistrer une Commande</h3>
                        <button @click="openCommande = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form action="{{ route('logistique.pf.commande') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Produit Fini Demandé</label>
                            <select name="produit_fini_id" required class="w-full rounded-2xl border-slate-200 text-sm">
                                <option value="">Sélectionner...</option>
                                @foreach ($produits as $pf) <option value="{{ $pf->id }}">{{ $pf->designation }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Client Grossiste</label>
                            <select name="client_id" required class="w-full rounded-2xl border-slate-200 text-sm">
                                <option value="">Sélectionner...</option>
                                @foreach ($clients as $c) <option value="{{ $c->id }}">{{ $c->nom }} {{ $c->prenom }}</option> @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Quantité</label>
                                <input type="number" step="0.01" name="quantite_commandee" required class="w-full rounded-2xl border-slate-200 text-sm" value="{{ old('quantite_commandee') }}" />
                                @error('quantite_commandee')
                                    <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Date</label>
                                <input type="date" name="date_commande" required class="w-full rounded-2xl border-slate-200 text-sm" value="{{ date('Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="openCommande = false" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold text-sm">Annuler</button>
                            <button type="submit" class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold text-sm">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ───── Modal : Nouvelle Expédition ───── --}}
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-show="openExpedition" x-cloak style="display: none;">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="openExpedition = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-slate-900">Enregistrer une Expédition</h3>
                        <button @click="openExpedition = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form action="{{ route('logistique.pf.livraison') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Commande Client Associée</label>
                            <select name="commande_pf_id" required class="w-full rounded-2xl border-slate-200 text-sm">
                                <option value="">Sélectionner...</option>
                                @foreach ($commandesPendantes as $cmd)
                                    <option value="{{ $cmd->id }}">
                                        {{ $cmd->numero }} - {{ $cmd->produitFini->designation }}
                                        (Reste: {{ $cmd->quantite_commandee - $cmd->livraisonPfs->sum('quantite_expediee') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Grammage</label>
                                <input type="text" name="grammage" class="w-full rounded-2xl border-slate-200 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Type Emballage</label>
                                <input type="text" name="type_emballage" class="w-full rounded-2xl border-slate-200 text-sm" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Qte Expédiée</label>
                                <input type="number" step="0.01" name="quantite_expediee" required class="w-full rounded-2xl border-slate-200 text-sm" value="{{ old('quantite_expediee') }}" />
                                @error('quantite_expediee')
                                    <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Date Expédition</label>
                                <input type="date" name="date_livraison" required class="w-full rounded-2xl border-slate-200 text-sm" value="{{ date('Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="openExpedition = false" class="flex-1 px-6 py-3 border border-slate-200 rounded-2xl text-slate-600 font-bold text-sm">Annuler</button>
                            <button type="submit" class="flex-1 px-6 py-3 bg-slate-900 text-white rounded-2xl font-bold text-sm">Valider l'expédition</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
