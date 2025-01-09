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
        Schema::create('resultats_definitifs', function (Blueprint $table) {
            $table->id('id_resultat_definitif');

            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');

            $table->BigInteger('id_parcours');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');

            $table->BigInteger('id_niveau');
            $table->foreign('id_niveau')->references('id_niveau')->on('niveau');
            $table->integer('cycle');
            $table->string('nom_niveau');
            $table->integer('rang');
            $table->string('nom_niveau_long')->nullable();

            $table->BigInteger('id_examen_par_au');
            $table->foreign('id_examen_par_au')->references('id_examen_par_au')->on('examen_par_au');

            $table->BigInteger('id_session_examen');
            $table->foreign('id_session_examen')->references('id_session_examen')->on('session_examen');
            $table->string('nom_session_examen');
            $table->string('type_session_retenue');

            $table->BigInteger('id_etudiants');
            $table->foreign('id_etudiants')->references('id_etudiants')->on('etudiants');
            $table->string('im');
            $table->date('date_annulation_inscription')->nullable();
            $table->string('statut');


            $table->BigInteger('id_ue');
            $table->foreign('id_ue')->references('id_unite_enseignement')->on('unite_enseignement');
            $table->double('coef');

            $table->BigInteger('id_ec');
            $table->foreign('id_ec')->references('id_element_constitutif')->on('element_constitutif');
            $table->double('note_ue');
            $table->double('note_ec');
            $table->string('valide');

            $table->double('total');
            $table->double('total_coefficient');
            $table->double('moyenne');
            $table->double('moyenne_passage');
            $table->integer('nombre_ue');
            $table->integer('nombre_ue_a_valider');
            $table->integer('nombre_ue_validees');
            $table->integer('nombre_note_elim');

            $table->string('statut_au_suivante');

            $table->BigInteger('id_niveau_suivant')->nullable();
            $table->foreign('id_niveau_suivant')->references('id_niveau')->on('niveau');

            $table->string('intitule');
            $table->string('nom_parcours')->nullable();
            $table->string('nom_niveau_suivant')->nullable();
            $table->integer('rang_suivant')->nullable();
            $table->string('cycle_suivant')->nullable();
            $table->string('nom_etudiant');
            $table->string('prenoms');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('nom_unite_enseignement');
            $table->string('nom_element_constitutif');
            $table->boolean('a_passe_examen')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats_definitifs');
    }
};
