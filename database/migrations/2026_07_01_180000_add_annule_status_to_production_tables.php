<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE phase_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','termine','valide','interrompu','annule') NOT NULL DEFAULT 'en_attente'");
        DB::statement("ALTER TABLE ordre_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','conditionne','termine','interrompu','annule') NOT NULL DEFAULT 'en_cours'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE phase_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','termine','valide','interrompu') NOT NULL DEFAULT 'en_attente'");
        DB::statement("ALTER TABLE ordre_productions MODIFY COLUMN statut ENUM('en_attente','en_cours','conditionne','termine','interrompu') NOT NULL DEFAULT 'en_cours'");
    }
};
