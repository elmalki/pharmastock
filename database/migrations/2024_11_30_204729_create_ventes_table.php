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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('n_facture')->nullable();
            $table->date('date')->nullable();
            $table->date('dateEcheance')->nullable();
            $table->text('ordonnance')->nullable();
            $table->enum('paiement',['Crédit','Éspèce','TPE','Chèque','Effet','Virement','Virsement','En compte','Multi Réglement'])->nullable();
            $table->enum('situation',['En caisse','En portefeuille','En banque','Impayé','Réglé en espèce','Contre réglement','Réglé'])->nullable();
            $table->decimal('fraisMedicament', 8, 2)->unsigned()->nullable();
            $table->decimal('montantPaye', 8, 2)->unsigned()->nullable();
            $table->decimal('benefice', 8, 2)->unsigned()->nullable();
            $table->foreignIdFor(\App\Models\Client::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
