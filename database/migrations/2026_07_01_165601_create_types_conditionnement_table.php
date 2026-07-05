<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_conditionnement', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // TC-00001
            $table->string('libelle');                  // Sachet, Boîte, Bidon, Sac...
            $table->string('description')->nullable();  // Description optionnelle
            $table->string('unite')->nullable();        // ex: g, mL, kg
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_conditionnement');
    }
};
