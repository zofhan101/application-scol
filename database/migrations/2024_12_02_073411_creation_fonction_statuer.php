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
            create or replace function statuer(a_id_etudiant bigint, a_id_niveau bigint)
                    RETURNS VARCHAR AS
                    $$
                    DECLARE
                        niveau_v RECORD;
                        nb_triplements INTEGER;
                        nb_redoublements INTEGER;
                        dernier_redoublement RECORD;
                    BEGIN
                        SELECT *
                        INTO niveau_v
                        FROM niveau
                        WHERE id_niveau = a_id_niveau;

                        -- récuperer les triplements
                        CREATE TEMP TABLE triplements AS
                            SELECT DISTINCT ON(id_au, id_etudiants) id_au,  id_etudiants, statut_au_suivante
                            FROM resultats_definitifs
                            WHERE id_etudiants = a_id_etudiant
                            AND cycle = niveau_v.cycle
                            AND statut_au_suivante = 'triplant'
                            ORDER BY id_au asc, id_etudiants asc , id_ue asc;

                        SELECT COUNT(*) INTO nb_triplements FROM triplements;
                        IF nb_triplements > 0
                            THEN
                                DROP TABLE triplements;
                                RETURN 'exclu'::VARCHAR;
                        END IF;
                        DROP TABLE triplements;

                        -- cas des redoublements
                        CREATE TEMP TABLE redoublements AS
                            SELECT DISTINCT ON(id_au, id_etudiants) id_au, id_etudiants, id_niveau, rang, statut_au_suivante
                            FROM resultats_definitifs
                            WHERE id_etudiants = a_id_etudiant
                            AND cycle = niveau_v.cycle
                            AND statut_au_suivante = 'redoublant'
                            ORDER BY id_au asc, id_etudiants asc, id_ue asc;

                        SELECT COUNT(*) INTO nb_redoublements FROM redoublements;
                        IF nb_redoublements >= 2
                            THEN
                                DROP TABLE redoublements;
                                return 'exclu'::VARCHAR;
                        ELSEIF nb_redoublements = 1
                            THEN
                                SELECT *
                                INTO dernier_redoublement
                                FROM redoublements;

                                -- cas des niveaux consécutifs
                                IF ABS(niveau_v.rang - dernier_redoublement.rang) = 1
                                    THEN
                                        DROP TABLE redoublements;
                                        RETURN 'exclu'::VARCHAR;

                                -- cas de niveaux non consécutifs
                                ELSEIF ABS(niveau_v.rang - dernier_redoublement.rang) > 1
                                    THEN
                                        DROP TABLE redoublements;
                                        RETURN 'redoublant'::VARCHAR;

                                -- cas d'un même niveaus
                                ELSEIF ABS(niveau_v.rang - dernier_redoublement.rang) = 0
                                    THEN
                                        DROP TABLE redoublements;
                                        RETURN 'triplant'::VARCHAR;
                                END IF;
                        ELSEIF nb_redoublements = 0
                            THEN
                                DROP TABLE redoublements;
                                RETURN 'redoublant'::VARCHAR;
                        END IF;
            END;
            $$ LANGUAGE plpgsql;

            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop function if exists statuer;
        ');
    }
};
