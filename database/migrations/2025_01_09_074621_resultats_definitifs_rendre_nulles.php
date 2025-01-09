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
            drop view if exists v_non_admis;
        ');

        DB::statement('
            drop view if exists v_historique_redoublement_triplement;
        ');


        DB::statement('
            drop view if exists v_export_ent;
        ');

        Schema::table('resultats_definitifs', function (Blueprint $table) {
            $table->string('type_session_retenue')->nullable()->change();
            $table->date('date_annulation_inscription')->nullable()->change();
            $table->double('coef')->nullable()->change();
            $table->bigInteger('id_ec')->nullable()->change();
            $table->double('note_ec')->nullable()->change();
            $table->string('valide')->nullable()->change();
            $table->string('nom_element_constitutif')->nullable()->change();
            $table->double('total_coefficient')->nullable()->change();
            $table->double('moyenne_passage')->nullable()->change();
            $table->integer('nombre_ue')->nullable()->change();
            $table->integer('nombre_ue_a_valider')->nullable()->change();
            $table->integer('nombre_ue_validees')->nullable()->change();
            $table->integer('nombre_note_elim')->nullable()->change();
        });

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

        DB::statement("

             CREATE OR REPLACE VIEW v_export_ent AS
                SELECT
                    DISTINCT ON (id_au, im)
                    id_au,
                    id_mention_ent AS idmention,
                    COALESCE(id_parcours_ent, 0) AS idparcours,
                    0 as rang,
                    CASE
                        WHEN nombre_ue_validees < nombre_ue_a_valider OR nombre_note_elim > 0
                            THEN 'NE'::VARCHAR
                        ELSE (round(moyenne::NUMERIC, 2))::VARCHAR
                    END AS noteFin,
                    id_niveau as idNiveau,
                    im
                FROM resultats_definitifs as res
                JOIN
                    parcours as p on res.id_parcours =  p.id_parcours
                JOIN
                    mention as m on p.id_mention = m.id_mention
                LEFT JOIN
                    correspondance_parcours_ent as cp on p.id_parcours = cp.id_parcours
                JOIN
                    correspondance_mention_ent as cm on m.id_mention = cm.id_mention
                ORDER BY id_au, im;

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_non_admis;
        ');

        DB::statement('
            drop view if exists v_historique_redoublement_triplement;
        ');

        DB::statement('
            drop view if exists v_export_ent;
        ');


        Schema::table('resultats_definitifs', function (Blueprint $table) {
            $table->string('type_session_retenue')->nullable(false)->change();
            $table->date('date_annulation_inscription')->nullable(false)->change();
            $table->double('coef')->nullable(false)->change();
            $table->bigInteger('id_ec')->nullable(false)->change();
            $table->double('note_ec')->nullable(false)->change();
            $table->string('valide')->nullable(false)->change();
            $table->string('nom_element_constitutif')->nullable(false)->change();
            $table->double('total_coefficient')->nullable(false)->change();
            $table->double('moyenne_passage')->nullable(false)->change();
            $table->integer('nombre_ue')->nullable(false)->change();
            $table->integer('nombre_ue_a_valider')->nullable(false)->change();
            $table->integer('nombre_ue_validees')->nullable(false)->change();
            $table->integer('nombre_note_elim')->nullable(false)->change();
        });

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


        DB::statement("

             CREATE OR REPLACE VIEW v_export_ent AS
                SELECT
                    DISTINCT ON (id_au, im)
                    id_au,
                    id_mention_ent AS idmention,
                    COALESCE(id_parcours_ent, 0) AS idparcours,
                    0 as rang,
                    CASE
                        WHEN nombre_ue_validees < nombre_ue_a_valider OR nombre_note_elim > 0
                            THEN 'NE'::VARCHAR
                        ELSE (round(moyenne::NUMERIC, 2))::VARCHAR
                    END AS noteFin,
                    id_niveau as idNiveau,
                    im
                FROM resultats_definitifs as res
                JOIN
                    parcours as p on res.id_parcours =  p.id_parcours
                JOIN
                    mention as m on p.id_mention = m.id_mention
                LEFT JOIN
                    correspondance_parcours_ent as cp on p.id_parcours = cp.id_parcours
                JOIN
                    correspondance_mention_ent as cm on m.id_mention = cm.id_mention
                ORDER BY id_au, im;

        ");

    }

};
