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
        Schema::create('commande_produit', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Produit::class)->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Commande::class)->onDelete('cascade');
            $table->string('n_lot')->nullable();
            $table->unsignedInteger('tva')->default(0);// tva for the invoices
            $table->unsignedInteger('qte')->nullable();
            $table->unsignedInteger('qte_achete')->nullable();
            $table->decimal('prix_achat',8,2)->unsigned()->nullable();
            $table->decimal('prix_vente',8,2)->unsigned()->nullable();
            $table->date('expirationDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
