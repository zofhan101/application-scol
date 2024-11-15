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
            create or replace view v_resultats as
                select m.id_au, m.id_parcours, m.id_niveau, m.id_etudiants, m.im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire
                from v_moyennes as m
                join v_taux_ue_validees as v on (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau and m.id_etudiants = v.id_etudiants) or (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau and m.id_etudiants is null and  v.id_etudiants is null )
                join v_nombre_note_eliminatoire as e on (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants = e.id_etudiants) or (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants is null and e.id_etudiants is null);

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_resultats;
        ');
    }
};
