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
        Schema::create('resultats_avant_repechage', function (Blueprint $table) {
            $table->id('id_resultats_avant_repechage');
            $table->BigInteger('id_note_eval');
            $table->foreign('id_note_eval')->references('id_note_eval')->on('note_eval');

            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');

            $table->BigInteger('id_parcours');;
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');

            $table->BigInteger('id_niveau');
            $table->foreign('id_niveau')->references('id_niveau')->on('niveau');

            $table->BigInteger('id_examen_par_au');
            $table->foreign('id_examen_par_au')->references('id_examen_par_au')->on('examen_par_au');
            $table->BigInteger('id_session_examen');
            $table->foreign('id_session_examen')->references('id_session_examen')->on('session_examen');
            $table->string('nom_session_examen');
            $table->string('type_session');

            $table->date('date_annulation_inscription')->nullable();

            $table->double('coefficient');
            $table->BigInteger('id_unite_enseignement');
            $table->foreign('id_unite_enseignement')->references('id_unite_enseignement')->on('unite_enseignement');

            $table->BigInteger('id_ue_ec');
            $table->foreign('id_ue_ec')->references('id_ue_ec')->on('ue_ec_parcours_niveau_au');
            $table->BigInteger('id_element_constitutif');
            $table->foreign('id_element_constitutif')->references('id_element_constitutif')->on('element_constitutif');

            $table->string('im')->nullable();
            $table->BigInteger('id_etudiants')->nullable();
            $table->foreign('id_etudiants')->references('id_etudiants')->on('etudiants');

            $table->double('note_ec');
            $table->double('note_ue');
            $table->enum('valide',['E', 'N', 'V']);

            $table->double('total');
            $table->double('total_coefficient');
            $table->double('moyenne');
            $table->integer('nombre_ue');
            $table->integer('nombre_ue_validees');
            $table->integer('nombre_ue_a_valider');
            $table->integer('nombre_note_eliminatoire');
            $table->enum('decision',['valide', 'repechage']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats_avant_repechage');
    }
};
