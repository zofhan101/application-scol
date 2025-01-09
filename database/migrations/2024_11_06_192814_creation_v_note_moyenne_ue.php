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
            create or replace view v_note_moyenne_ue as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note, date_annulation
            from v_note
            group by id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants, date_annulation

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_note_moyenne_ue
        ');
    }
};
