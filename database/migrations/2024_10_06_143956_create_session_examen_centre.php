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
        Schema::create('session_examen', function (Blueprint $table) {
            $table->id('id_session_examen');
            $table->string('nom_session_examen');
            $table->timestamps();
        });

        Schema::create('examen_par_au', function (Blueprint $table) {
            $table->id('id_examen_par_au');
            $table->BigInteger('id_session_examen');
            $table->foreign('id_session_examen')->references('id_session_examen')->on('session_examen');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->timestamps();
        });

        Schema::create('ue_ec_parcours_niveau_au', function (Blueprint $table) {
            $table->id('id_ue_ec');
            $table->double('coefficient');
            $table->BigInteger('id_examen_par_au');
            $table->foreign('id_examen_par_au')->references('id_examen_par_au')->on('examen_par_au');

            $table->BigInteger('id_parcours');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');

            $table->BigInteger('id_niveau');
            $table->foreign('id_niveau')->references('id_niveau')->on('niveau');

            $table->BigInteger('id_unite_enseignement');
            $table->foreign('id_unite_enseignement')->references('id_unite_enseignement')->on('unite_enseignement');


            $table->BigInteger('id_element_constitutif');
            $table->foreign('id_element_constitutif')->references('id_element_constitutif')->on('element_constitutif');

            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');


            $table->timestamps();
        });

        DB::statement('
            create or replace view v_liste_ue_ec as
            select ue.id_unite_enseignement, ue.nom_unite_enseignement, ec.id_element_constitutif, ec.nom_element_constitutif, c.coefficient, c.id_ue_ec, epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, au.id_au, au.intitule
            from ue_ec_parcours_niveau_au as c
            join unite_enseignement as ue on c.id_unite_enseignement = ue.id_unite_enseignement
            join element_constitutif as ec on c.id_element_constitutif = ec.id_element_constitutif
            join examen_par_au as epa on c.id_examen_par_au = epa.id_examen_par_au
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            join parcours as p on c.id_parcours = p.id_parcours
            join niveau as n on c.id_niveau = n.id_niveau
            join au on c.id_au = au.id_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_liste_ue_ec;'
        );
        Schema::dropIfExists('ue_ec_parcours_niveau_au');
        Schema::dropIfExists('examen_par_au');
        Schema::dropIfExists('session_examen');
    }
};
