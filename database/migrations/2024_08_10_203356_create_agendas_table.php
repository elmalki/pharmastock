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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('degree', ['Urgent', 'Moyen', 'Ordinaire'])->default('Ordinaire');
            $table->date('debut')->nullable();
            $table->time('hdebut')->default('00:00');
            $table->date('fin')->nullable();
            $table->date('dateRappel')->nullable();
            $table->time('hfin')->default('00:00');
            $table->date('startRecur')->nullable();
            $table->date('endRecur')->nullable();
            $table->boolean('isRecurrent')->default(false);
            $table->tinyInteger('qte')->nullable();
            $table->enum('unit',['jour','semaine','mois','année']);
            $table->unsignedInteger('event_id')->nullable();
          //  $table->foreign('event_id')->references('id')->on('agendas')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
