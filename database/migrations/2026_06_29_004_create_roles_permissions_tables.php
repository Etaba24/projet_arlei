<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('groupe');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('couleur', 7)->default('#64748b'); // hex
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null')->after('role');
        });

        // ── Seed all permissions ──
        $permissions = [
            // Production
            ['name' => 'Créer un ordre de production',        'slug' => 'production.creer',                   'groupe' => 'Production'],
            ['name' => 'Voir les ordres de production',       'slug' => 'production.voir',                    'groupe' => 'Production'],
            ['name' => 'Interrompre un OP',                   'slug' => 'production.interrompre',             'groupe' => 'Production'],
            ['name' => 'Reprendre un OP',                     'slug' => 'production.reprendre',               'groupe' => 'Production'],
            ['name' => 'Valider une phase',                   'slug' => 'production.valider-phase',           'groupe' => 'Production'],
            ['name' => 'Enregistrer le conditionnement',      'slug' => 'production.conditionner',            'groupe' => 'Production'],
            ['name' => 'Valider le conditionnement',          'slug' => 'production.valider-conditionnement', 'groupe' => 'Production'],
            ['name' => 'Démarrer / terminer une phase',       'slug' => 'production.demarrer-phase',          'groupe' => 'Production'],
            ['name' => 'Tableau de suivi de production',      'slug' => 'production.suivi',                   'groupe' => 'Production'],
            // Stocks
            ['name' => 'Gérer les matières premières',        'slug' => 'stocks.matieres-premieres',          'groupe' => 'Stocks & Ressources'],
            ['name' => 'Gérer les produits finis',            'slug' => 'stocks.produits-finis',              'groupe' => 'Stocks & Ressources'],
            ['name' => 'Gérer les lots de MP',                'slug' => 'stocks.lots',                        'groupe' => 'Stocks & Ressources'],
            ['name' => 'Gérer les machines',                  'slug' => 'stocks.machines',                    'groupe' => 'Stocks & Ressources'],
            ['name' => 'Gérer les transformations',           'slug' => 'stocks.transformations',             'groupe' => 'Stocks & Ressources'],
            // Logistique
            ['name' => 'Logistique matières premières',       'slug' => 'logistique.mp',                      'groupe' => 'Logistique'],
            ['name' => 'Logistique produits finis',           'slug' => 'logistique.pf',                      'groupe' => 'Logistique'],
            ['name' => 'Gérer les fournisseurs',              'slug' => 'logistique.fournisseurs',            'groupe' => 'Logistique'],
            ['name' => 'Gérer les clients',                   'slug' => 'logistique.clients',                 'groupe' => 'Logistique'],
            // RH
            ['name' => 'Gérer les employés',                  'slug' => 'rh.employes',                        'groupe' => 'RH & Organisation'],
            ['name' => 'Gérer les équipes',                   'slug' => 'rh.equipes',                         'groupe' => 'RH & Organisation'],
            // Admin
            ['name' => 'Gérer les utilisateurs',              'slug' => 'admin.utilisateurs',                 'groupe' => 'Administration'],
            ['name' => 'Gérer les rôles et permissions',      'slug' => 'admin.roles',                        'groupe' => 'Administration'],
        ];

        $now = now();
        foreach ($permissions as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        DB::table('permissions')->insert($permissions);

        // ── Seed system roles ──
        $adminId = DB::table('roles')->insertGetId([
            'name'        => 'Administrateur',
            'slug'        => 'administrateur',
            'couleur'     => '#10b981',
            'description' => 'Accès complet à toutes les fonctionnalités.',
            'is_system'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $operateurId = DB::table('roles')->insertGetId([
            'name'        => 'Opérateur Terrain',
            'slug'        => 'operateur-terrain',
            'couleur'     => '#3b82f6',
            'description' => 'Démarrage et terminaison des phases de production.',
            'is_system'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // Admin gets all permissions
        $allPermIds = DB::table('permissions')->pluck('id');
        foreach ($allPermIds as $permId) {
            DB::table('role_permissions')->insert(['role_id' => $adminId, 'permission_id' => $permId]);
        }

        // Opérateur gets production view + demarrer-phase + suivi
        $operateurSlugs = ['production.voir', 'production.demarrer-phase', 'production.suivi'];
        $operateurPermIds = DB::table('permissions')->whereIn('slug', $operateurSlugs)->pluck('id');
        foreach ($operateurPermIds as $permId) {
            DB::table('role_permissions')->insert(['role_id' => $operateurId, 'permission_id' => $permId]);
        }

        // ── Assign role_id to existing users based on their role column ──
        DB::table('users')->where('role', 'admin')->update(['role_id' => $adminId]);
        DB::table('users')->where('role', 'operateur')->update(['role_id' => $operateurId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
