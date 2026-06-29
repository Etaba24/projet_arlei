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
        Schema::create('livraison_mps', function (Blueprint $table) {
            $table->id();
            $table->string('numero_bordereau');
            $table->foreignId('commande_mp_id')->constrained('commande_mps')->onDelete('restrict');
            $table->foreignId('employe_id')->constrained('employes')->onDelete('restrict'); // Réceptionnaire
            $table->decimal('quantite_recue', 12, 2);
            $table->date('date_reception');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraison_mps');
    }
};
