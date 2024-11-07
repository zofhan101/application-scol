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
            create or replace function f_note(p_id_examen_par_au bigint)
            returns table(id_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen varchar, type_session varchar, im varchar, id_etudiants bigint, note double precision) as
            $$
            begin
                return query select a.id_au, a.id_parcours, a.id_niveau, a.id_unite_enseignement, a.id_ue_ec, a.id_examen_par_au, a.id_session_examen, a.nom_session_examen, a.type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note
                                from v_correspondance_note_matricule as n
                                right join f_association_etu_ec(p_id_examen_par_au) as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;
            end;
            $$ language plpgsql;
        ');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists f_note;
        ');
    }
};
