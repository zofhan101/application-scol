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
            create or replace view v_taux_ue_validees as
                select v.id_au, v.id_parcours, v.id_niveau, im, id_etudiants, nombre_ue, nombre_ue_validees, ((nombre_ue_validees::DOUBLE PRECISION/nombre_ue::DOUBLE PRECISION)*100) as pourcentage_validation
                from v_nombre_ue_validees_complet as v
                join v_nombre_ue as n on v.id_au = n.id_au and v.id_parcours = n.id_parcours and v.id_niveau = n.id_niveau;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_taux_ue_validees;
        ');
    }
};
