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
        Schema::create('selectionnes', function (Blueprint $table) {
            $table->id('id_selectionnes');
            $table->string('nom');
            $table->string('prenoms');
            $table->string('num_bacc');
            $table->BigInteger('id_parcours');
            $table->BigInteger('id_au');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->boolean('est_inscrit')->nullable();
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
