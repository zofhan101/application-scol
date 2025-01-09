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
            create or replace view v_note_eval_ue as
            select distinct on( id_au, id_parcours, id_niveau, id_examen_par_au,id_unite_enseignement,im)id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au,id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, im, id_etudiants, note_ue, valide
            from note_eval
            order by id_au, id_parcours, id_niveau, id_examen_par_au,id_unite_enseignement,im, id_ue_ec asc

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_note_eval_ue
        ');
    }
};
