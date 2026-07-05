<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phase_productions', function (Blueprint $table) {
            // Quantité de MP consommée par cette phase spécifique
            if (!Schema::hasColumn('phase_productions', 'quantite_mp_phase')) {
                $table->decimal('quantite_mp_phase', 10, 3)->nullable()->after('matiere_premiere_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phase_productions', function (Blueprint $table) {
            $table->dropColumn(['quantite_mp_phase']);
        });
    }
};


