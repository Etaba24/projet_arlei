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
        Schema::create('commande_mps', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // CMD-MP-00001
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->onDelete('restrict');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->onDelete('restrict');
            $table->foreignId('employe_id')->constrained('employes')->onDelete('restrict');
            $table->decimal('quantite_commandee', 12, 2);
            $table->date('date_commande');
            $table->enum('statut', ['en_attente', 'livree', 'annulee'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_mps');
    }
};
