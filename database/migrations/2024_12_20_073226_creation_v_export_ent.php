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
            drop view if exists v_export_ent;
        ');
    }
};
