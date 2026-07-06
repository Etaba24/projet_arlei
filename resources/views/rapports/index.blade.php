<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rapports</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if(Auth::user()->hasAdminInterface())
                    Consulter et lire les rapports soumis par les opérateurs.
                @else
                    Rédiger et transmettre un rapport à l’administration.
                @endif
            </p>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ session('status') }}
            </div>
        @endif

        @if(!Auth::user()->hasAdminInterface())
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900">Nouveau rapport</h2>
                <p class="text-sm text-slate-500 mt-1">Décrivez la situation, les actions réalisées et les points à suivre.</p>

                <form action="{{ route('rapports.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Titre</label>
                        <input type="text" name="titre" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex. Incident machine, besoin d’approvisionnement..." />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Contenu du rapport</label>
                        <textarea name="contenu" rows="6" required class="w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Décrivez votre observation..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                            Soumettre au responsable
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        @if(Auth::user()->hasAdminInterface())
                            Rapports reçus
                        @else
                            Mes rapports soumis
                        @endif
                    </h2>
                    <p class="text-sm text-slate-500">Liste paginée des rapports.</p>
                </div>
            </div>

            @if($rapports->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">Aucun rapport pour le moment.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3">Titre</th>
                                @if(Auth::user()->hasAdminInterface())
                                    <th class="px-4 py-3">Émetteur</th>
                                @endif
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rapports as $rapport)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $rapport->titre }}</td>
                                    @if(Auth::user()->hasAdminInterface())
                                        <td class="px-4 py-3 text-slate-600">{{ $rapport->user->name }}</td>
                                    @endif
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $rapport->statut === 'lu' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $rapport->statut === 'lu' ? 'Lu' : 'Nouveau' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('rapports.show', $rapport) }}" class="text-emerald-600 font-semibold hover:underline">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-4 border-t border-slate-100">
                    {{ $rapports->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
