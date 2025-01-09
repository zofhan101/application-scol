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
       DB::statement("
            create or replace view v_liste_ue as
                select distinct on (ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement) ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement
                from ue_ec_parcours_niveau_au as ue_ec
                join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
                join session_examen as se on epa.id_session_examen = se.id_session_examen
                where type_session = 'eval'
                order by ue_ec.id_au desc, id_parcours, id_niveau, id_unite_enseignement, id_ue_ec asc;

       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_liste_ue;
        ');
    }
};
