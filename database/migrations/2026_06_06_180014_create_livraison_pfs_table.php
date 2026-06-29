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
        Schema::create('livraison_pfs', function (Blueprint $table) {
            $table->id();
            $table->string('numero_bordereau')->unique(); // BL-00001
            $table->foreignId('commande_pf_id')->constrained('commande_pfs')->onDelete('restrict');
            $table->string('numero_lot'); // Issu de la production
            $table->string('grammage')->nullable(); // ex: 500g, 1kg
            $table->string('type_emballage')->nullable(); // Sachet kraft 500g, Carton de 5kg
            $table->decimal('quantite_expediee', 12, 2);
            $table->date('date_livraison');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraison_pfs');
    }
};
