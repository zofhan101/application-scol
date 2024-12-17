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
            create or replace view  v_check_inscription_ue_ec as
            select i.*, ue_ec.id_ue_ec, coefficient, id_examen_par_au, id_unite_enseignement, id_element_constitutif
            from ue_ec_parcours_niveau_au as ue_ec
            join v_inscrits as i on ue_ec.id_parcours = i.id_parcours and ue_ec.id_niveau = i.id_niveau and ue_ec.id_au = i.id_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('drop view if exists v_check_inscription_ue_ec');
    }
};
