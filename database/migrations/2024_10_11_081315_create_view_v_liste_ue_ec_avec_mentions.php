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
            create or replace view v_liste_ue_ec_avec_mentions as
            select ue.id_unite_enseignement, ue.nom_unite_enseignement, ec.id_element_constitutif, ec.nom_element_constitutif, c.coefficient, c.id_ue_ec, epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, m.id_mention, m.nom_mention, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, au.id_au, au.intitule
            from ue_ec_parcours_niveau_au as c
                join unite_enseignement as ue on c.id_unite_enseignement = ue.id_unite_enseignement
                join element_constitutif as ec on c.id_element_constitutif = ec.id_element_constitutif
                join examen_par_au as epa on c.id_examen_par_au = epa.id_examen_par_au
                join session_examen as se on epa.id_session_examen = se.id_session_examen
                join parcours as p on c.id_parcours = p.id_parcours
                join mention as m on p.id_mention = m.id_mention
                join niveau as n on c.id_niveau = n.id_niveau
                join au on c.id_au = au.id_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_liste_ue_ec_avec_mentions
        ');
    }
};
