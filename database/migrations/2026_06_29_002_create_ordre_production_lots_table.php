<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordre_production_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordre_production_id')
                  ->constrained('ordre_productions')
                  ->onDelete('cascade');
            $table->foreignId('lot_matiere_id')
                  ->constrained('lots_matieres_premieres')
                  ->onDelete('restrict');
            $table->decimal('quantite_utilisee', 12, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordre_production_lots');
    }
};
