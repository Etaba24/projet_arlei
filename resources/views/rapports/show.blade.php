<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Détail du rapport</h1>
            <p class="text-sm text-slate-500 mt-1">Consultez le contenu du rapport soumis.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Titre</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $rapport->titre }}</h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $rapport->statut === 'lu' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $rapport->statut === 'lu' ? 'Lu' : 'Nouveau' }}
                </span>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                    <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Émetteur</p>
                    <p class="font-bold text-slate-900 mt-1">{{ $rapport->user->name }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200">
                    <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Soumis le</p>
                    <p class="font-bold text-slate-900 mt-1">{{ $rapport->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold">Contenu</p>
                <p class="mt-3 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $rapport->contenu }}</p>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('rapports.index') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl">
                    Retour aux rapports
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
