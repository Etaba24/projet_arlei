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
        Schema::table('phase_productions', function (Blueprint $table) {
            $table->dateTime('date_attribution')->nullable()->after('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phase_productions', function (Blueprint $table) {
            $table->dropColumn('date_attribution');
        });
    }
};
