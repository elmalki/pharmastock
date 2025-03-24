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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('label')->nullable();
            $table->string('unite')->nullable();
            $table->text('description')->nullable();
            // $table->date('dateRappel')->nullable();
            $table->boolean('perissable')->default(false);
            $table->boolean('generated')->default(false);
            $table->unsignedInteger('limit_command')->nullable();
            $table->foreignIdFor(\App\Models\Categorie::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
