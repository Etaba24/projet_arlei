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
        Schema::create('ordre_productions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // OP-2026-0001
            $table->foreignId('produit_fini_id')->constrained('produits_finis')->onDelete('restrict');
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->onDelete('restrict');
            $table->foreignId('employe_id')->constrained('employes')->onDelete('restrict'); // Lanceur
            $table->decimal('quantite_mp_injectee', 12, 2);
            $table->string('numero_lot')->unique(); // Lot unique pour le produit fini
            $table->string('qr_code_path')->nullable(); // Chemin vers le fichier QR
            $table->dateTime('date_debut');
            $table->enum('statut', ['en_attente', 'en_cours', 'conditionne', 'termine'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordre_productions');
    }
};
