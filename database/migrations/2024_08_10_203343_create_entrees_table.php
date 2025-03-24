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
        Schema::create('entrees', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('qte')->nullable();
            $table->string('n_bon')->nullable();
            $table->string('n_lot')->nullable();
            $table->string('n_facture')->nullable();
            $table->date('expirationDate')->nullable();
            $table->double('prix_achat')->nullable();
            $table->double('prix_vente')->nullable();
            $table->foreignIdFor(\App\Models\Produit::class)->nullable();
            $table->foreignIdFor(\App\Models\Commande::class)->nullable();
            $table->foreignIdFor(\App\Models\Fournisseur::class)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrees');
    }
};
