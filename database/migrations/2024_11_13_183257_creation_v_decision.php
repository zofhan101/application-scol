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
            create or replace view v_decision as
                select id_au, id_parcours, id_niveau, id_etudiants, im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire,
                CASE
                    WHEN
                        moyenne >= (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1)
                        AND nombre_ue_validees >= nombre_ue_a_valider
                        AND nombre_note_eliminatoire = 0
                    THEN  'admis'::varchar
                    ELSE 'repechage'::varchar
                END AS decision
                from v_resultats;

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_decision;
        ');
    }
};
