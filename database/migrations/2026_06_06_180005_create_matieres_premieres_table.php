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
        Schema::create('matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MP-00001
            $table->string('libelle');
            $table->string('variete')->nullable(); // Belombo, Adianga, etc.
            $table->decimal('qte_en_stock', 12, 2)->default(0);
            $table->enum('unite_mesure', ['Kg', 'Litres', 'Tonnes']);
            $table->decimal('seuil_securite', 12, 2)->default(0); // Alerte si stock < seuil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres_premieres');
    }
};
