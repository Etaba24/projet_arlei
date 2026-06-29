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
        Schema::create('phase_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordre_production_id')->constrained('ordre_productions')->onDelete('cascade');
            $table->foreignId('transformation_id')->constrained('transformations')->onDelete('restrict');
            $table->foreignId('equipe_id')->constrained('equipes')->onDelete('restrict');
            $table->foreignId('machine_id')->constrained('machines')->onDelete('restrict');
            $table->enum('numero_phase', ['initiale', 'intermediaire', 'finale']);
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'valide'])->default('en_attente');
            $table->text('observations')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();

            // Une seule phase de chaque type par ordre de production
            $table->unique(['ordre_production_id', 'numero_phase']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phase_productions');
    }
};
