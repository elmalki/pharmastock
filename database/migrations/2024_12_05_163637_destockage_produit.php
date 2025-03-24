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
        Schema::create('destockage_produit', function (Blueprint $table) {
            $table->foreignIdFor(Produit::class)->constrained();
            $table->foreignIdFor(\App\Models\Destockage::class)->constrained();
            $table->unsignedInteger('qte')->nullable();
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
        Schema::dropIfExists('destockage_produit');
    }

};
