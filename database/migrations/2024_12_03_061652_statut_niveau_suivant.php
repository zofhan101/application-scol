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
            create or replace view v_statut_avant_deliberation as
                select id_au, id_parcours, id_niveau, im, id_etudiants, date_annulation_inscription, statut, a_passe_examen, total, total_coefficient, (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1) as moyenne_passage, moyenne,  nombre_ue, nombre_ue_a_valider, nombre_ue_validees, nombre_note_eliminatoire,
                CASE
                    WHEN date_annulation_inscription is not null AND statut = 'passant' and a_passe_examen = FALSE
                    THEN 'passant'::VARCHAR
                    WHEN date_annulation_inscription is not null AND statut = 'passant' and a_passe_examen = FALSE
                    THEN 'redoublant'::VARCHAR
                    WHEN
                        moyenne >= (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1)
                        AND nombre_ue_validees >= nombre_ue_a_valider
                        AND nombre_note_eliminatoire = 0
                    THEN  'passant'::varchar
                    ELSE statuer(id_etudiants, id_niveau)
                END AS statut_au_suivante
                from v_conditions_passage;

        ");

        DB::statement("

            create or replace function get_niveau_suivant(a_statut VARCHAR, a_id_niveau BIGINT)
                 RETURNS BIGINT AS
                 $$
                 DECLARE
                     niveau_v RECORD;
                     niveau_suivant RECORD;
                 BEGIN
                     SELECT *
                     INTO niveau_v
                     FROM niveau
                     WHERE id_niveau = a_id_niveau;

                     IF a_statut = 'exclus'
                        THEN RETURN NULL;
                     ELSEIF a_statut = 'redoublant' or a_statut = 'triplant'
                         THEN RETURN a_id_niveau;
                     ELSEIF a_statut = 'passant'
                         THEN
                             SELECT *
                             INTO niveau_suivant
                             FROM niveau
                             WHERE rang = niveau_v.rang + 1;

                             IF niveau_suivant IS NOT NULL
                                THEN RETURN niveau_suivant.id_niveau;
                             ELSE
                                RETURN NULL;
                             END IF;
                     END IF;

                 END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement('
            create or replace view v_niveau_suivant_avant_deliberation as
                select id_au, id_parcours, id_niveau, im, id_etudiants, date_annulation_inscription, statut, a_passe_examen, total, total_coefficient, moyenne_passage, moyenne,  nombre_ue, nombre_ue_a_valider, nombre_ue_validees, nombre_note_eliminatoire,statut_au_suivante, get_niveau_suivant(statut, id_niveau) as niveau_suivant
                from v_statut_avant_deliberation;

        ');


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            DROP VIEW IF EXISTS v_niveau_suivant_avant_deliberation;
        ');

        DB::statement('
            drop function if exists get_niveau_suivant;
        ');

        DB::statement('
            drop view if exists v_statut_avant_deliberation;
        ');
    }
};
