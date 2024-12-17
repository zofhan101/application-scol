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
        Schema::create('barcode_matricule', function (Blueprint $table) {
            $table->BigInteger('id_ue_ec');
            $table->foreign('id_ue_ec')->references('id_ue_ec')->on('ue_ec_parcours_niveau_au');
            $table->integer('numero');
            $table->string('matricule');
            $table->foreign('matricule')->references('im')->on('etudiants');
            $table->primary(['id_ue_ec', 'numero']);
            $table->unique(['id_ue_ec', 'matricule']);
            $table->boolean('verifie')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_matriule');
    }
};
