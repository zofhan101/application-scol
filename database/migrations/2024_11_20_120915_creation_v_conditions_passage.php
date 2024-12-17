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
             create or replace view v_conditions_passage as
                select m.id_au, m.id_parcours, m.id_niveau, m.im, m.id_etudiants, m.date_annulation_inscription, m.statut, m.a_passe_examen, m.total, m.total_coefficient, m.moyenne,  av.nombre_ue, av.nombre_ue_a_valider, nv.nombre_ue_validees, ne.nombre_note_eliminatoire
                from v_moyenne_rep as m
                join v_nombre_ue_validees_complet_rep as nv on (nv.id_au = m.id_au and nv.id_parcours = m.id_parcours and nv.id_niveau = m.id_niveau and nv.id_etudiants = m.id_etudiants) or (nv.id_au = m.id_au and nv.id_parcours = m.id_parcours and nv.id_niveau = m.id_niveau and nv.id_etudiants is null and m.id_etudiants is null)
                join v_nombre_ue_a_valider as av on av.id_au = m.id_au and av.id_parcours = m.id_parcours and av.id_niveau = m.id_niveau
                join v_nombre_note_eliminatoire_rep as ne on (ne.id_au = m.id_au and ne.id_parcours = m.id_parcours and ne.id_niveau = m.id_niveau and ne.id_etudiants = m.id_etudiants) or (ne.id_au = m.id_au and ne.id_parcours = m.id_parcours and ne.id_niveau = m.id_niveau and ne.id_etudiants is null and m.id_etudiants is null);

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_conditions_passage;
        ');
    }
};
