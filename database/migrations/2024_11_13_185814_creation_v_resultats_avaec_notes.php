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
            create or replace view v_resultats_avec_notes as
                select n.*, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire, decision
                from v_decision as d
                join note_eval as n on (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants = n.id_etudiants) or (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants is null and n.id_etudiants is null);

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_resultats_avec_notes;
        ');
    }
};
