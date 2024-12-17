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
            create or replace view v_moyenne_rep as
                select id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants,total, total_coefficient, (total/total_coefficient) as moyenne, statut, a_passe_examen
                from v_total_note_rep;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_moyenne_rep;
        ');
    }
};
