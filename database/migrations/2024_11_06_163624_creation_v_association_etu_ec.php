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
            CREATE OR REPLACE FUNCTION ue_ec_eval_filtre(p_id_examen_par_au bigint)
            RETURNS TABLE (id_ue_ec bigint, coefficient double precision, id_examen_par_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_element_constitutif bigint, id_au bigint, id_session_examen bigint, nom_session_examen varchar, type_session varchar) AS
            $$
            BEGIN
                RETURN QUERY SELECT v.id_ue_ec, v.coefficient , v.id_examen_par_au, v.id_parcours, v.id_niveau, v.id_unite_enseignement, v.id_element_constitutif, v.id_au, v.id_session_examen, v.nom_session_examen, v.type_session FROM v_ue_ec_eval as v
                                WHERE v.id_examen_par_au = p_id_examen_par_au;
            END;
            $$ LANGUAGE plpgsql;
        ');

        DB::statement('
            create or replace function f_association_etu_ec(p_id_examen_par_au bigint)
            returns table(id_au bigint, id_parcours bigint, id_niveau bigint, coefficient double precision, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen varchar,  type_session varchar,id_etudiants bigint, im varchar, date_annulation date) as
            $$
            BEGIN
                return query select i.id_au, i.id_parcours, i.id_niveau, ue_ec.coefficient, ue_ec.id_unite_enseignement, ue_ec.id_ue_ec, ue_ec.id_examen_par_au, ue_ec.id_session_examen, ue_ec.nom_session_examen, ue_ec.type_session,i.id_etudiants, i.im, i.date_annulation
                                from ue_ec_eval_filtre(p_id_examen_par_au) as ue_ec
                                left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

            end;
            $$ LANGUAGE plpgsql;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop function if exists f_association_etu_ec;
        ');

        DB::statement('
            drop function if exists ue_ec_eval_filtre;
        ');


        DB::statement('
            drop view if exists v_ue_ec_eval;
        ');
    }
};
