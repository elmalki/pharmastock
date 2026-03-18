<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lot_id');
            $table->foreignId('produit_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->enum('type', ['entree', 'vente', 'destockage', 'ajustement', 'retour']);
            $table->nullableMorphs('reference');
            $table->integer('quantite');
            $table->integer('stock_avant');
            $table->integer('stock_apres');
            $table->string('raison')->nullable();
            $table->timestamps();

            $table->foreign('lot_id')->references('id')->on('commande_produit')->cascadeOnDelete();
            $table->index(['produit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
