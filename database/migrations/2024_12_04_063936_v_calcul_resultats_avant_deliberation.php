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
            CREATE OR REPLACE VIEW v_calcul_resultats_avant_deliberation AS
                  SELECT
                      eval.id_au,
                      eval.id_parcours,
                      eval.id_niveau,
                      eval.id_etudiants,

                      eval.id_examen_par_au,
                      eval.id_session_examen,
                      eval.nom_session_examen,
                      eval.type_session_retenue,
                      eval.coefficient,
                      eval.id_unite_enseignement,
                      eval.id_element_constitutif,
                      eval.im,
                      eval.note_ue,
                      eval.note_ec,
                      val.valide,
                      eval.statut,
                      eval.a_passe_examen,

                      niv.date_annulation_inscription,
                      niv.total,
                      niv.total_coefficient,
                      niv.moyenne_passage,
                      niv.moyenne,
                      niv.nombre_ue,
                      niv.nombre_ue_a_valider,
                      niv.nombre_ue_validees,
                      niv.nombre_note_eliminatoire,
                      niv.statut_au_suivante,
                      niv.niveau_suivant

                  FROM
                      v_assemblage_eval_repe AS eval
                  JOIN
                      v_niveau_suivant_avant_deliberation AS niv
                  ON
                      eval.id_au = niv.id_au AND
                      eval.id_parcours = niv.id_parcours AND
                      eval.id_niveau = niv.id_niveau AND
                      (eval.id_etudiants = niv.id_etudiants OR (eval.id_etudiants IS NULL AND niv.id_etudiants IS NULL))

                 JOIN
                     v_validation_ue_rep AS val
                 ON
                     eval.id_au = val.id_au AND
                     eval.id_parcours = val.id_parcours AND
                     eval.id_niveau = val.id_niveau AND
                     (eval.id_etudiants = val.id_etudiants OR (val.id_etudiants IS NULL AND val.id_etudiants IS NULL)) AND
                     eval.id_unite_enseignement = val.id_unite_enseignement;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_calcul_resultats_avant_deliberation;
        ');
    }
};
