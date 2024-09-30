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
        Schema::create('niveau', function (Blueprint $table) {
            $table->id('id_niveau');
            $table->string('nom_niveau');
            $table->Integer('rang');
            $table->timestamps();
        });

        Schema::create('inscription', function (Blueprint $table) {
            $table->id('id_inscription');
            $table->date('date_inscription');
            $table->date('date_annulation')->nullable();
            $table->BigInteger('id_agent_inscription');
            $table->foreign('id_agent_inscription')->references('id')->on('users');
            $table->BigInteger('id_agent_annulation')->nullable();
            $table->foreign('id_agent_annulation')->references('id')->on('users');
            $table->BigInteger('id_etudiant');
            $table->foreign('id_etudiant')->references('id_etudiants')->on('etudiants');
            $table->BigInteger('id_niveau');
            $table->foreign('id_niveau')->references('id_niveau')->on('niveau');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('niveau');

    }
};
