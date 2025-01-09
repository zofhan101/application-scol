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
            create or replace view v_nombre_ue_validees_rep as
                select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_ue_validees
                from v_validation_ue_rep
                where valide = 'V'
                group by id_au, id_parcours, id_niveau, im, id_etudiants, statut;
        ");

        DB::statement('
            create or replace view v_nombre_ue_validees_sans_inscrits_rep as
                select distinct on(id_au, id_parcours, id_niveau) id_au, id_parcours, id_niveau, im, id_etudiants, 0 as nombre_ue_validees
                from v_validation_ue_rep
                where id_etudiants is null
                order by id_au, id_parcours, id_niveau, id_examen_par_au asc ,id_unite_enseignement asc;
        ');

        DB::statement('
            create or replace view v_nombre_ue_validees_complet_rep as
                select * from v_nombre_ue_validees_rep
                union all
                select * from  v_nombre_ue_validees_sans_inscrits_rep;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue_validees_complet_rep;
        ');

        DB::statement('
            drop view if exists v_nombre_ue_validees_sans_inscrits_rep;
        ');

        DB::statement('
            drop view if exists v_nombre_ue_validees_rep;
        ');
    }
};
