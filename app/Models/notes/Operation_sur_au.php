<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;
use Exception;
use stdClass;

class Operation_sur_au
{
    //liste de repechage
    public static function get_liste_repechage($id_au, $id_parcours, $id_niveau){
        DB::statement('
            select creer_v_liste_repechage_affichage(?, ?, ?);
        ',[$id_au, $id_parcours, $id_niveau]);


        $liste_repechage =  DB::select('select *  from v_liste_repechage_affichage');
        return $liste_repechage;
    }

    //résultats de l'AU avant repechages

    public static function get_resultats_avant_repechage($id_au, $id_parcours, $id_niveau){
        $resultats_base = DB::select('
            select DENSE_RANK() OVER (ORDER BY moyenne desc) AS rang , * from v_resultats_avant_repechage_complet
            where id_au = ? and id_parcours = ? and id_niveau = ?
            order by rang asc, id_session_examen asc, id_unite_enseignement asc , id_element_constitutif asc
        ', [$id_au, $id_parcours, $id_niveau]);

        if(empty($resultats_base))
            throw new Exception('Résultats indisponibles');

        $ligne1 = $resultats_base[0];

        $etudiants = [];
        $etu = new stdClass();
        $etu->rang = $ligne1->rang;
        $etu->id_etudiant = $ligne1->id_etudiants;
        $etu->im = $ligne1->im;
        $etu->nom = $ligne1->nom;
        $etu->prenoms = $ligne1->prenoms;
        $etu->date_annulation = $ligne1->date_annulation_inscription;
        $etu->intitule = $ligne1->intitule;
        $etu->parcours = $ligne1->nom_parcours;
        $etu->niveau = $ligne1->nom_niveau;
        $etu->total = $ligne1->total;
        $etu->total_coefficient = $ligne1->total_coefficient;
        $etu->moyenne = $ligne1->moyenne;
        $etu->nombre_ue = $ligne1->nombre_ue;
        $etu->nombre_ue_validees = $ligne1->nombre_ue_validees;
        $etu->pourcentage_validation = $ligne1->pourcentage_validation;
        $etu->nombre_note_eliminatoire = $ligne1->nombre_note_eliminatoire;
        $etu->decision = $ligne1->decision;

        $id_etudiant;
        $id_etudiant_prec = $ligne1->id_etudiants;

        $evals = [];
        $eval = new stdClass();
        $eval->id_examen_par_au = $ligne1->id_examen_par_au;
        $eval->nom_session_examen = $ligne1->nom_session_examen;

        $id_examen_par_au;
        $id_examen_par_au_prec = $ligne1->id_examen_par_au;

        $ues = [];
        $ue = new stdClass();
        $ue->id_ue = $ligne1->id_unite_enseignement;
        $ue->nom_ue = $ligne1->nom_unite_enseignement;
        $ue->note_ue = $ligne1->note_ue;
        $ue->coefficient = $ligne1->coefficient;
        $ue->validation = $ligne1->valide;
        $id_ue;
        $id_ue_prec = $ligne1->id_unite_enseignement;

        $ecs = [];
        $ec;
        foreach($resultats_base as $resultat){
            $id_etudiant = $resultat->id_etudiants;
            $id_ue = $resultat->id_unite_enseignement;
            $id_examen_par_au = $resultat->id_examen_par_au;


            if($id_etudiant != $id_etudiant_prec){
                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coefficient;
                $ue->validation = $resultat->valide;
                $ecs  = [];

                $eval->ues = $ues;
                $evals[] = $eval;

                $eval = new stdClass();
                $eval->id_examen_par_au = $resultat->id_examen_par_au;
                $eval->nom_session_examen = $resultat->nom_session_examen;
                $ues = [];

                $etu->evals = $evals;
                $etudiants[] = $etu;

                $etu = new stdClass();
                $etu->rang = $resultat->rang;
                $etu->id_etudiant = $resultat->id_etudiants;
                $etu->im = $resultat->im;
                $etu->nom = $resultat->nom;
                $etu->prenoms = $resultat->prenoms;
                $etu->date_annulation = $resultat->date_annulation_inscription;
                $etu->intitule = $ligne1->intitule;
                $etu->parcours = $resultat->nom_parcours;
                $etu->niveau = $resultat->id_niveau;
                $etu->total = $resultat->total;
                $etu->total_coefficient = $resultat->total_coefficient;
                $etu->moyenne = $resultat->moyenne;
                $etu->nombre_ue = $resultat->nombre_ue;
                $etu->nombre_ue_validees = $resultat->nombre_ue_validees;
                $etu->pourcentage_validation = $resultat->pourcentage_validation;
                $etu->nombre_note_eliminatoire = $resultat->nombre_note_eliminatoire;
                $etu->decision = $resultat->decision;
                $evals = [];
            }
            else if($id_examen_par_au != $id_examen_par_au_prec){
                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coefficient;
                $ue->validation = $resultat->valide;
                $ecs  = [];

                $eval->ues = $ues;
                $evals[] = $eval;

                $eval = new stdClass();
                $eval->id_examen_par_au = $resultat->id_examen_par_au;
                $eval->nom_session_examen = $resultat->nom_session_examen;
                $ues = [];
            }
            else if($id_ue != $id_ue_prec){


                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coefficient;
                $ue->validation = $resultat->valide;
                $ecs  = [];
            }

            $ec = new stdClass();
            $ec->id_ec = $resultat->id_element_constitutif;
            $ec->id_ue_ec = $resultat->id_ue_ec;
            $ec->nom_ec = $resultat->nom_element_constitutif;
            $ec->note_ec = $resultat->note_ec;
            $ecs[] = $ec;

            $id_etudiant_prec = $id_etudiant;
            $id_ue_prec = $id_ue;
            $id_examen_par_au_prec = $id_examen_par_au;
        }

        $ue->ecs = $ecs;
        $ues[] = $ue;
        $eval->ues = $ues;
        $evals[] = $eval;
        $etu->evals = $evals;
        $etudiants[] = $etu;

        return $etudiants;


    }

    public static function verrouiller_resultats($id_au, $id_user){
        DB::statement('
            INSERT INTO operation_par_au (id_au, date_resultats_avant_repechage, id_user_date_resultats_avant_repechage)
            VALUES(?,?,?)
        ', [$id_au, date('Y-m-d'), $id_user]);
    }

    public static function generer_resultats_au($id_au){
        // calculer la vue des resultats et insérer le résultat filtré dans la table des résultats
        DB::statement('
            INSERT INTO  resultats_avant_repechage(id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire, decision)

            SELECT  id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire, decision

            FROM v_resultats_avec_notes
            WHERE id_au = ?;
        ', [$id_au]);

        DB::statement('
            refresh materialized view v_resultats_avant_repechage_complet;
        ');

        DB::statement('
            refresh materialized view v_liste_repechage;
        ');
    }


    public static function get_operation_by_id_au($id_au){
        $operations = DB::select('
            select * from operation_par_au where id_au = ?
        ', [$id_au]);
        return $operations;
    }

}
