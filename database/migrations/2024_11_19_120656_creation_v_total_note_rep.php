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
            create or replace view v_total_note_rep as
                select id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants, sum(note_ue * coefficient) as total, sum(coefficient) as total_coefficient, statut, a_passe_examen
                from v_note_ue_rep
                group by id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants, statut, a_passe_examen;


        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_total_note_rep;
        ');
    }
};
