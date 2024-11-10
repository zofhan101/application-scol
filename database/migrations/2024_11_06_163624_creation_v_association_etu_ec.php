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
        DB::statement('
            create or replace view v_ue_ec_eval as
            select ue_ec.*, se.id_session_examen, nom_session_examen, type_session
            from ue_ec_parcours_niveau_au as ue_ec
            join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where type_session = \'eval\' or type_session = \'repe\';

        ');
        DB::statement('
            create or replace view v_association_etu_ec as
            select ue_ec.id_au, ue_ec.id_parcours, ue_ec.id_niveau, ue_ec.coefficient, id_unite_enseignement, id_ue_ec, ue_ec.id_element_constitutif, ue_ec.id_examen_par_au, id_session_examen, nom_session_examen, type_session,id_etudiants, im, date_annulation
            from v_ue_ec_eval as ue_ec
            left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_association_etu_ec;
        ');

        DB::statement('
            drop view if exists v_ue_ec_eval;
        ');
    }
};
