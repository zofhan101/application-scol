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
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id('id_etudiants');
            $table->string('im',50)->unique();
            $table->string('nom');
            $table->string('prenoms');
            $table->enum('sexe',['m','f']);
            $table->date('date_premiere_inscription');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('num_piece_identite',100)->nullable();
            $table->date('date_delivrance')->nullable();
            $table->string('lieu_delivrance')->nullable();
            $table->string('adresse');
            $table->string('telephone',20);
            $table->string('pere');
            $table->string('profession_pere');
            $table->string('tel_pere',20);
            $table->string('adresse_pere');
            $table->string('mere');
            $table->string('profession_mere');
            $table->string('tel_mere',20);
            $table->string('adresse_mere');
            $table->string('etablissement_transfert')->nullable();
            $table->string('niveau_au_transfert',20)->nullable();
            $table->string('au_transfert',20)->nullable();
            $table->boolean('est_officier');

            $table->BigInteger('id_parcours');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
