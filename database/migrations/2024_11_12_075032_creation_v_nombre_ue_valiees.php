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
            create or replace view v_nombre_ue_validees as
                select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_ue_validees
                from v_note_eval_ue
                where valide = 'V' and type_session = 'eval'
                group by id_au, id_parcours, id_niveau, im, id_etudiants;

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue_validees;
        ');
    }
};
