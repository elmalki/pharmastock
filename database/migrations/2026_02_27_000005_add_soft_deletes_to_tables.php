<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
