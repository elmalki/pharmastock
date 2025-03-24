<?php

use App\Models\Produit;
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
        Schema::create('vente_produit', function (Blueprint $table) {
            $table->foreignIdFor(Produit::class)->constrained();
            $table->foreignIdFor(\App\Models\Vente::class)->constrained();
            $table->unsignedInteger('tva')->default(0);
            $table->double('prix',8,2)->unsigned()->nullable();
            $table->double('prix_achat',8,2)->unsigned()->nullable();
            $table->unsignedInteger('qte')->nullable();
            $table->unsignedInteger('remise')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vente_produit');
    }

};
