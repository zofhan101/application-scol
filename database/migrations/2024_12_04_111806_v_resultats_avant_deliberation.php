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
            CREATE MATERIALIZED VIEW v_resultats_avant_deliberation AS
                SELECT
                    ra.id_resultat_avant_deliberation,
                    ra.id_au,
                    ra.id_parcours,
                    ra.id_niveau,
                    ra.id_etudiants,
                    ra.id_examen_par_au,
                    ra.id_session_examen,
                    ra.nom_session_examen,
                    ra.type_session_retenue,
                    ra.coefficient,
                    ra.id_unite_enseignement,
                    ra.id_element_constitutif,
                    ra.im,
                    ra.note_ue,
                    ra.note_ec,
                    ra.valide,
                    ra.statut,
                    ra.a_passe_examen,
                    ra.date_annulation_inscription,
                    ra.total,
                    ra.total_coefficient,
                    ra.moyenne_passage,
                    ra.moyenne,
                    ra.nombre_ue,
                    ra.nombre_ue_a_valider,
                    ra.nombre_ue_validees,
                    ra.nombre_note_eliminatoire,
                    ra.statut_au_suivante,
                    ra.id_niveau_suivant,

                    au.intitule AS intitule_au,

                    p.nom_parcours,


                    n.nom_niveau,
                    n.rang,
                    n.nom_niveau_long,
                    n.cycle,

                    e.nom AS nom_etudiant,
                    e.prenoms,
                    e.date_naissance,
                    e.lieu_naissance,

                    ue.nom_unite_enseignement,

                    ec.nom_element_constitutif,

                    ns.nom_niveau AS nom_niveau_suivant,
                    ns.rang AS rang_suivant,
                    ns.cycle as cycle_suivant
                FROM
                    resultats_avant_deliberation ra
                JOIN
                    au ON ra.id_au = au.id_au
                JOIN
                    parcours p ON ra.id_parcours = p.id_parcours
                JOIN
                    niveau n ON ra.id_niveau = n.id_niveau
                JOIN
                    etudiants e ON ra.id_etudiants = e.id_etudiants
                JOIN
                    unite_enseignement ue ON ra.id_unite_enseignement = ue.id_unite_enseignement
                JOIN
                    element_constitutif ec ON ra.id_element_constitutif = ec.id_element_constitutif
                LEFT JOIN
                    niveau ns ON ra.id_niveau_suivant = ns.id_niveau;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop MATERIALIZED VIEW if exists v_resultats_avant_deliberation;
        ');
    }
};
