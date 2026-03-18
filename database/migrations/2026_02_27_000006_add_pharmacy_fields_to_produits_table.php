<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->string('dci')->nullable()->after('label');
            $table->string('forme')->nullable()->after('dci');
            $table->string('dosage')->nullable()->after('forme');
            $table->string('laboratoire')->nullable()->after('dosage');
            $table->boolean('ordonnance_requise')->default(false)->after('perissable');
            $table->decimal('prix_public', 8, 2)->nullable()->after('ordonnance_requise');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['dci', 'forme', 'dosage', 'laboratoire', 'ordonnance_requise', 'prix_public']);
        });
    }
};
