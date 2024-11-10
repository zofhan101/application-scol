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
            create or replace view v_note_validation_ue as
            select id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, coefficient, id_unite_enseignement, date_annulation,im, id_etudiants,note,
                case
                    when note >= (select (note_max/2) as moyenne from note_max order by id_note_max desc limit 1) then 'V'
                    when note > (select note_elim from note_eliminatoire order by id_note_eliminatoire desc limit 1) and note < (select (note_max/2) as moyenne from note_max order by id_note_max desc limit 1) then 'N'
                    else 'E'
                end AS valide
            from v_note_moyenne_ue

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_note_validation_ue
        ');
    }
};
