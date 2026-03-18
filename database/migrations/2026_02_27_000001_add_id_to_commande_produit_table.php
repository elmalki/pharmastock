<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commande_produit', function (Blueprint $table) {
            $table->id()->first();
            $table->unique(['produit_id', 'commande_id', 'n_lot'], 'commande_produit_unique_lot');
        });
    }

    public function down(): void
    {
        Schema::table('commande_produit', function (Blueprint $table) {
            $table->dropUnique('commande_produit_unique_lot');
            $table->dropColumn('id');
        });
    }
};
