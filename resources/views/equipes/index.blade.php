<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center w-full gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Départements & Équipes</h1>
                <p class="text-sm text-slate-500 mt-1">Organigramme opérationnel et structures d'équipes de transformation.</p>
            </div>
            <form method="GET" action="{{ route('equipes.index') }}" class="relative w-full md:w-80 ml-0 md:ml-8">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher dans le tableau..."
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </form>
        </div>
    </x-slot>


    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Colonne Départements -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900">Nouveau Département</h2>
                @if($errors->has('designation'))
                    <div class="mt-3 bg-rose-50 border border-rose-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-rose-700">
                        {{ $errors->first('designation') }}
                    </div>
                @endif
                <form action="{{ route('equipes.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="departement" />
                    <div>
                        <label for="designation" class="block text-sm font-semibold text-slate-700">Désignation du Département <span class="text-rose-500">*</span></label>
                        <input type="text" name="designation" id="designation" required placeholder="ex: Département Production" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
                        <input type="text" name="description" placeholder="ex: Service de transformation agro-industrielle" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                        Créer le Département
                    </button>
                </form>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-base">Liste des Départements</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase text-xxs font-semibold border-b border-slate-200">
                                <th class="py-3 px-6">Code</th>
                                <th class="py-3 px-6">Désignation</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($departements as $dep)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-6 font-semibold text-slate-900">{{ $dep->code }}</td>
                                    <td class="py-4 px-6 text-slate-700">
                                        <div class="font-semibold">{{ $dep->designation }}</div>
                                        <div class="text-xs text-slate-400">{{ $dep->description }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <form action="{{ route('equipes.destroy', $dep->id) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this);">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="departement" />
                                            <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg text-rose-500 hover:text-rose-700 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">Aucun département configuré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Colonne Équipes -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900">Nouvelle Équipe</h2>
                @if($errors->has('nom'))
                    <div class="mt-3 bg-rose-50 border border-rose-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-rose-700">
                        {{ $errors->first('nom') }}
                    </div>
                @endif
                <form action="{{ route('equipes.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="equipe" />
                    <div>
                        <label for="nom" class="block text-sm font-semibold text-slate-700">Nom de l'Équipe <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" id="nom" required placeholder="ex: Équipe de Broyage" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    </div>
                    <div>
                        <label for="description_eq" class="block text-sm font-semibold text-slate-700">Description</label>
                        <input type="text" name="description" id="description_eq" placeholder="ex: Service de broyage café" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                        Créer l'Équipe
                    </button>
                </form>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-base">Liste des Équipes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase text-xxs font-semibold border-b border-slate-200">
                                <th class="py-3 px-6">Code</th>
                                <th class="py-3 px-6">Désignation</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($equipes as $eq)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-6 font-semibold text-slate-900">{{ $eq->code }}</td>
                                    <td class="py-4 px-6 text-slate-700">
                                        <div class="font-semibold">{{ $eq->nom }}</div>
                                        <div class="text-xs text-slate-400">{{ $eq->description }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <form action="{{ route('equipes.destroy', $eq->id) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this);">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="equipe" />
                                            <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg text-rose-500 hover:text-rose-700 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">Aucune équipe configurée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($equipes->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $equipes->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
