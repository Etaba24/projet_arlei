<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Transforme produits_semi_finis d'un journal de traçabilité par phase
     * (phase_production_id, ordre_production_id, matiere_premiere_id, matiere_obtenue_id,
     * machine_id, statut) en un catalogue de stock (comme produits_finis) : chaque ligne
     * représente un "état" de transformation réutilisable, avec son propre qte_en_stock.
     */
    public function up(): void
    {
        Schema::table('produits_semi_finis', function (Blueprint $table) {
            if (Schema::hasColumn('produits_semi_finis', 'phase_production_id')) {
                $table->dropForeign(['phase_production_id']);
                $table->dropColumn('phase_production_id');
            }
            if (Schema::hasColumn('produits_semi_finis', 'ordre_production_id')) {
                $table->dropForeign(['ordre_production_id']);
                $table->dropColumn('ordre_production_id');
            }
            if (Schema::hasColumn('produits_semi_finis', 'matiere_premiere_id')) {
                $table->dropForeign(['matiere_premiere_id']);
                $table->dropColumn('matiere_premiere_id');
            }
            if (Schema::hasColumn('produits_semi_finis', 'matiere_obtenue_id')) {
                $table->dropForeign(['matiere_obtenue_id']);
                $table->dropColumn('matiere_obtenue_id');
            }
            if (Schema::hasColumn('produits_semi_finis', 'machine_id')) {
                $table->dropForeign(['machine_id']);
                $table->dropColumn('machine_id');
            }
            if (Schema::hasColumn('produits_semi_finis', 'statut')) {
                $table->dropColumn('statut');
            }
            if (Schema::hasColumn('produits_semi_finis', 'quantite')) {
                $table->dropColumn('quantite');
            }
            if (!Schema::hasColumn('produits_semi_finis', 'qte_en_stock')) {
                $table->decimal('qte_en_stock', 12, 2)->default(0)->after('designation');
            }
        });

        DB::table('permissions')->where('slug', 'stocks.semi-finis')->update([
            'name'        => 'Gérer les produits semi-finis',
            'description' => 'Créer, modifier et supprimer les états / produits semi-finis issus des phases de transformation.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produits_semi_finis', function (Blueprint $table) {
            if (!Schema::hasColumn('produits_semi_finis', 'phase_production_id')) {
                $table->foreignId('phase_production_id')->nullable()->unique()->constrained('phase_productions')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('produits_semi_finis', 'ordre_production_id')) {
                $table->foreignId('ordre_production_id')->nullable()->constrained('ordre_productions')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('produits_semi_finis', 'matiere_premiere_id')) {
                $table->foreignId('matiere_premiere_id')->nullable()->constrained('matieres_premieres');
            }
            if (!Schema::hasColumn('produits_semi_finis', 'matiere_obtenue_id')) {
                $table->foreignId('matiere_obtenue_id')->nullable()->constrained('matieres_premieres');
            }
            if (!Schema::hasColumn('produits_semi_finis', 'machine_id')) {
                $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            }
            if (!Schema::hasColumn('produits_semi_finis', 'statut')) {
                $table->string('statut')->default('en_stock');
            }
            if (Schema::hasColumn('produits_semi_finis', 'qte_en_stock')) {
                $table->dropColumn('qte_en_stock');
            }
        });

        DB::table('permissions')->where('slug', 'stocks.semi-finis')->update([
            'name'        => 'Voir les produits semi-finis',
            'description' => 'Consulter le registre des produits semi-finis issus des phases de transformation.',
        ]);
    }
};
