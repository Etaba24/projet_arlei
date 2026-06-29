<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Nouvelles permissions lecture seule par groupe ──
        $newPermissions = [
            ['name' => 'Voir les stocks & ressources', 'slug' => 'stocks.voir',     'groupe' => 'Stocks & Ressources', 'description' => 'Lecture seule sur les matières premières, lots, machines et produits finis.'],
            ['name' => 'Voir la logistique',           'slug' => 'logistique.voir', 'groupe' => 'Logistique',          'description' => 'Lecture seule sur les mouvements MP/PF, fournisseurs et clients.'],
            ['name' => 'Voir les RH',                  'slug' => 'rh.voir',         'groupe' => 'RH & Organisation',   'description' => 'Lecture seule sur les employés et les équipes.'],
            ['name' => 'Voir la configuration',        'slug' => 'admin.voir',      'groupe' => 'Administration',      'description' => 'Lecture seule sur les comptes, rôles et permissions.'],
        ];

        foreach ($newPermissions as $perm) {
            if (!DB::table('permissions')->where('slug', $perm['slug'])->exists()) {
                DB::table('permissions')->insert(array_merge($perm, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // ── S'assurer que l'Administrateur a toutes les permissions ──
        $adminRole = DB::table('roles')->where('slug', 'administrateur')->first();
        if ($adminRole) {
            $allPermIds = DB::table('permissions')->pluck('id');
            foreach ($allPermIds as $permId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $adminRole->id, 'permission_id' => $permId]
                );
            }
        }

        // ── Créer le rôle système "Lecteur" ──
        if (!DB::table('roles')->where('slug', 'lecteur')->exists()) {
            $lecteurId = DB::table('roles')->insertGetId([
                'name'        => 'Lecteur',
                'slug'        => 'lecteur',
                'couleur'     => '#64748b',
                'description' => 'Accès lecture seule à l\'ensemble de l\'application, sans aucune modification possible.',
                'is_system'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $viewSlugs = [
                'production.voir',
                'production.suivi',
                'stocks.voir',
                'logistique.voir',
                'rh.voir',
                'admin.voir',
            ];

            $viewPermIds = DB::table('permissions')->whereIn('slug', $viewSlugs)->pluck('id');
            foreach ($viewPermIds as $permId) {
                DB::table('role_permissions')->insert(['role_id' => $lecteurId, 'permission_id' => $permId]);
            }
        }
    }

    public function down(): void
    {
        $lecteur = DB::table('roles')->where('slug', 'lecteur')->first();
        if ($lecteur) {
            DB::table('role_permissions')->where('role_id', $lecteur->id)->delete();
            DB::table('roles')->where('id', $lecteur->id)->delete();
        }

        $slugs   = ['stocks.voir', 'logistique.voir', 'rh.voir', 'admin.voir'];
        $permIds = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
