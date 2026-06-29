<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        /* ─────────── PHASE_PRODUCTIONS ─────────── */

        // Drop unique constraint (no longer unique: multiple intermediaire phases)
        Schema::table('phase_productions', function (Blueprint $table) {
            $table->dropUnique(['ordre_production_id', 'numero_phase']);
        });

        // Change numero_phase from ENUM to VARCHAR (allows any string)
        DB::statement("ALTER TABLE phase_productions MODIFY COLUMN numero_phase VARCHAR(50) NOT NULL");

        // Add 'interrompu' to statut enum
        DB::statement("ALTER TABLE phase_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','termine','valide','interrompu') NOT NULL DEFAULT 'en_attente'");

        // Add new columns
        Schema::table('phase_productions', function (Blueprint $table) {
            $table->unsignedSmallInteger('ordre')->default(1)->after('numero_phase');
            $table->unsignedSmallInteger('duree_estimee_min')->nullable()->after('machine_id');
        });

        // Set ordre values from numero_phase
        DB::statement("UPDATE phase_productions SET ordre =
            CASE numero_phase
                WHEN 'initiale'      THEN 1
                WHEN 'intermediaire' THEN 2
                WHEN 'finale'        THEN 3
                ELSE 2
            END");

        /* ─────────── ORDRE_PRODUCTIONS ─────────── */

        // Add simulation & interruption columns
        Schema::table('ordre_productions', function (Blueprint $table) {
            $table->decimal('quantite_pf_cible',   12, 3)->nullable()->after('quantite_mp_injectee');
            $table->decimal('quantite_pf_estimee', 12, 3)->nullable()->after('quantite_pf_cible');
            $table->decimal('taux_perte_estime',    5, 2)->nullable()->after('quantite_pf_estimee');
            $table->unsignedInteger('duree_estimee_min')->nullable()->after('taux_perte_estime');
            $table->timestamp('date_interruption')->nullable()->after('date_debut');
            $table->text('motif_interruption')->nullable()->after('date_interruption');
        });

        // Make matiere_premiere_id nullable (multiple MPs via pivot now)
        DB::statement("ALTER TABLE ordre_productions MODIFY COLUMN matiere_premiere_id BIGINT UNSIGNED NULL");

        // Add 'interrompu' to statut enum
        DB::statement("ALTER TABLE ordre_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','conditionne','termine','interrompu') NOT NULL DEFAULT 'en_cours'");

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(): void
    {
        // Intentionally not reversible to avoid data loss
    }
};
