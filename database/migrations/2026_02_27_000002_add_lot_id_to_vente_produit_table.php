<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vente_produit', function (Blueprint $table) {
            $table->unsignedBigInteger('lot_id')->nullable()->after('vente_id');
            $table->foreign('lot_id')->references('id')->on('commande_produit')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vente_produit', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->dropColumn('lot_id');
        });
    }
};
