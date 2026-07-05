<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE machines MODIFY COLUMN etat ENUM('pret', 'en_marche', 'arret', 'en_panne', 'en_maintenance') NOT NULL DEFAULT 'pret'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE machines MODIFY COLUMN etat ENUM('en_marche', 'en_panne', 'arret') NOT NULL DEFAULT 'arret'");
    }
};
