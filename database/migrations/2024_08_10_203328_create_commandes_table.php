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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('n_bon')->nullable();
            $table->string('n_facture')->nullable();
            $table->date('date')->nullable();
            $table->date('dateEcheance')->nullable();
            $table->decimal('montantPaye')->unsigned()->default(0);
            $table->enum('paiement',['Éspèce','Chèque','Effet','Virement','Virsement','En compte','Multi Réglement'])->nullable();
            $table->text('observation')->nullable();
            $table->string('situation')->nullable();
            $table->unsignedInteger('fournisseur_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
