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
           create or replace view v_assemblage_eval_repe as
                select eval.id_au, eval.id_parcours, eval.id_niveau, eval.id_examen_par_au, eval.id_session_examen, eval.nom_session_examen,
                CASE
                    WHEN rep.note_ue >= eval.note_ue THEN rep.type_session
                    WHEN eval.note_ue > rep.note_ue THEN eval.type_session
                END as type_session_retenue,
                eval.date_annulation_inscription, eval.coefficient, eval.id_unite_enseignement, eval.id_element_constitutif, eval.im, eval.id_etudiants,
                GREATEST(rep.note_ue, eval.note_ue) as note_ue,
                CASE
                    WHEN rep.note_ue >= eval.note_ue THEN rep.note_ec
                    WHEN eval.note_ue > rep.note_ue THEN eval.note_ec
                END as note_ec,
                rep.statut, rep.a_passe_examen
                from v_note_ue_avec_ec_rep as rep
                join resultats_avant_repechage as eval on (rep.id_au = eval.id_au and rep.id_parcours = eval.id_parcours and rep.id_niveau = eval.id_niveau  and rep.id_unite_enseignement = eval.id_unite_enseignement and  rep.id_element_constitutif = eval.id_element_constitutif and rep.id_etudiants = eval.id_etudiants) or (rep.id_au = eval.id_au and rep.id_parcours = eval.id_parcours and rep.id_niveau = eval.id_niveau  and rep.id_unite_enseignement = eval.id_unite_enseignement and  rep.id_element_constitutif = eval.id_element_constitutif and rep.id_etudiants is null and eval.id_etudiants is null);

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_assemblage_eval_repe;
        ');
    }
};
