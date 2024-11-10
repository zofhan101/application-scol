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
                create or replace function creer_v_resultats_eval(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint, a_id_examen_par_au bigint)
                RETURNS void AS
                $$
                DECLARE
                    nom_ue RECORD;
                    requete TEXT := 'CREATE OR REPLACE VIEW v_resultats_eval AS SELECT ROW_NUMBER() OVER (ORDER BY NULL) AS n° ,im, nom, prenoms';
                    colonnes TEXT := '';
                BEGIN
                    FOR nom_ue IN
                        select distinct nom_unite_enseignement
                        from v_note_eval_ue_complet as v
                        where v.id_au = a_id_au
                            and v.id_parcours = a_id_parcours
                            and v.id_niveau = a_id_niveau
                            and v.id_examen_par_au = a_id_examen_par_au
                    LOOP
                        colonnes := colonnes ||
                            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS \"Résultats ' || nom_ue.nom_unite_enseignement || '\"';
                    END LOOP;

                    requete := requete || colonnes || ' FROM  v_note_eval_ue_complet WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau || ' and id_examen_par_au = ' || a_id_examen_par_au || ' GROUP BY im, nom, prenoms;' ;

                    EXECUTE 'DROP view if exists v_resultats_eval;' ;
                    RAISE NOTICE '%',requete;
                    EXECUTE requete;


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
            drop function if exists creer_v_resultats_eval;
        ');
    }
};
