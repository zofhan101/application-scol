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
            create or replace view v_nombre_ue_validees_sans_inscrits as
                select distinct on(id_au, id_parcours, id_niveau) id_au, id_parcours, id_niveau, im, id_etudiants, 0 as nombre_ue_validees
                from v_note_eval_ue
                where id_etudiants is null and type_session = 'eval'
                order by id_au, id_parcours, id_niveau, id_examen_par_au asc ,id_unite_enseignement asc;

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue_validees_sans_inscrits;
        ');
    }
};
