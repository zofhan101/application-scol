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
            CREATE OR REPLACE VIEW v_non_admis as
                SELECT *
                FROM resultats_definitifs
                WHERE statut_au_suivante != 'passant' AND id_etudiants IS NOT NULL AND date_annulation_inscription is NULL;
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_historique_redoublement_triplement AS
                SELECT DISTINCT ON(id_etudiants, id_au) id_au, intitule, id_parcours, nom_parcours, id_niveau, nom_niveau, id_etudiants, im, nom_etudiant, prenoms, statut_au_suivante
                FROM resultats_definitifs
                WHERE (statut_au_suivante = 'redoublant' or statut_au_suivante = 'triplant')  AND date_annulation_inscription is NULL
                ORDER BY id_etudiants, id_au, id_ue;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::statement('
            drop view if exists v_historique_redoublement_triplement;
        ');

        DB::statement('
            drop view if exists v_non_admis;
        ');
    }
};
