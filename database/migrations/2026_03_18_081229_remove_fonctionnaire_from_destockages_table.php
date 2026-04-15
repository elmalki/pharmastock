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
        Schema::table('destockages', function (Blueprint $table) {
            $table->dropColumn('fonctionnaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destockages', function (Blueprint $table) {
            $table->string('fonctionnaire')->nullable();
        });
    }
};
