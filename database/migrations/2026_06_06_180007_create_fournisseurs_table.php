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
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // FRN-00001
            $table->string('designation');
            $table->string('nationalite')->nullable();
            $table->string('localite')->nullable();
            $table->string('raison_sociale')->nullable(); // Producteur direct, Coopérative
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
