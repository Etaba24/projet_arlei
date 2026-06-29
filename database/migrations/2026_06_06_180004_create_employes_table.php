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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique(); // EMP-00001
            $table->string('nom');
            $table->string('prenom');
            $table->string('residence')->nullable();
            $table->string('fonction');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('departement_id')->constrained('departements')->onDelete('restrict');
            $table->foreignId('equipe_id')->constrained('equipes')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
