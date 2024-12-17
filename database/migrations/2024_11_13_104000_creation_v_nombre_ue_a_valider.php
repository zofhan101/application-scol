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
            create or replace view v_nombre_ue_a_valider as
                select id_au, id_parcours, id_niveau, nombre_ue, FLOOR((nombre_ue * (select pourcentage_admission from pourcentage_admission order by id_pourcentage_admission desc limit 1))/100) AS nombre_ue_a_valider
                from v_nombre_ue;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue_a_valider;
        ');
    }
};
