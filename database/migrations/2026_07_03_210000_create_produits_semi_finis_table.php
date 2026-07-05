<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits_semi_finis', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('code')->unique();
            $table->string('designation');

            // La phase d'origine : unique => une phase validée ne génère qu'un seul semi-fini
            $table->foreignId('phase_production_id')->unique()->constrained('phase_productions')->cascadeOnDelete();
            $table->foreignId('ordre_production_id')->constrained('ordre_productions')->cascadeOnDelete();

            // Traçabilité : MP source, MP obtenue, machine utilisée
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres');
            $table->foreignId('matiere_obtenue_id')->nullable()->constrained('matieres_premieres');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();

            $table->decimal('quantite', 12, 2);
            $table->string('unite_mesure')->nullable();
            $table->string('statut')->default('en_stock'); // en_stock | consomme

            $table->timestamps();
        });

        // ── Permission dédiée, rattachée au rôle Administrateur ──
        $now = now();
        $permId = DB::table('permissions')->insertGetId([
            'name'        => 'Voir les produits semi-finis',
            'slug'        => 'stocks.semi-finis',
            'groupe'      => 'Stocks & Ressources',
            'description' => 'Consulter le registre des produits semi-finis issus des phases de transformation.',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $adminId = DB::table('roles')->where('slug', 'administrateur')->value('id');
        if ($adminId) {
            DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission_id' => $permId]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produits_semi_finis');
        DB::table('permissions')->where('slug', 'stocks.semi-finis')->delete();
    }
};
