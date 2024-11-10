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
            create or replace view v_note as
                select a.id_au, a.id_parcours, a.id_niveau, a.coefficient, a.id_unite_enseignement, a.id_ue_ec, a.id_element_constitutif, a.id_examen_par_au, id_session_examen, nom_session_examen, type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note, date_annulation
                from v_correspondance_note_matricule as n
                right join v_association_etu_ec as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;

        ');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_note;
        ');
    }
};
