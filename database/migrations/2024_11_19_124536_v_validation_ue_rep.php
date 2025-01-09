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
            create or replace view v_validation_ue_rep as
                select id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session_retenue, date_annulation_inscription, coefficient, id_unite_enseignement, im, id_etudiants, note_ue,statut, a_passe_examen, 
                    case
                        when note_ue >= (select note_validation_ue as moyenne from note_validation_ue order by id_note_validation_ue desc limit 1) then 'V'
                        when note_ue > (select note_elim from note_eliminatoire order by id_note_eliminatoire desc limit 1) and note_ue < (select note_validation_ue from note_validation_ue order by id_note_validation_ue desc limit 1) then 'N'
                        else 'E'
                    end AS valide
                from v_note_ue_rep;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_validation_ue_rep;
        ');
    }
};
