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
            create or replace view v_note_eval_complet as
                select  id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au,id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement, note.id_ue_ec, ec.id_element_constitutif, ec.nom_element_constitutif,e.im, e.id_etudiants, e.nom, e.prenoms, note_ec, note_ue, valide
                from note_eval as note
                join au on note.id_au = au.id_au
                join parcours as p on note.id_parcours = p.id_parcours
                join niveau as n on note.id_niveau = n.id_niveau
                join unite_enseignement as ue on note.id_unite_enseignement = ue.id_unite_enseignement
                join element_constitutif as ec on note.id_element_constitutif = ec.id_element_constitutif
                left join etudiants as e on note.id_etudiants = e.id_etudiants;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_note_eval_complet;
        ');
    }
};
