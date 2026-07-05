<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * L'état / matière obtenue en sortie d'une phase, et l'état consommé en entrée par une
     * phase suivante, référencent désormais produits_semi_finis au lieu de matieres_premieres.
     */
    public function up(): void
    {
        if (Schema::hasColumn('phase_productions', 'matiere_premiere_obtenue_id')) {
            // Le nom de la contrainte n'a pas toujours suivi la convention Laravel par défaut
            // (ex: fk_phase_obtenue) selon l'historique des migrations sur cet environnement.
            $constraints = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'phase_productions'
                 AND COLUMN_NAME = 'matiere_premiere_obtenue_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            foreach ($constraints as $constraint) {
                Schema::table('phase_productions', function (Blueprint $table) use ($constraint) {
                    $table->dropForeign($constraint->CONSTRAINT_NAME);
                });
            }
        }

        Schema::table('phase_productions', function (Blueprint $table) {
            if (Schema::hasColumn('phase_productions', 'matiere_premiere_obtenue_id')) {
                $table->dropColumn('matiere_premiere_obtenue_id');
            }

            if (!Schema::hasColumn('phase_productions', 'produit_semi_fini_id')) {
                $table->foreignId('produit_semi_fini_id')->nullable()->after('matiere_premiere_id')
                    ->constrained('produits_semi_finis')->nullOnDelete();
            }

            if (!Schema::hasColumn('phase_productions', 'produit_semi_fini_obtenu_id')) {
                $table->foreignId('produit_semi_fini_obtenu_id')->nullable()->after('quantite_mp_phase')
                    ->constrained('produits_semi_finis')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phase_productions', function (Blueprint $table) {
            if (Schema::hasColumn('phase_productions', 'produit_semi_fini_id')) {
                $table->dropForeign(['produit_semi_fini_id']);
                $table->dropColumn('produit_semi_fini_id');
            }
            if (Schema::hasColumn('phase_productions', 'produit_semi_fini_obtenu_id')) {
                $table->dropForeign(['produit_semi_fini_obtenu_id']);
                $table->dropColumn('produit_semi_fini_obtenu_id');
            }
            if (!Schema::hasColumn('phase_productions', 'matiere_premiere_obtenue_id')) {
                $table->foreignId('matiere_premiere_obtenue_id')->nullable()->after('quantite_mp_phase')
                    ->constrained('matieres_premieres')->nullOnDelete();
            }
        });
    }
};
