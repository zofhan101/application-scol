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
                select m.id_au, m.id_parcours, m.id_niveau, m.id_etudiants, m.im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire
                from v_moyennes as m
                join v_nombre_ue_a_valider as v on (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau )
                join v_nombre_note_eliminatoire as e on (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants = e.id_etudiants) or (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants is null and e.id_etudiants is null)
                join v_nombre_ue_validees_complet as c on (m.id_au = c.id_au and m.id_parcours = c.id_parcours and m.id_niveau = c.id_niveau and m.id_etudiants = c.id_etudiants) or (m.id_au = c.id_au and m.id_parcours = c.id_parcours and m.id_niveau = c.id_niveau and m.id_etudiants is null and c.id_etudiants is null);
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
