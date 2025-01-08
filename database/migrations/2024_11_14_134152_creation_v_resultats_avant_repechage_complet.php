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
            create materialized view v_resultats_avant_repechage_complet as
                select id_resultats_avant_repechage, id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement, id_ue_ec, ec.id_element_constitutif, ec.nom_element_constitutif, e.id_etudiants, e.im, e.nom, e.prenoms, note_ec, note_ue, valide,  total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire, decision
                from resultats_avant_repechage as r
                join au on r.id_au = au.id_au
                join parcours as p on r.id_parcours = p.id_parcours
                join niveau as n on r.id_niveau = n.id_niveau
                join unite_enseignement as ue on r.id_unite_enseignement = ue.id_unite_enseignement
                join element_constitutif as ec on r.id_element_constitutif = ec.id_element_constitutif
                left join etudiants as e on r.id_etudiants = e.id_etudiants;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop materialized view if exists v_resultats_avant_repechage_complet;
        ');
    }
};
