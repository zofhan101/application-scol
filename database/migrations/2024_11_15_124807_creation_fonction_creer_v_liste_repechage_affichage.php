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
            create or replace function creer_v_liste_repechage_affichage(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint)
                RETURNS void AS
                $$
                DECLARE
                    nom_ue RECORD;
                    requete TEXT := 'CREATE MATERIALIZED VIEW v_liste_repechage_affichage AS SELECT ROW_NUMBER() OVER (ORDER BY NULL) AS N° ,im, nom, prenoms';
                    colonnes TEXT := '';
                BEGIN
                    FOR nom_ue IN
                        select distinct nom_unite_enseignement
                        from v_liste_repechage as v
                        where v.id_au = a_id_au
                            and v.id_parcours = a_id_parcours
                            and v.id_niveau = a_id_niveau
                    LOOP
                        colonnes := colonnes ||
                            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS \"Résultats ' || nom_ue.nom_unite_enseignement || '\"';
                    END LOOP;

                    requete := requete || colonnes || ' FROM  v_liste_repechage WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau ||  ' GROUP BY im, nom, prenoms;' ;

                    EXECUTE 'DROP MATERIALIZED VIEW  if exists v_liste_repechage_affichage;' ;
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
            drop function if exists creer_v_liste_repechage_affichage;
        ');
    }
};
