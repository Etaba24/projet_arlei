<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure there is an index on ordre_production_id to prevent foreign key errors
        // since the unique index was dropped in a previous update.
        try {
            Schema::table('phase_productions', function (Blueprint $table) {
                $table->index('ordre_production_id');
            });
        } catch (\Exception $e) {
            // Already exists or failed silently
        }

        Schema::table('phase_productions', function (Blueprint $table) {
            if (!Schema::hasColumn('phase_productions', 'matiere_premiere_obtenue_id')) {
                $table->foreignId('matiere_premiere_obtenue_id')->nullable()->after('quantite_mp_phase')->constrained('matieres_premieres')->onDelete('set null');
            } else {
                try {
                    $table->foreign('matiere_premiere_obtenue_id')->references('id')->on('matieres_premieres')->onDelete('set null');
                } catch (\Exception $e) {
                    // Already exists
                }
            }

            if (!Schema::hasColumn('phase_productions', 'quantite_obtenue')) {
                $table->decimal('quantite_obtenue', 10, 3)->nullable()->after('matiere_premiere_obtenue_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phase_productions', function (Blueprint $table) {
            try {
                $table->dropForeign(['matiere_premiere_obtenue_id']);
            } catch (\Exception $e) {}
            
            $columns = [];
            if (Schema::hasColumn('phase_productions', 'matiere_premiere_obtenue_id')) {
                $columns[] = 'matiere_premiere_obtenue_id';
            }
            if (Schema::hasColumn('phase_productions', 'quantite_obtenue')) {
                $columns[] = 'quantite_obtenue';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
