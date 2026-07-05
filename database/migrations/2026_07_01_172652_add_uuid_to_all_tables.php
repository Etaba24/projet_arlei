<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected $tables = [
        'clients', 'fournisseurs', 'employes', 'equipes', 'machines',
        'matieres_premieres', 'produits_finis', 'lots_matieres_premieres',
        'ordre_productions', 'phase_productions', 'conditionnements',
        'transformations', 'types_conditionnement', 'users', 'roles', 'departements'
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->uuid('uuid')->nullable()->after('id')->index();
                });

                // Generate UUID for existing records
                $records = DB::table($table)->whereNull('uuid')->get(['id']);
                foreach ($records as $record) {
                    DB::table($table)->where('id', $record->id)->update(['uuid' => (string) Str::uuid()]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('uuid');
                });
            }
        }
    }
};
