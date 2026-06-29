<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots_matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->string('code_lot')->unique();
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->onDelete('cascade');
            $table->unsignedBigInteger('fournisseur_id')->nullable();
            $table->string('numero_commande')->nullable();
            $table->date('date_reception');
            $table->date('date_peremption')->nullable();
            $table->decimal('quantite_initiale', 12, 3);
            $table->decimal('quantite_disponible', 12, 3);
            $table->enum('statut', ['disponible', 'epuise', 'perime', 'quarantaine'])->default('disponible');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Migre le stock existant de chaque MP vers un lot initial
        $mps = DB::table('matieres_premieres')->get();
        foreach ($mps as $index => $mp) {
            $year = now()->year;
            $seq  = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('lots_matieres_premieres')->insert([
                'code_lot'           => "LOT-MP-{$year}-{$seq}",
                'matiere_premiere_id'=> $mp->id,
                'date_reception'     => now()->subMonths(rand(1, 6))->toDateString(),
                'quantite_initiale'  => $mp->qte_en_stock,
                'quantite_disponible'=> $mp->qte_en_stock,
                'statut'             => $mp->qte_en_stock > 0 ? 'disponible' : 'epuise',
                'notes'              => 'Lot initial — stock migré automatiquement',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lots_matieres_premieres');
    }
};
