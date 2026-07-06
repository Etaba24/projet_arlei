<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_conditionnement', function (Blueprint $table) {
            $table->decimal('quantite_par_unite', 12, 2)->nullable()->after('unite');
        });
    }

    public function down(): void
    {
        Schema::table('types_conditionnement', function (Blueprint $table) {
            $table->dropColumn('quantite_par_unite');
        });
    }
};
