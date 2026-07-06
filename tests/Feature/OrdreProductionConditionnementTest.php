<?php

namespace Tests\Feature;

use App\Models\Departement;
use App\Models\Employe;
use App\Models\Equipe;
use App\Models\MatierePremiere;
use App\Models\OrdreProduction;
use App\Models\ProduitFini;
use App\Models\TypeConditionnement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdreProductionConditionnementTest extends TestCase
{
    use RefreshDatabase;

    public function test_conditionnement_form_shows_conditionnement_types_and_prefilled_mp_quantity(): void
    {
        $user = User::factory()->create();

        $departement = Departement::create(['nom' => 'Production']);
        $equipe = Equipe::create(['nom' => 'Conditionnement']);
        $employe = Employe::create([
            'matricule' => 'EMP-001',
            'nom' => 'Durand',
            'prenom' => 'Jean',
            'fonction' => 'Opérateur',
            'departement_id' => $departement->id,
            'equipe_id' => $equipe->id,
            'user_id' => $user->id,
        ]);
        $matierePremiere = MatierePremiere::create([
            'code' => 'MP-001',
            'libelle' => 'Farine',
            'unite_mesure' => 'Kg',
            'qte_en_stock' => 100,
        ]);
        $produitFini = ProduitFini::create([
            'code' => 'PF-001',
            'designation' => 'Produit test',
            'qte_en_stock' => 0,
            'unite_mesure' => 'Kg',
        ]);
        $typeConditionnement = TypeConditionnement::create([
            'libelle' => 'Sachet 500g',
            'description' => 'Conditionnement standard',
            'unite' => 'Sachets',
        ]);

        $ordreProduction = OrdreProduction::create([
            'code' => 'OP-2026-0001',
            'produit_fini_id' => $produitFini->id,
            'matiere_premiere_id' => $matierePremiere->id,
            'employe_id' => $employe->id,
            'quantite_mp_injectee' => 50,
            'numero_lot' => 'LOT-001',
            'date_debut' => now(),
            'statut' => 'en_cours',
        ]);

        $response = $this->actingAs($user)->get(route('ordre-productions.show', $ordreProduction));

        $response->assertOk();
        $response->assertSee('name="type_emballage"');
        $response->assertSee('Sachet 500g');
        $response->assertSee('value="50"', false);
    }
}
