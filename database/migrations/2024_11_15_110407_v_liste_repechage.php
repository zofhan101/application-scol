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
        DB::statement("
            create materialized view v_liste_repechage as
                select distinct on (id_au, id_parcours, id_niveau, id_etudiants, id_unite_enseignement) id_au, intitule, id_parcours, nom_parcours, id_niveau, nom_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, id_unite_enseignement, nom_unite_enseignement, id_etudiants, im, nom, prenoms, valide, decision
                from v_resultats_avant_repechage_complet
                where decision = 'repechage'
                order by id_au, id_parcours, id_niveau, id_etudiants, id_unite_enseignement, id_ue_ec asc;

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::statement('
            drop materialized view if exists v_liste_repechage_affichage;
        ');
        DB::statement('
            drop materialized view if exists v_liste_repechage
        ');
    }
};
