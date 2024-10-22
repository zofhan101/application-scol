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
        Schema::create('autres_inscriptions',function (Blueprint $table) {
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->BigInteger('id_etudiants');
            $table->foreign('id_etudiants')->references('id_etudiants')->on('etudiants');
            $table->String('etablissement');
            $table->String('niveau',50);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autres_inscriptions');

    }
};
