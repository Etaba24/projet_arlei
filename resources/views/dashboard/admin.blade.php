<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tableau de Bord</h2>
            </div>
            
            <div class="relative w-full md:w-80 ml-0 md:ml-8">
                <input type="text" id="searchInput" placeholder="Rechercher dans le tableau..." 
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
            </div>

            <div class="ml-auto flex items-center gap-4">
                <!-- Notifications Stock Alert -->
                <div x-data="{ 
                    isOpen: false, 
                    hasUnread: localStorage.getItem('read_alerts_count') !== '{{ $alertesStockMP->count() }}' && {{ $alertesStockMP->count() }} > 0
                }" class="relative">
                    <button @click="isOpen = !isOpen; if(isOpen) { hasUnread = false; localStorage.setItem('read_alerts_count', '{{ $alertesStockMP->count() }}'); }" 
                            class="relative p-2 text-slate-500 hover:text-slate-700 bg-white hover:bg-slate-50 rounded-xl border border-slate-200 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 flex items-center justify-center"
                            title="Alertes de stock"
                            aria-label="Alertes de stock">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Green Dot -->
                        <span x-show="hasUnread" 
                              class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white animate-pulse"
                              style="display: none;"></span>
                    </button>

                    <!-- Popup Panel -->
                    <div x-show="isOpen" 
                         @click.outside="isOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-slate-100 shadow-xl z-50 overflow-hidden"
                         style="display: none;">
                         
                        <!-- Popup Header -->
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-sm">Alertes de Stock</h4>
                            <span class="px-2.5 py-0.5 bg-rose-50 text-rose-600 rounded-full text-[10px] font-bold">
                                {{ $alertesStockMP->count() }} alerte(s)
                            </span>
                        </div>

                        <!-- Popup List -->
                        <div class="max-h-[300px] overflow-y-auto divide-y divide-slate-50">
                            @forelse($alertesStockMP as $mp)
                                <div class="p-4 hover:bg-slate-50 transition-colors flex items-start space-x-3">
                                    <div class="p-2 bg-rose-50 rounded-lg text-rose-500 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline">
                                            <p class="text-xs font-bold text-slate-400 truncate uppercase">{{ $mp->code }}</p>
                                            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Alerte</span>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800 truncate mt-0.5">{{ $mp->libelle }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-xs text-slate-600">Stock: <strong class="text-rose-600">{{ number_format($mp->qte_en_stock, 1) }}</strong> {{ $mp->unite_mesure }}</span>
                                            <span class="text-[10px] text-slate-300">|</span>
                                            <span class="text-xs text-slate-500">Seuil: <strong>{{ number_format($mp->seuil_securite, 1) }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 px-4 text-center">
                                    <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto text-emerald-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Tout est en règle !</p>
                                    <p class="text-xs text-slate-500 mt-1">Aucun stock de matière première n'est sous le seuil.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <!-- Popup Footer -->
                        @if($alertesStockMP->count() > 0)
                            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 text-center">
                                <a href="{{ route('matieres-premieres.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                                    Gérer les matières premières
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Button 'Lancer OP' -->
                <a href="{{ route('ordre-productions.create') }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-lg whitespace-nowrap">
                    + Lancer OP
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-8">
        {{-- Section 1 : KPI 360° --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @php 
                $stats = [
                    ['Prod', $totalOP ?? 0, 'bg-emerald-100 text-emerald-700'],
                    ['MP', $totalMatieresPremieres ?? 0, 'bg-blue-100 text-blue-700'],
                    ['PF', $totalProduitsFinis ?? 0, 'bg-amber-100 text-amber-700'],
                    ['Staff', $totalEmployes ?? 0, 'bg-violet-100 text-violet-700'],
                    ['Machines', $machinesCount ?? 0, 'bg-rose-100 text-rose-700'],
                    ['Transfo', $totalTransformations ?? 0, 'bg-slate-100 text-slate-700']
                ];
            @endphp
            @foreach($stats as $kpi)
            <div class="{{ $kpi[2] }} p-4 rounded-2xl flex flex-col items-center">
                <span class="text-[10px] uppercase font-bold opacity-70">{{ $kpi[0] }}</span>
                <span class="text-xl font-black">{{ $kpi[1] }}</span>
            </div>
            @endforeach
        </div>

        {{-- Section 2 : État Production & Machines --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6">État de la Production</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-slate-50 rounded-2xl"><p class="text-[10px] text-slate-400 uppercase font-bold">En cours</p><p class="text-2xl font-black">{{ $opEnCours ?? 0 }}</p></div>
                    <div class="p-4 bg-slate-50 rounded-2xl"><p class="text-[10px] text-slate-400 uppercase font-bold">Terminés</p><p class="text-2xl font-black">{{ $opTermines ?? 0 }}</p></div>
                    <div class="p-4 bg-slate-50 rounded-2xl"><p class="text-[10px] text-slate-400 uppercase font-bold">Conditionnés</p><p class="text-2xl font-black">{{ $opConditionnes ?? 0 }}</p></div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6">Maintenance</h3>
                <div class="space-y-4">
                    <div><div class="flex justify-between text-xs mb-1"><span>En marche</span><span>{{ $machinesEnMarche ?? 0 }}</span></div><div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500" style="width: {{ ($machinesCount ?? 1) > 0 ? (($machinesEnMarche ?? 0)/($machinesCount ?? 1))*100 : 0 }}%"></div></div></div>
                    <div><div class="flex justify-between text-xs mb-1"><span>En panne</span><span>{{ $machinesEnPanne ?? 0 }}</span></div><div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-rose-500" style="width: {{ ($machinesCount ?? 1) > 0 ? (($machinesEnPanne ?? 0)/($machinesCount ?? 1))*100 : 0 }}%"></div></div></div>
                </div>
            </div>
        </div>

        {{-- Section 3 : Tableau des OP --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800 text-lg">Derniers Ordres de Production</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold">
                    <tr>
                        <th class="py-4 px-8">Code OP</th>
                        <th class="py-4 px-6">Produit Fini</th>
                        <th class="py-4 px-6">Statut</th>
                        <th class="py-4 px-6 text-right">Détails</th>
                    </tr>
                </thead>
                <tbody id="opTable" class="divide-y divide-slate-50">
                    @forelse(($derniersOP ?? []) as $op)
                    <tr class="text-sm">
                        <td class="py-4 px-8 font-bold">{{ $op->code }}</td>
                        <td class="py-4 px-6 text-slate-600">{{ $op->produitFini?->designation ?? 'N/A' }}</td>
                        <td class="py-4 px-6"><span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold">{{ str_replace('_', ' ', $op->statut) }}</span></td>
                        <td class="py-4 px-6 text-right"><a href="{{ route('ordre-productions.show', $op) }}" class="text-emerald-600 font-bold hover:underline">Accéder</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-8 text-center text-slate-400">Aucun ordre en cours.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script de recherche simple --}}
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#opTable tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    </script>
</x-app-layout>
