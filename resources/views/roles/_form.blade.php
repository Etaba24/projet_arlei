@php
    $prefix       = ($isEdit ?? false) ? 'edit' : 'create';
    $rolePermIds  = $rolePerms ? $rolePerms->pluck('id')->toArray() : [];
    $alpineFunc   = 'roleForm_' . $prefix;
    $totalPerms   = $permissions->flatten()->count();
@endphp

<div class="overflow-y-auto max-h-[78vh]"
     x-data="{{ $alpineFunc }}({{ json_encode($rolePermIds) }})"
     x-init="syncAll()">

    <div class="p-7 space-y-6">

        {{-- ── Infos de base ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">
                    Nom du rôle <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" required
                       placeholder="Ex : Chef de Production, Responsable Qualité…"
                       value="{{ old('name', $role?->name) }}"
                       class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Couleur</label>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl border border-slate-200 shrink-0 transition-colors"
                         :style="'background-color:' + couleur"></div>
                    <input type="color" name="couleur" x-model="couleur"
                           value="{{ old('couleur', $role?->couleur ?? '#6366f1') }}"
                           class="h-9 flex-1 rounded-xl border-slate-200 cursor-pointer">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Description</label>
                <input type="text" name="description"
                       placeholder="Description courte (optionnel)"
                       value="{{ old('description', $role?->description) }}"
                       class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            </div>
        </div>

        {{-- ── Permissions ── --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Permissions</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        <span x-text="totalChecked"></span> / {{ $totalPerms }} sélectionnée(s)
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="checkAll(true)"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-800 px-3 py-1.5 rounded-lg hover:bg-emerald-50 transition-colors">
                        Tout cocher
                    </button>
                    <button type="button" @click="checkAll(false)"
                            class="text-xs font-bold text-slate-500 hover:text-slate-700 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                        Tout décocher
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($permissions as $groupe => $perms)
                @php
                    $groupSlug = \Illuminate\Support\Str::slug($groupe);
                    $gc = match($groupe) {
                        'Production'          => ['bg'=>'bg-emerald-50','border'=>'border-emerald-200','title'=>'text-emerald-800','badge'=>'bg-emerald-600','check'=>'text-emerald-600'],
                        'Stocks & Ressources' => ['bg'=>'bg-blue-50',   'border'=>'border-blue-200',   'title'=>'text-blue-800',   'badge'=>'bg-blue-600',   'check'=>'text-blue-600'],
                        'Logistique'          => ['bg'=>'bg-amber-50',  'border'=>'border-amber-200',  'title'=>'text-amber-800',  'badge'=>'bg-amber-600',  'check'=>'text-amber-600'],
                        'RH & Organisation'   => ['bg'=>'bg-violet-50', 'border'=>'border-violet-200', 'title'=>'text-violet-800', 'badge'=>'bg-violet-600', 'check'=>'text-violet-600'],
                        'Administration'      => ['bg'=>'bg-rose-50',   'border'=>'border-rose-200',   'title'=>'text-rose-800',   'badge'=>'bg-rose-600',   'check'=>'text-rose-600'],
                        default               => ['bg'=>'bg-slate-50',  'border'=>'border-slate-200',  'title'=>'text-slate-800',  'badge'=>'bg-slate-600',  'check'=>'text-slate-600'],
                    };
                    $groupPermIds = $perms->pluck('id')->toArray();
                @endphp
                <div class="{{ $gc['bg'] }} border {{ $gc['border'] }} rounded-2xl overflow-hidden">
                    {{-- Group header --}}
                    <div class="px-4 py-3 flex items-center justify-between border-b {{ $gc['border'] }}">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox"
                                       class="w-4 h-4 rounded border-slate-300 focus:ring-offset-0 {{ $gc['check'] }} focus:ring-current"
                                       :checked="groupAllChecked('{{ $groupSlug }}')"
                                       :indeterminate="groupIndeterminate('{{ $groupSlug }}')"
                                       @change="toggleGroup('{{ $groupSlug }}', $event.target.checked)">
                                <span class="text-xs font-black {{ $gc['title'] }} uppercase tracking-widest">{{ $groupe }}</span>
                            </label>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black text-white {{ $gc['badge'] }}"
                                  x-text="groupChecked['{{ $groupSlug }}'] + '/{{ $perms->count() }}'">
                                {{ count(array_intersect($rolePermIds, $groupPermIds)) }}/{{ $perms->count() }}
                            </span>
                        </div>
                        <button type="button"
                                @click="$refs.grp_{{ $prefix }}_{{ $groupSlug }}.classList.toggle('hidden'); $event.target.closest('button').querySelector('svg').classList.toggle('rotate-180')"
                                class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Checkboxes --}}
                    <div x-ref="grp_{{ $prefix }}_{{ $groupSlug }}"
                         class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all group
                                      hover:bg-white hover:shadow-sm border border-transparent hover:border-white select-none">
                            <input type="checkbox"
                                   name="permissions[]"
                                   value="{{ $perm->id }}"
                                   data-group="{{ $groupSlug }}"
                                   @change="onPermChange('{{ $groupSlug }}', {{ $perms->count() }})"
                                   @if(in_array($perm->id, $rolePermIds)) checked @endif
                                   class="w-4 h-4 rounded border-slate-300 {{ $gc['check'] }} focus:ring-current focus:ring-offset-0 shrink-0">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $perm->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $perm->slug }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Footer fixe --}}
    <div class="px-7 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between sticky bottom-0">
        <p class="text-xs text-slate-400">
            <span x-text="totalChecked" class="font-bold text-slate-600"></span> / {{ $totalPerms }} permissions
        </p>
        <div class="flex gap-3">
            <button type="button"
                    onclick="document.getElementById('modal-{{ $prefix }}-role').classList.add('hidden')"
                    class="px-5 py-2.5 text-slate-600 text-sm font-semibold border border-slate-200 rounded-xl hover:bg-white transition-colors">
                Annuler
            </button>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-7 py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                {{ ($isEdit ?? false) ? 'Enregistrer' : 'Créer le rôle' }}
            </button>
        </div>
    </div>
</div>

<script>
function {{ $alpineFunc }}(initialIds) {
    // Build initial groupChecked map
    const buildGroupMap = () => {
        const map = {};
        document.querySelectorAll('input[data-group]').forEach(cb => {
            const g = cb.dataset.group;
            if (!(g in map)) map[g] = 0;
            if (cb.checked) map[g]++;
        });
        return map;
    };

    return {
        couleur: '{{ old('couleur', $role?->couleur ?? '#6366f1') }}',
        groupChecked: {},

        get totalChecked() {
            return Object.values(this.groupChecked).reduce((a, b) => a + b, 0);
        },

        syncAll() {
            this.groupChecked = buildGroupMap();
        },

        groupAllChecked(slug) {
            const cbs = document.querySelectorAll(`input[data-group="${slug}"]`);
            return cbs.length > 0 && [...cbs].every(cb => cb.checked);
        },

        groupIndeterminate(slug) {
            const cbs = [...document.querySelectorAll(`input[data-group="${slug}"]`)];
            const n = cbs.filter(cb => cb.checked).length;
            return n > 0 && n < cbs.length;
        },

        toggleGroup(slug, state) {
            document.querySelectorAll(`input[data-group="${slug}"]`).forEach(cb => cb.checked = state);
            const count = state ? document.querySelectorAll(`input[data-group="${slug}"]`).length : 0;
            this.groupChecked[slug] = count;
        },

        onPermChange(slug, total) {
            const checked = document.querySelectorAll(`input[data-group="${slug}"]:checked`).length;
            this.groupChecked[slug] = checked;
        },

        checkAll(state) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = state);
            this.syncAll();
        },

        // Called from openEditModal to reset checkboxes without page reload
        resetToIds(ids) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = ids.includes(parseInt(cb.value));
            });
            this.syncAll();
        },
    };
}
</script>
