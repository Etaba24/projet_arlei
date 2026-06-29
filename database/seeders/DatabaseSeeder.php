<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\Employe;
use App\Models\MatierePremiere;
use App\Models\ProduitFini;
use App\Models\Fournisseur;
use App\Models\Client;
use App\Models\Machine;
use App\Models\Transformation;
use App\Models\OrdreProduction;
use App\Models\PhaseProduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Création des Utilisateurs
        $adminUser = User::create([
            'name' => 'Directeur Usine',
            'email' => 'admin@production.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $operatorUser1 = User::create([
            'name' => 'Chef Équipe Lavage',
            'email' => 'lavage@production.com',
            'password' => Hash::make('password'),
            'role' => 'operateur',
        ]);

        $operatorUser2 = User::create([
            'name' => 'Chef Équipe Broyage',
            'email' => 'broyage@production.com',
            'password' => Hash::make('password'),
            'role' => 'operateur',
        ]);

        $operatorUser3 = User::create([
            'name' => 'Chef Équipe Conditionnement',
            'email' => 'cond@production.com',
            'password' => Hash::make('password'),
            'role' => 'operateur',
        ]);

        // 2. Création des Départements
        $depProduction = Departement::create([
            'designation' => 'Département Production',
            'description' => 'Supervision de la transformation industrielle',
        ]);

        $depLogistique = Departement::create([
            'designation' => 'Département Logistique',
            'description' => 'Gestion des stocks et des expéditions',
        ]);

        // 3. Création des Équipes
        $eqLavage = Equipe::create([
            'nom' => 'Équipe de Lavage',
            'description' => 'Responsable du nettoyage des matières premières',
        ]);

        $eqBroyage = Equipe::create([
            'nom' => 'Équipe de Broyage',
            'description' => 'Responsable du broyage et du concassage',
        ]);

        $eqConditionnement = Equipe::create([
            'nom' => 'Équipe de Conditionnement',
            'description' => 'Responsable de l\'emballage final',
        ]);

        // 4. Création des Employés
        Employe::create([
            'nom' => 'Ngo',
            'prenom' => 'Marie',
            'residence' => 'Douala',
            'fonction' => 'Chef de Production',
            'telephone' => '+237600000001',
            'email' => 'admin@production.com',
            'departement_id' => $depProduction->id,
            'equipe_id' => $eqLavage->id, // Équipe par défaut
            'user_id' => $adminUser->id,
        ]);

        Employe::create([
            'nom' => 'Eto\'o',
            'prenom' => 'Samuel',
            'residence' => 'Yaoundé',
            'fonction' => 'Chef Équipe Lavage',
            'telephone' => '+237600000002',
            'email' => 'lavage@production.com',
            'departement_id' => $depProduction->id,
            'equipe_id' => $eqLavage->id,
            'user_id' => $operatorUser1->id,
        ]);

        Employe::create([
            'nom' => 'Aboubakar',
            'prenom' => 'Vincent',
            'residence' => 'Garoua',
            'fonction' => 'Chef Équipe Broyage',
            'telephone' => '+237600000003',
            'email' => 'broyage@production.com',
            'departement_id' => $depProduction->id,
            'equipe_id' => $eqBroyage->id,
            'user_id' => $operatorUser2->id,
        ]);

        Employe::create([
            'nom' => 'Zambo',
            'prenom' => 'André',
            'residence' => 'Yaoundé',
            'fonction' => 'Chef Équipe Conditionnement',
            'telephone' => '+237600000004',
            'email' => 'cond@production.com',
            'departement_id' => $depProduction->id,
            'equipe_id' => $eqConditionnement->id,
            'user_id' => $operatorUser3->id,
        ]);

        // 5. Création des Matières Premières
        MatierePremiere::create([
            'libelle' => 'Manioc',
            'variete' => 'Belombo',
            'qte_en_stock' => 5000.00,
            'unite_mesure' => 'Kg',
            'seuil_securite' => 1000.00,
        ]);

        MatierePremiere::create([
            'libelle' => 'Café',
            'variete' => 'Robusta',
            'qte_en_stock' => 2500.00,
            'unite_mesure' => 'Kg',
            'seuil_securite' => 500.00,
        ]);

        MatierePremiere::create([
            'libelle' => 'Cacao',
            'variete' => 'Criollo',
            'qte_en_stock' => 3000.00,
            'unite_mesure' => 'Kg',
            'seuil_securite' => 800.00,
        ]);

        // 6. Création des Produits Finis
        ProduitFini::create([
            'designation' => 'Farine de Manioc (Sachet 1kg)',
            'qte_en_stock' => 200,
            'unite_mesure' => 'Sachets',
        ]);

        ProduitFini::create([
            'designation' => 'Café Moulu (Sachet 250g)',
            'qte_en_stock' => 150,
            'unite_mesure' => 'Sachets',
        ]);

        ProduitFini::create([
            'designation' => 'Cacao en Poudre (Sachet 500g)',
            'qte_en_stock' => 100,
            'unite_mesure' => 'Sachets',
        ]);

        // 7. Création des Fournisseurs
        Fournisseur::create([
            'designation' => 'Coopérative Agricole de l\'Est',
            'nationalite' => 'Camerounaise',
            'localite' => 'Bertoua',
            'raison_sociale' => 'Coopérative',
            'telephone' => '+237611111111',
            'email' => 'contact@coopest.cm',
        ]);

        Fournisseur::create([
            'designation' => 'Plantations du Moungo',
            'nationalite' => 'Camerounaise',
            'localite' => 'Nkongsamba',
            'raison_sociale' => 'Producteur direct',
            'telephone' => '+237622222222',
            'email' => 'moungo@plantations.cm',
        ]);

        // 8. Création des Clients
        Client::create([
            'nom' => 'Supermarché',
            'prenom' => 'Kado',
            'entreprise' => 'Kado Supermarché',
            'raison_sociale' => 'Supermarché',
            'telephone' => '+237633333333',
            'email' => 'achats@kadosuper.cm',
        ]);

        Client::create([
            'nom' => 'Grossiste du Centre',
            'prenom' => 'Jean',
            'entreprise' => 'Sodiko SARL',
            'raison_sociale' => 'Grossiste',
            'telephone' => '+237644444444',
            'email' => 'contact@sodiko.cm',
        ]);

        // 9. Création des Machines
        Machine::create([
            'designation' => 'Laveuse Industrielle L1',
            'etat' => 'en_marche',
        ]);

        Machine::create([
            'designation' => 'Broyeuse Industrielle B1',
            'etat' => 'en_marche',
        ]);

        Machine::create([
            'designation' => 'Four de Séchage S1',
            'etat' => 'en_marche',
        ]);

        Machine::create([
            'designation' => 'Conditionneuse C1',
            'etat' => 'en_marche',
        ]);

        // 10. Création des Transformations
        Transformation::create([
            'designation' => 'Lavage',
            'description' => 'Nettoyage à l\'eau claire des matières premières',
        ]);

        Transformation::create([
            'designation' => 'Épluchage',
            'description' => 'Retrait de la peau/écorce des tubercules ou fruits',
        ]);

        Transformation::create([
            'designation' => 'Broyage',
            'description' => 'Réduction en pâte ou poudre fine',
        ]);

        Transformation::create([
            'designation' => 'Séchage',
            'description' => 'Déshydratation de la matière dans un four de séchage',
        ]);

        Transformation::create([
            'designation' => 'Torréfaction',
            'description' => 'Grillage contrôlé des fèves de café ou cacao',
        ]);

        // 11. Création des Ordres de Production pour les statistiques du dashboard
        $matiereManioc = MatierePremiere::where('libelle', 'Manioc')->first();
        $produitFarine = ProduitFini::where('designation', 'Farine de Manioc (Sachet 1kg)')->first();
        $employe1 = Employe::where('email', 'admin@production.com')->first();
        $machines = Machine::all();
        $transf = Transformation::all();

        // Ordre 1 : Production complètement terminée
        $op1 = OrdreProduction::create([
            'code' => 'OP-2024-0001',
            'numero_lot' => 'LOT-2024-0001',
            'produit_fini_id' => $produitFarine->id,
            'matiere_premiere_id' => $matiereManioc->id,
            'employe_id' => $employe1->id,
            'quantite_mp_injectee' => 500.00,
            'date_debut' => Carbon::now()->subDays(5),
            'statut' => 'termine',
        ]);

        // Créer les 3 phases avec statut validé
        PhaseProduction::create([
            'ordre_production_id' => $op1->id,
            'phase' => 'initiale',
            'transformation_id' => $transf->where('designation', 'Lavage')->first()->id,
            'equipe_id' => 1,
            'machine_id' => $machines->first()->id,
            'statut' => 'valide',
            'date_debut' => Carbon::now()->subDays(5),
            'date_fin' => Carbon::now()->subDays(5)->addHours(2),
            'validated_by' => $adminUser->id,
            'validated_at' => Carbon::now()->subDays(5)->addHours(2),
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op1->id,
            'phase' => 'intermediaire',
            'transformation_id' => $transf->where('designation', 'Broyage')->first()->id,
            'equipe_id' => 2,
            'machine_id' => $machines->where('designation', 'Broyeuse Industrielle B1')->first()->id,
            'statut' => 'valide',
            'date_debut' => Carbon::now()->subDays(5)->addHours(2),
            'date_fin' => Carbon::now()->subDays(5)->addHours(4),
            'validated_by' => $adminUser->id,
            'validated_at' => Carbon::now()->subDays(5)->addHours(4),
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op1->id,
            'phase' => 'finale',
            'transformation_id' => $transf->where('designation', 'Séchage')->first()->id,
            'equipe_id' => 3,
            'machine_id' => $machines->where('designation', 'Four de Séchage S1')->first()->id,
            'statut' => 'valide',
            'date_debut' => Carbon::now()->subDays(5)->addHours(4),
            'date_fin' => Carbon::now()->subDays(5)->addHours(8),
            'validated_by' => $adminUser->id,
            'validated_at' => Carbon::now()->subDays(5)->addHours(8),
        ]);

        // Ordre 2 : Production en cours (phases 1 et 2 validées, phase 3 en cours)
        $op2 = OrdreProduction::create([
            'code' => 'OP-2024-0002',
            'numero_lot' => 'LOT-2024-0002',
            'produit_fini_id' => $produitFarine->id,
            'matiere_premiere_id' => $matiereManioc->id,
            'employe_id' => $employe1->id,
            'quantite_mp_injectee' => 600.00,
            'date_debut' => Carbon::now()->subDays(2),
            'statut' => 'en_cours',
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op2->id,
            'phase' => 'initiale',
            'transformation_id' => $transf->where('designation', 'Lavage')->first()->id,
            'equipe_id' => 1,
            'machine_id' => $machines->first()->id,
            'statut' => 'valide',
            'date_debut' => Carbon::now()->subDays(2),
            'date_fin' => Carbon::now()->subDays(2)->addHours(2),
            'validated_by' => $adminUser->id,
            'validated_at' => Carbon::now()->subDays(2)->addHours(2),
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op2->id,
            'phase' => 'intermediaire',
            'transformation_id' => $transf->where('designation', 'Broyage')->first()->id,
            'equipe_id' => 2,
            'machine_id' => $machines->where('designation', 'Broyeuse Industrielle B1')->first()->id,
            'statut' => 'valide',
            'date_debut' => Carbon::now()->subDays(2)->addHours(2),
            'date_fin' => Carbon::now()->subDays(2)->addHours(4),
            'validated_by' => $adminUser->id,
            'validated_at' => Carbon::now()->subDays(2)->addHours(4),
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op2->id,
            'phase' => 'finale',
            'transformation_id' => $transf->where('designation', 'Séchage')->first()->id,
            'equipe_id' => 3,
            'machine_id' => $machines->where('designation', 'Four de Séchage S1')->first()->id,
            'statut' => 'en_cours',
            'date_debut' => Carbon::now()->subDays(2)->addHours(4),
            'date_fin' => null,
        ]);

        // Ordre 3 : Production attendant validation (phases 1 et 2 en attente de validation)
        $op3 = OrdreProduction::create([
            'code' => 'OP-2024-0003',
            'numero_lot' => 'LOT-2024-0003',
            'produit_fini_id' => $produitFarine->id,
            'matiere_premiere_id' => $matiereManioc->id,
            'employe_id' => $employe1->id,
            'quantite_mp_injectee' => 450.00,
            'date_debut' => Carbon::now()->subHours(3),
            'statut' => 'en_cours',
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op3->id,
            'phase' => 'initiale',
            'transformation_id' => $transf->where('designation', 'Lavage')->first()->id,
            'equipe_id' => 1,
            'machine_id' => $machines->first()->id,
            'statut' => 'termine',
            'date_debut' => Carbon::now()->subHours(3),
            'date_fin' => Carbon::now()->subHours(1),
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op3->id,
            'phase' => 'intermediaire',
            'transformation_id' => $transf->where('designation', 'Broyage')->first()->id,
            'equipe_id' => 2,
            'machine_id' => $machines->where('designation', 'Broyeuse Industrielle B1')->first()->id,
            'statut' => 'en_cours',
            'date_debut' => Carbon::now()->subHours(1),
            'date_fin' => null,
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op3->id,
            'phase' => 'finale',
            'transformation_id' => $transf->where('designation', 'Séchage')->first()->id,
            'equipe_id' => 3,
            'machine_id' => $machines->where('designation', 'Four de Séchage S1')->first()->id,
            'statut' => 'en_attente',
            'date_debut' => null,
            'date_fin' => null,
        ]);

        // Ordre 4 : Production en attente (toutes phases en attente)
        $op4 = OrdreProduction::create([
            'code' => 'OP-2024-0004',
            'numero_lot' => 'LOT-2024-0004',
            'produit_fini_id' => $produitFarine->id,
            'matiere_premiere_id' => $matiereManioc->id,
            'employe_id' => $employe1->id,
            'quantite_mp_injectee' => 550.00,
            'date_debut' => Carbon::now(),
            'statut' => 'en_attente',
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op4->id,
            'phase' => 'initiale',
            'transformation_id' => $transf->where('designation', 'Lavage')->first()->id,
            'equipe_id' => 1,
            'machine_id' => $machines->first()->id,
            'statut' => 'en_attente',
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op4->id,
            'phase' => 'intermediaire',
            'transformation_id' => $transf->where('designation', 'Broyage')->first()->id,
            'equipe_id' => 2,
            'machine_id' => $machines->where('designation', 'Broyeuse Industrielle B1')->first()->id,
            'statut' => 'en_attente',
        ]);

        PhaseProduction::create([
            'ordre_production_id' => $op4->id,
            'phase' => 'finale',
            'transformation_id' => $transf->where('designation', 'Séchage')->first()->id,
            'equipe_id' => 3,
            'machine_id' => $machines->where('designation', 'Four de Séchage S1')->first()->id,
            'statut' => 'en_attente',
        ]);
    }
}
