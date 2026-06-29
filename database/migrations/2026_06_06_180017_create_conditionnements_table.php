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
        Schema::create('conditionnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordre_production_id')->unique()->constrained('ordre_productions')->onDelete('cascade');
            $table->foreignId('equipe_id')->constrained('equipes')->onDelete('restrict');
            $table->string('type_emballage'); // Sachet kraft 500g, Carton de 5kg
            $table->decimal('quantite_produite', 12, 2); // Quantité finale obtenue
            $table->decimal('quantite_mp_consommee', 12, 2); // Quantité MP réellement consommée
            $table->decimal('perte', 12, 2)->default(0); // Calculé : injectée - consommée ou consommée - produite
            $table->date('date_fabrication');
            $table->date('date_peremption');
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'valide'])->default('en_attente');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditionnements');
    }
};
