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
        Schema::create('commande_pfs', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // CMD-PF-00001
            $table->foreignId('produit_fini_id')->constrained('produits_finis')->onDelete('restrict');
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->decimal('quantite_commandee', 12, 2);
            $table->date('date_commande');
            $table->enum('statut', ['en_attente', 'en_preparation', 'livree', 'annulee'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_pfs');
    }
};
