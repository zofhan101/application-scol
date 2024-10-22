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
        Schema::table('etudiants', function (Blueprint $table) {
            $table->BigInteger('id_agent');
            $table->foreign('id_agent')->references('id')->on('users');
            $table->integer('annee_bacc');
            $table->BigInteger('id_serie');
            $table->foreign('id_serie')->references('id_serie')->on('serie');
            $table->BigInteger('id_province');
            $table->foreign('id_province')->references('id_province')->on('province');
            $table->BigInteger('id_nationalite');
            $table->foreign('id_nationalite')->references('id_nationalites')->on('nationalites');

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
