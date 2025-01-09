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
            create or replace view v_note_ue_avec_ec_rep as
            select n.id_au, n.id_parcours, n.id_niveau, n.id_examen_par_au, n.id_session_examen, n.nom_session_examen, n.type_session, n.date_annulation, n.coefficient, n.id_unite_enseignement, n.id_ue_ec, n.id_element_constitutif, n.im, n.id_etudiants, n.note as note_ec, v.note as note_ue, n.statut, n.a_passe_examen
            from v_note_moyenne_ue_rep as v
            join v_note as n on (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants = v.id_etudiants) or (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants is null and v.id_etudiants is null);
        ');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists  v_note_ue_avec_ec_rep;
        ');
    }
};
