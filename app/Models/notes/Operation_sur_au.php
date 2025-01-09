<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;
use Exception;
use stdClass;

class Operation_sur_au
{

    //cloture d'un import
    public static function enregistrer_import($id_au, $id_parcours, $id_user){
        DB::insert('
            INSERT INTO operation_par_import_resultat_paces(date_import, id_user_date_import, id_au, id_parcours)
            VALUES(?,?,?,?)
        ', [date('Y-m-d'), $id_user, $id_au, $id_parcours]);
    }

    //controle d'existence d'un import
    public static function get_operation_par_import($id_au, $id_parcours){
        $res = DB::select('
            select * from operation_par_import_resultat_paces
            WHERE id_au =  ?
            AND id_parcours = ?
        ', [$id_au, $id_parcours]);

        return $res;
    }

    //export des résultats pour ENT
    public static function get_data_export_ent($id_au){
        $resultats = DB::select('
            SELECT
                idmention,
                idparcours,
                rang,
                noteFin,
                idNiveau,
                im
            FROM v_export_ent
            WHERE id_au = ?
        ', [$id_au]);

        if(empty($resultats))
            throw new Exception('Aucun résultat à exporter n\'a été trouvé pour cette A.U. sélectionnée');
        return $resultats;
    }


    //relevé de notes
    public static function get_resultats_by_im($im){
        $resultats_base = DB::select("
            SELECT
            DISTINCT ON(
                id_au,
                id_examen_par_au,
                im,
                id_ue
            )
                *,
                (SELECT  note_max from note_max order by id_note_max desc limit 1) AS note_max,
                (SELECT  note_max from note_max order by id_note_max desc limit 1) * total_coefficient as total_max,
                CASE
                    WHEN
                        statut_au_suivante = 'redoublant'
                        OR statut_au_suivante =  'triplant'
                        OR statut_au_suivante = 'passant' AND id_niveau_suivant = id_niveau_suivant
                        THEN 'AJOURNE'::VARCHAR
                    WHEN statut_au_suivante = 'exclu'
                        THEN 'exclu'::VARCHAR
                    WHEN
                        statut_au_suivante = 'passant'
                        AND (
                            id_niveau_suivant != id_niveau
                            OR id_niveau_suivant IS NULL
                        )
                        THEN 'ADMIS'::VARCHAR

                END AS decision

            FROM resultats_definitifs
            WHERE  im = ?
            ORDER BY
                id_au,
                id_examen_par_au,
                im,
                id_ue
        ", [$im]);

        //traitement des résultats
        if(empty($resultats_base))
            throw new Exception("Aucun résultat d'examen n'est encore disponible pour l'étudiant sélectionné");

        $res = [];
        $element = [];
        $id_au_prec = $resultats_base[0]->id_au;
        $id_au;
        foreach($resultats_base as $resultat){
            $id_au = $resultat->id_au;
            if($id_au != $id_au_prec){
                $res[] =  $element;
                $element = [];
            }
            $element[] = $resultat;

            $id_au_prec = $id_au;
        }
        $res[] = $element;
        return $res;

    }

    //liste des exclus
    public static function get_listes_exclus($id_au){
        //par parcours et par niveau
        $resultats_base = DB::select("
            SELECT
            DISTINCT ON(id_au, id_etudiants)
            DENSE_RANK() OVER(
                PARTITION BY id_parcours, id_niveau
                ORDER BY im asc
            ) as num,
                 intitule,
                 id_parcours,
                 nom_parcours,
                 id_niveau,
                 nom_niveau,
                 im,
                 nom_etudiant,
                 prenoms
            FROM resultats_definitifs
            WHERE statut_au_suivante = 'exclu'
            AND id_au = ?
            ORDER BY id_au, id_etudiants;
        ", [$id_au]);

        if(empty($resultats_base))
            throw new Exception('Aucun étudiant exclu pour cette A.U.');

        $ligne1 = $resultats_base[0];

        $parcours = [];
        $parcour = new stdClass();
        $parcour->id_parcours = $ligne1->id_parcours;
        $parcour->nom_parcours = $ligne1->nom_parcours;

        $id_parcours;
        $id_parcours_prec = $ligne1->id_parcours;

        $niveaux = [];
        $niveau = new stdClass();
        $niveau->id_niveau = $ligne1->id_niveau;
        $niveau->nom_niveau = $ligne1->nom_niveau;
        $niveau->intitule = $ligne1->intitule;
        $niveau->nom_parcours = $ligne1->nom_parcours;

        $id_niveau;
        $id_niveau_prec = $ligne1->id_niveau;

        $liste_etu = [];
        $etu;

        foreach($resultats_base as $resultat){
            $id_parcours = $resultat->id_parcours;
            $id_niveau = $resultat->id_niveau;

            if($id_parcours != $id_parcours_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $liste_etu  = [];

                $parcour->niveaux = $niveaux;
                $parcours[] = $parcour;
                $parcour = new stdClass();
                $parcour->id_parcours = $resultat->id_parcours;
                $parcour->nom_parcours = $resultat->nom_parcours;


            }
            else if($id_niveau != $id_niveau_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $niveau->intitule = $resultat->intitule;
                $niveau->nom_parcours = $resultat->nom_parcours;


                $liste_etu  = [];

            }

            $etu = new stdClass();
            $etu->N° = $resultat->num;
            $etu->IM = $resultat->im;
            $etu->nom = $resultat->nom_etudiant;
            $etu->prenoms = $resultat->prenoms;

            $liste_etu[] = $etu;

            $id_parcours_prec = $id_parcours;
            $id_niveau_prec = $id_niveau;
        }
        $niveau->liste_etu = $liste_etu;
        $niveaux[] = $niveau;
        $parcour->niveaux = $niveaux;
        $parcours[] = $parcour;

        return $parcours;



    }

    //listes des triplants
    public static function get_listes_triplants($id_au){
        //par parcours et par niveau
        $resultats_base = DB::select("
            SELECT
            DISTINCT ON(id_au, id_etudiants)
            DENSE_RANK() OVER(
                PARTITION BY id_parcours, id_niveau
                ORDER BY im asc
            ) as num,
                 intitule,
                 id_parcours,
                 nom_parcours,
                 id_niveau,
                 nom_niveau,
                 im,
                 nom_etudiant,
                 prenoms
            FROM resultats_definitifs
            WHERE statut_au_suivante = 'triplant'
            AND id_au = ?
            ORDER BY id_au, id_etudiants;
        ", [$id_au]);

        if(empty($resultats_base))
            throw new Exception('Aucun étudiant triplant pour cette A.U.');

        $ligne1 = $resultats_base[0];

        $parcours = [];
        $parcour = new stdClass();
        $parcour->id_parcours = $ligne1->id_parcours;
        $parcour->nom_parcours = $ligne1->nom_parcours;

        $id_parcours;
        $id_parcours_prec = $ligne1->id_parcours;

        $niveaux = [];
        $niveau = new stdClass();
        $niveau->id_niveau = $ligne1->id_niveau;
        $niveau->nom_niveau = $ligne1->nom_niveau;
        $niveau->intitule = $ligne1->intitule;
        $niveau->nom_parcours = $ligne1->nom_parcours;

        $id_niveau;
        $id_niveau_prec = $ligne1->id_niveau;

        $liste_etu = [];
        $etu;

        foreach($resultats_base as $resultat){
            $id_parcours = $resultat->id_parcours;
            $id_niveau = $resultat->id_niveau;

            if($id_parcours != $id_parcours_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $liste_etu  = [];

                $parcour->niveaux = $niveaux;
                $parcours[] = $parcour;
                $parcour = new stdClass();
                $parcour->id_parcours = $resultat->id_parcours;
                $parcour->nom_parcours = $resultat->nom_parcours;


            }
            else if($id_niveau != $id_niveau_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $niveau->intitule = $resultat->intitule;
                $niveau->nom_parcours = $resultat->nom_parcours;


                $liste_etu  = [];

            }

            $etu = new stdClass();
            $etu->N° = $resultat->num;
            $etu->IM = $resultat->im;
            $etu->nom = $resultat->nom_etudiant;
            $etu->prenoms = $resultat->prenoms;

            $liste_etu[] = $etu;

            $id_parcours_prec = $id_parcours;
            $id_niveau_prec = $id_niveau;
        }
        $niveau->liste_etu = $liste_etu;
        $niveaux[] = $niveau;
        $parcour->niveaux = $niveaux;
        $parcours[] = $parcour;

        return $parcours;



    }

    //listes des redoublants
    public static function get_listes_redoublants($id_au){
        //par parcours et par niveau
        $resultats_base = DB::select("
            SELECT
            DISTINCT ON(id_au, id_etudiants)
            DENSE_RANK() OVER(
                PARTITION BY id_parcours, id_niveau
                ORDER BY im asc
            ) as num,


                 intitule,
                 id_parcours,
                 nom_parcours,
                 id_niveau,
                 nom_niveau,
                 im,
                 nom_etudiant,
                 prenoms
            FROM resultats_definitifs
            WHERE (statut_au_suivante = 'redoublant'
            OR (statut_au_suivante =  'passant'
            AND date_annulation_inscription IS NOT NULL))
            AND id_au = ?

            ORDER BY id_au, id_etudiants;
        ", [$id_au]);

        if(empty($resultats_base))
            throw new Exception('Aucun étudiant redoublant pour cette A.U.');

        $ligne1 = $resultats_base[0];

        $parcours = [];
        $parcour = new stdClass();
        $parcour->id_parcours = $ligne1->id_parcours;
        $parcour->nom_parcours = $ligne1->nom_parcours;

        $id_parcours;
        $id_parcours_prec = $ligne1->id_parcours;

        $niveaux = [];
        $niveau = new stdClass();
        $niveau->id_niveau = $ligne1->id_niveau;
        $niveau->nom_niveau = $ligne1->nom_niveau;
        $niveau->intitule = $ligne1->intitule;
        $niveau->nom_parcours = $ligne1->nom_parcours;

        $id_niveau;
        $id_niveau_prec = $ligne1->id_niveau;

        $liste_etu = [];
        $etu;

        foreach($resultats_base as $resultat){
            $id_parcours = $resultat->id_parcours;
            $id_niveau = $resultat->id_niveau;

            if($id_parcours != $id_parcours_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $liste_etu  = [];

                $parcour->niveaux = $niveaux;
                $parcours[] = $parcour;
                $parcour = new stdClass();
                $parcour->id_parcours = $resultat->id_parcours;
                $parcour->nom_parcours = $resultat->nom_parcours;


            }
            else if($id_niveau != $id_niveau_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $niveau->intitule = $resultat->intitule;
                $niveau->nom_parcours = $resultat->nom_parcours;


                $liste_etu  = [];

            }

            $etu = new stdClass();
            $etu->N° = $resultat->num;
            $etu->IM = $resultat->im;
            $etu->nom = $resultat->nom_etudiant;
            $etu->prenoms = $resultat->prenoms;

            $liste_etu[] = $etu;

            $id_parcours_prec = $id_parcours;
            $id_niveau_prec = $id_niveau;
        }
        $niveau->liste_etu = $liste_etu;
        $niveaux[] = $niveau;
        $parcour->niveaux = $niveaux;
        $parcours[] = $parcour;

        return $parcours;



    }


    //préparation des résultats définitifs
    public static function preparer_resultats_definitifs($id_au, $id_user){
        DB::update('
            UPDATE operation_par_au
            SET
                date_resultats_definitifs = ?,
                id_user_date_resultats_definitifs = ?
            WHERE id_au = ?
        ', [ date('Y-m-d'), $id_user, $id_au ]);
    }

    //listes d'admission
    public static function get_listes_admission($id_au){
        //par parcours et par niveau
        $resultats_base = DB::select("
            SELECT
            DISTINCT ON(id_au, id_etudiants)
            DENSE_RANK() OVER(
                PARTITION BY id_parcours, id_niveau
                ORDER BY im asc
            ) as num,
                 intitule,
                 id_parcours,
                 nom_parcours,
                 id_niveau,
                 nom_niveau,
                 im,
                 nom_etudiant,
                 prenoms
            FROM resultats_definitifs
            WHERE statut_au_suivante = 'passant'
            AND id_au = ?
            AND date_annulation_inscription IS NULL
            ORDER BY id_au, id_etudiants;
        ", [$id_au]);

        if(empty($resultats_base))
            throw new Exception('Aucun étudiant admis pour cette A.U.');

        $ligne1 = $resultats_base[0];

        $parcours = [];
        $parcour = new stdClass();
        $parcour->id_parcours = $ligne1->id_parcours;
        $parcour->nom_parcours = $ligne1->nom_parcours;

        $id_parcours;
        $id_parcours_prec = $ligne1->id_parcours;

        $niveaux = [];
        $niveau = new stdClass();
        $niveau->id_niveau = $ligne1->id_niveau;
        $niveau->nom_niveau = $ligne1->nom_niveau;
        $niveau->intitule = $ligne1->intitule;
        $niveau->nom_parcours = $ligne1->nom_parcours;

        $id_niveau;
        $id_niveau_prec = $ligne1->id_niveau;

        $liste_etu = [];
        $etu;

        foreach($resultats_base as $resultat){
            $id_parcours = $resultat->id_parcours;
            $id_niveau = $resultat->id_niveau;

            if($id_parcours != $id_parcours_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $liste_etu  = [];

                $parcour->niveaux = $niveaux;
                $parcours[] = $parcour;
                $parcour = new stdClass();
                $parcour->id_parcours = $resultat->id_parcours;
                $parcour->nom_parcours = $resultat->nom_parcours;


            }
            else if($id_niveau != $id_niveau_prec){
                $niveau->liste_etu = $liste_etu;
                $niveaux[] = $niveau;
                $niveau = new stdClass();
                $niveau->id_niveau = $id_id_niveau;
                $niveau->nom_niveau = $resultat->nom_niveau;
                $niveau->intitule = $resultat->intitule;
                $niveau->nom_parcours = $resultat->nom_parcours;


                $liste_etu  = [];

            }

            $etu = new stdClass();
            $etu->N° = $resultat->num;
            $etu->IM = $resultat->im;
            $etu->nom = $resultat->nom_etudiant;
            $etu->prenoms = $resultat->prenoms;

            $liste_etu[] = $etu;

            $id_parcours_prec = $id_parcours;
            $id_niveau_prec = $id_niveau;
        }
        $niveau->liste_etu = $liste_etu;
        $niveaux[] = $niveau;
        $parcour->niveaux = $niveaux;
        $parcours[] = $parcour;

        return $parcours;



    }

    // consultation des résultats définitifs
    public static function get_resultats_definitifs($id_au, $id_parcours, $id_niveau){
        $resultats_base = DB::select('
            select DENSE_RANK() OVER (ORDER BY moyenne desc) AS rank , * from resultats_definitifs
            where id_au = ? and id_parcours = ? and id_niveau = ?
            order by rank asc, id_session_examen asc, id_ue asc , id_ec asc
        ', [$id_au, $id_parcours, $id_niveau]);

        if(empty($resultats_base))
            throw new Exception('Résultats indisponibles');

        $ligne1 = $resultats_base[0];

        $etudiants = [];
        $etu = new stdClass();
        $etu->rank = $ligne1->rank;
        $etu->id_etudiant = $ligne1->id_etudiants;
        $etu->im = $ligne1->im;
        $etu->nom = $ligne1->nom_etudiant;
        $etu->prenoms = $ligne1->prenoms;
        $etu->date_annulation = $ligne1->date_annulation_inscription;
        $etu->intitule = $ligne1->intitule;
        $etu->parcours = $ligne1->nom_parcours;
        $etu->niveau = $ligne1->nom_niveau;
        $etu->total = $ligne1->total;
        $etu->total_coefficient = $ligne1->total_coefficient;
        $etu->moyenne_passage = $ligne1->moyenne_passage;
        $etu->moyenne = $ligne1->moyenne;
        $etu->nombre_ue = $ligne1->nombre_ue;
        $etu->nombre_ue_validees = $ligne1->nombre_ue_validees;
        $etu->nombre_ue_a_valider = $ligne1->nombre_ue_a_valider;
        $etu->nombre_note_eliminatoire = $ligne1->nombre_note_elim;
        //$etu->decision = $ligne1->decision;
        $etu->statut = $ligne1->statut;
        $etu->a_passe_examen = $ligne1->a_passe_examen;
        $etu->statut_au_suivante = $ligne1->statut_au_suivante;
        $etu->id_niveau_suivant = $ligne1->id_niveau_suivant;
        $etu->nom_niveau_suivant = $ligne1->nom_niveau_suivant;




        $id_etudiant;
        $id_etudiant_prec = $ligne1->id_etudiants;

        $evals = [];
        $eval = new stdClass();
        $eval->id_examen_par_au = $ligne1->id_examen_par_au;
        $eval->nom_session_examen = $ligne1->nom_session_examen;
        $eval->type_session_retenue =  $ligne1->type_session_retenue;

        $id_examen_par_au;
        $id_examen_par_au_prec = $ligne1->id_examen_par_au;

        $ues = [];
        $ue = new stdClass();
        $ue->id_ue = $ligne1->id_ue;
        $ue->nom_ue = $ligne1->nom_unite_enseignement;
        $ue->note_ue = $ligne1->note_ue;
        $ue->coefficient = $ligne1->coef;
        $ue->validation = $ligne1->valide;
        $id_ue;
        $id_ue_prec = $ligne1->id_ue;

        $ecs = [];
        $ec;
        foreach($resultats_base as $resultat){
            $id_etudiant = $resultat->id_etudiants;
            $id_ue = $resultat->id_ue;
            $id_examen_par_au = $resultat->id_examen_par_au;


            if($id_etudiant != $id_etudiant_prec){
                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coef;
                $ue->validation = $resultat->valide;
                $ecs  = [];

                $eval->ues = $ues;
                $evals[] = $eval;

                $eval = new stdClass();
                $eval->id_examen_par_au = $resultat->id_examen_par_au;
                $eval->nom_session_examen = $resultat->nom_session_examen;
                $eval->type_session_retenue =  $resultat->type_session_retenue;
                $ues = [];

                $etu->evals = $evals;
                $etudiants[] = $etu;

                $etu = new stdClass();
                $etu->rank = $resultat->rank;
                $etu->id_etudiant = $resultat->id_etudiants;
                $etu->im = $resultat->im;
                $etu->nom = $resultat->nom_etudiant;
                $etu->prenoms = $resultat->prenoms;
                $etu->date_annulation = $resultat->date_annulation_inscription;
                $etu->intitule = $resultat->intitule;
                $etu->parcours = $resultat->nom_parcours;
                $etu->niveau = $resultat->nom_niveau;
                $etu->total = $resultat->total;
                $etu->total_coefficient = $resultat->total_coefficient;
                $etu->moyenne_passage = $resultat->moyenne_passage;
                $etu->moyenne = $resultat->moyenne;
                $etu->nombre_ue = $resultat->nombre_ue;
                $etu->nombre_ue_validees = $resultat->nombre_ue_validees;
                $etu->nombre_ue_a_valider = $resultat->nombre_ue_a_valider;
                $etu->nombre_note_eliminatoire = $resultat->nombre_note_elim;
                //$etu->decision = $resultat->decision;
                $etu->statut = $resultat->statut;
                $etu->a_passe_examen = $resultat->a_passe_examen;
                $etu->statut_au_suivante = $resultat->statut_au_suivante;
                $etu->id_niveau_suivant = $resultat->id_niveau_suivant;
                $etu->nom_niveau_suivant = $resultat->nom_niveau_suivant;
                $evals = [];
            }
            else if($id_examen_par_au != $id_examen_par_au_prec){
                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coef;
                $ue->validation = $resultat->valide;
                $ecs  = [];

                $eval->ues = $ues;
                $evals[] = $eval;

                $eval = new stdClass();
                $eval->id_examen_par_au = $resultat->id_examen_par_au;
                $eval->nom_session_examen = $resultat->nom_session_examen;
                $eval->type_session_retenue =  $resultat->type_session_retenue;
                $ues = [];
            }
            else if($id_ue != $id_ue_prec){


                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->coefficient = $resultat->coef;
                $ue->validation = $resultat->valide;
                $ecs  = [];
            }

            $ec = new stdClass();
            $ec->id_ec = $resultat->id_ec;
            //$ec->id_ue_ec = $resultat->id_ue_ec;
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


    //délibération

    public static function admettre_etudiant($id_au, $id_etudiant, $rang_niveau){
        //récupération du niveau suivant
        $niveaux = DB::select('
            SELECT *
            FROM niveau
            WHERE rang = ?::INTEGER + 1
        ', [$rang_niveau]);
        $niveau_suivant = empty($niveaux) == false ? $niveaux[0]:null;

        //mise à jour de la table
        DB::update('
            UPDATE resultats_definitifs
            SET
                a_ete_delibere = ?,
                statut_au_suivante = ?,
                id_niveau_suivant = ?,
                nom_niveau_suivant = ?,
                rang_suivant = ?,
                cycle_suivant = ?
            WHERE
                id_au = ?
            AND
                id_etudiants = ?
        ', [
            'true',
            'passant',
            $niveau_suivant->id_niveau ?? 'null',
            $niveau_suivant->nom_niveau ?? 'null',
            $niveau_suivant->rang ?? 'null',
            $niveau_suivant->cycle ?? 'null',
            $id_au,
            $id_etudiant
        ]);
    }

    public static function cloturer_deliberation($id_au, $id_parcours, $id_niveau, $id_user){
        $operations = Operation_sur_au::get_operation_par_deliberation($id_au, $id_parcours, $id_niveau);

        if(empty($operations)){
            throw new Exception('ERREUR: aucune opération d\'ouverture de délibération n\'a été trouvée pour les parcours et niveau selectionné');
        }else{
            $operation = $operations[0];
            if($operation->date_ouverture_deliberation != null){
                DB::update('
                    UPDATE operation_par_deliberation
                    SET
                        date_cloture_deliberation = ?,
                        id_user_date_cloture_deliberation = ?
                    WHERE id_au = ?
                    AND id_parcours = ?
                    AND id_niveau = ?
                ', [date('Y-m-d'), $id_user, $id_au, $id_parcours, $id_niveau]);
            }
            else{
                throw new Exception('ERREUR: délibération non encore ouverte pour les parcours et niveau sélectionnés');

            }
        }

    }

    public static function ouvrir_deliberation($id_au, $id_parcours, $id_niveau, $id_user){
        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            throw new Exception('ERREUR: ouverture de la délibération impossible car aucune opération de traitement des résultats d\'examen n\'a été trouvée');
        }
        else{
            $operation_par_au = $operations_par_au[0];
            if($operation_par_au->date_resultats_avant_deliberation != null){
                $operations = Operation_sur_au::get_operation_par_deliberation($id_au, $id_parcours, $id_niveau);
                if(empty($operation)){
                    DB::insert('
                        insert into operation_par_deliberation(id_au, id_parcours, id_niveau, date_ouverture_deliberation, id_user_date_ouverture_deliberation)
                        VALUES (?,?,?,?,?)
                    ', [$id_au, $id_parcours, $id_niveau, date('Y-m-d'), $id_user]);
                }else{
                    throw new Exception('ERREUR: la délibération a déjà été ouverte pour les parcours et niveau sélectionné');
                }
            }
            else{
                throw new Exception('ERREUR: ouverture de la délibération impossible car les résultats avant la délibération n\'ont par encore été générés');
            }
        }

    }

    public static function get_operation_par_deliberation($id_au, $id_parcours, $id_niveau){
        $operation = DB::select('
            select * from operation_par_deliberation
            WHERE id_au = ?
            AND id_parcours = ?
            AND id_niveau = ?
        ', [$id_au, $id_parcours, $id_niveau]);

        return $operation;
    }

    public static function get_data_deliberation($id_au, $id_parcours, $id_niveau){
        $resultats_base = DB::select('
            SELECT *
            FROM v_non_admis
            WHERE id_au = ?
            AND id_parcours = ?
            AND id_niveau = ?
            ORDER BY im ASC, id_session_examen ASC, id_ue ASC , id_ec ASC;
        ', [$id_au, $id_parcours, $id_niveau]);

        $historique_base = DB::select('
            SELECT *
            FROM v_historique_redoublement_triplement
            WHERE id_etudiants IN (
                SELECT id_etudiants
                FROM v_non_admis
                WHERE id_au = ?
                AND id_parcours = ?
                AND id_niveau = ?
            )
            AND id_au != ?
            ORDER BY im asc, id_au asc;
        ', [$id_au, $id_parcours, $id_niveau, $id_au]);
        // on considère que la délibération ne se fait que durant l'A.U. courante

        if(empty($resultats_base))
            throw new Exception('Résultats indisponibles');

        // traitement des résultats
            $ligne1 = $resultats_base[0];

            $etudiants = [];
            $etu = new stdClass();
            //$etu->rank = $ligne1->rank;
            $etu->id_etudiant = $ligne1->id_etudiants;
            $etu->im = $ligne1->im;
            $etu->nom = $ligne1->nom_etudiant;
            $etu->prenoms = $ligne1->prenoms;
            $etu->date_annulation = $ligne1->date_annulation_inscription;
            $etu->intitule = $ligne1->intitule;
            $etu->parcours = $ligne1->nom_parcours;
            $etu->id_parcours = $ligne1->id_parcours;
            $etu->niveau = $ligne1->nom_niveau;
            $etu->id_niveau =  $ligne1->id_niveau;
            $etu->rang_niveau =  $ligne1->rang;
            $etu->total = $ligne1->total;
            $etu->total_coefficient = $ligne1->total_coefficient;
            $etu->moyenne_passage = $ligne1->moyenne_passage;
            $etu->moyenne = $ligne1->moyenne;
            $etu->nombre_ue = $ligne1->nombre_ue;
            $etu->nombre_ue_validees = $ligne1->nombre_ue_validees;
            $etu->nombre_ue_a_valider = $ligne1->nombre_ue_a_valider;
            $etu->nombre_note_eliminatoire = $ligne1->nombre_note_elim;
            //$etu->decision = $ligne1->decision;
            $etu->statut = $ligne1->statut;
            $etu->a_passe_examen = $ligne1->a_passe_examen;
            $etu->statut_au_suivante = $ligne1->statut_au_suivante;
            $etu->id_niveau_suivant = $ligne1->id_niveau_suivant;
            $etu->nom_niveau_suivant = $ligne1->nom_niveau_suivant;




            $id_etudiant;
            $id_etudiant_prec = $ligne1->id_etudiants;

            $evals = [];
            $eval = new stdClass();
            $eval->id_examen_par_au = $ligne1->id_examen_par_au;
            $eval->nom_session_examen = $ligne1->nom_session_examen;
            $eval->type_session_retenue =  $ligne1->type_session_retenue;

            $id_examen_par_au;
            $id_examen_par_au_prec = $ligne1->id_examen_par_au;

            $ues = [];
            $ue = new stdClass();
            $ue->id_ue = $ligne1->id_ue;
            $ue->nom_ue = $ligne1->nom_unite_enseignement;
            $ue->note_ue = $ligne1->note_ue;
            $ue->coefficient = $ligne1->coef;
            $ue->validation = $ligne1->valide;
            $id_ue;
            $id_ue_prec = $ligne1->id_ue;

            $ecs = [];
            $ec;
            foreach($resultats_base as $resultat){
                $id_etudiant = $resultat->id_etudiants;
                $id_ue = $resultat->id_ue;
                $id_examen_par_au = $resultat->id_examen_par_au;


                if($id_etudiant != $id_etudiant_prec){
                    $ue->ecs = $ecs;
                    $ues[] = $ue;

                    $ue = new stdClass();
                    $ue->id_ue = $id_ue;
                    $ue->nom_ue = $resultat->nom_unite_enseignement;
                    $ue->note_ue = $resultat->note_ue;
                    $ue->coefficient = $resultat->coef;
                    $ue->validation = $resultat->valide;
                    $ecs  = [];

                    $eval->ues = $ues;
                    $evals[] = $eval;

                    $eval = new stdClass();
                    $eval->id_examen_par_au = $resultat->id_examen_par_au;
                    $eval->nom_session_examen = $resultat->nom_session_examen;
                    $eval->type_session_retenue =  $resultat->type_session_retenue;
                    $ues = [];

                    $etu->evals = $evals;
                    $etudiants[] = $etu;

                    $etu = new stdClass();
                    //$etu->rank = $resultat->rank;
                    $etu->id_etudiant = $resultat->id_etudiants;
                    $etu->im = $resultat->im;
                    $etu->nom = $resultat->nom_etudiant;
                    $etu->prenoms = $resultat->prenoms;
                    $etu->date_annulation = $resultat->date_annulation_inscription;
                    $etu->intitule = $resultat->intitule;
                    $etu->parcours = $resultat->nom_parcours;
                    $etu->id_parcours = $resultat->id_parcours;
                    $etu->niveau = $resultat->nom_niveau;
                    $etu->id_niveau =  $resultat->id_niveau;
                    $etu->rang_niveau =  $resultat->rang;
                    $etu->total = $resultat->total;
                    $etu->total_coefficient = $resultat->total_coefficient;
                    $etu->moyenne_passage = $resultat->moyenne_passage;
                    $etu->moyenne = $resultat->moyenne;
                    $etu->nombre_ue = $resultat->nombre_ue;
                    $etu->nombre_ue_validees = $resultat->nombre_ue_validees;
                    $etu->nombre_ue_a_valider = $resultat->nombre_ue_a_valider;
                    $etu->nombre_note_eliminatoire = $resultat->nombre_note_elim;
                    //$etu->decision = $resultat->decision;
                    $etu->statut = $resultat->statut;
                    $etu->a_passe_examen = $resultat->a_passe_examen;
                    $etu->statut_au_suivante = $resultat->statut_au_suivante;
                    $etu->id_niveau_suivant = $resultat->id_niveau_suivant;
                    $etu->nom_niveau_suivant = $resultat->nom_niveau_suivant;
                    $evals = [];
                }
                else if($id_examen_par_au != $id_examen_par_au_prec){
                    $ue->ecs = $ecs;
                    $ues[] = $ue;

                    $ue = new stdClass();
                    $ue->id_ue = $id_ue;
                    $ue->nom_ue = $resultat->nom_unite_enseignement;
                    $ue->note_ue = $resultat->note_ue;
                    $ue->coefficient = $resultat->coef;
                    $ue->validation = $resultat->valide;
                    $ecs  = [];

                    $eval->ues = $ues;
                    $evals[] = $eval;

                    $eval = new stdClass();
                    $eval->id_examen_par_au = $resultat->id_examen_par_au;
                    $eval->nom_session_examen = $resultat->nom_session_examen;
                    $eval->type_session_retenue =  $resultat->type_session_retenue;
                    $ues = [];
                }
                else if($id_ue != $id_ue_prec){


                    $ue->ecs = $ecs;
                    $ues[] = $ue;

                    $ue = new stdClass();
                    $ue->id_ue = $id_ue;
                    $ue->nom_ue = $resultat->nom_unite_enseignement;
                    $ue->note_ue = $resultat->note_ue;
                    $ue->coefficient = $resultat->coef;
                    $ue->validation = $resultat->valide;
                    $ecs  = [];
                }

                $ec = new stdClass();
                $ec->id_ec = $resultat->id_ec;
                //$ec->id_ue_ec = $resultat->id_ue_ec;
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



        // traitement de l'historique
            $table = [];
            $historique = [];
            if(empty($historique_base) == false){
                $id_etudiants;
                $id_etudiants_prec = $historique_base[0]->id_etudiants;

                foreach($historique_base as $ligne){
                    $id_etudiants = $ligne->id_etudiants;
                    if($id_etudiants != $id_etudiants_prec){
                        $historique[(string)$id_etudiants_prec] =  $table;
                        $table = [];
                    }
                    $table[] = $ligne;

                    $id_etudiants_prec = $id_etudiant;
                }

                $historique[(string)$id_etudiants_prec] =  $table;

            }

            return[$etudiants, $historique];

        }



        // résultats avant délibération

    public static function get_resultats_avant_deliberation($id_au, $id_parcours, $id_niveau){
        $resultats_base = DB::select('
            select DENSE_RANK() OVER (ORDER BY moyenne desc) AS rank , * from v_resultats_avant_deliberation
            where id_au = ? and id_parcours = ? and id_niveau = ?
            order by rank asc, id_session_examen asc, id_unite_enseignement asc , id_element_constitutif asc
        ', [$id_au, $id_parcours, $id_niveau]);

        if(empty($resultats_base))
            throw new Exception('Résultats indisponibles');

        $ligne1 = $resultats_base[0];

        $etudiants = [];
        $etu = new stdClass();
        $etu->rank = $ligne1->rank;
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
        $etu->moyenne_passage = $ligne1->moyenne_passage;
        $etu->moyenne = $ligne1->moyenne;
        $etu->nombre_ue = $ligne1->nombre_ue;
        $etu->nombre_ue_validees = $ligne1->nombre_ue_validees;
        $etu->nombre_ue_a_valider = $ligne1->nombre_ue_a_valider;
        $etu->nombre_note_eliminatoire = $ligne1->nombre_note_eliminatoire;
        //$etu->decision = $ligne1->decision;
        $etu->statut = $ligne1->statut;
        $etu->a_passe_examen = $ligne1->a_passe_examen;
        $etu->statut_au_suivante = $ligne1->statut_au_suivante;
        $etu->id_niveau_suivant = $ligne1->id_niveau_suivant;
        $etu->nom_niveau_suivant = $ligne1->nom_niveau_suivant;




        $id_etudiant;
        $id_etudiant_prec = $ligne1->id_etudiants;

        $evals = [];
        $eval = new stdClass();
        $eval->id_examen_par_au = $ligne1->id_examen_par_au;
        $eval->nom_session_examen = $ligne1->nom_session_examen;
        $eval->type_session_retenue =  $ligne1->type_session_retenue;

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
                $eval->type_session_retenue =  $resultat->type_session_retenue;
                $ues = [];

                $etu->evals = $evals;
                $etudiants[] = $etu;

                $etu = new stdClass();
                $etu->rank = $resultat->rank;
                $etu->id_etudiant = $resultat->id_etudiants;
                $etu->im = $resultat->im;
                $etu->nom = $resultat->nom;
                $etu->prenoms = $resultat->prenoms;
                $etu->date_annulation = $resultat->date_annulation_inscription;
                $etu->intitule = $resultat->intitule;
                $etu->parcours = $resultat->nom_parcours;
                $etu->niveau = $resultat->nom_niveau;
                $etu->total = $resultat->total;
                $etu->total_coefficient = $resultat->total_coefficient;
                $etu->moyenne_passage = $resultat->moyenne_passage;
                $etu->moyenne = $resultat->moyenne;
                $etu->nombre_ue = $resultat->nombre_ue;
                $etu->nombre_ue_validees = $resultat->nombre_ue_validees;
                $etu->nombre_ue_a_valider = $resultat->nombre_ue_a_valider;
                $etu->nombre_note_eliminatoire = $resultat->nombre_note_eliminatoire;
                //$etu->decision = $resultat->decision;
                $etu->statut = $resultat->statut;
                $etu->a_passe_examen = $resultat->a_passe_examen;
                $etu->statut_au_suivante = $resultat->statut_au_suivante;
                $etu->id_niveau_suivant = $resultat->id_niveau_suivant;
                $etu->nom_niveau_suivant = $resultat->nom_niveau_suivant;
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
                $eval->type_session_retenue =  $resultat->type_session_retenue;
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
            //$ec->id_ue_ec = $resultat->id_ue_ec;
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

    public static function verrouiller_resultats_avant_deliberation($id_au, $id_user){
        try {
                DB::update('
                update operation_par_au set date_resultats_avant_deliberation = ? , id_user_date_resultats_avant_deliberation = ?
                ', [date('Y-m-d'), $id_user]);

            } catch (\Exception $th) {
                throw $th;
            }
        }

    public static function generer_resultats_avant_deliberation($id_au){

        DB::transaction( function() use($id_au){
            try {
                DB::statement('
                INSERT INTO resultats_avant_deliberation (
                        id_au,
                        id_parcours,
                        id_niveau,
                        id_etudiants,
                        id_examen_par_au,
                        id_session_examen,
                        nom_session_examen,
                        type_session_retenue,
                        coefficient,
                        id_unite_enseignement,
                        id_element_constitutif,
                        im,
                        note_ue,
                        note_ec,
                        valide,
                        statut,
                        a_passe_examen,
                        date_annulation_inscription,
                        total,
                        total_coefficient,
                        moyenne_passage,
                        moyenne,
                        nombre_ue,
                        nombre_ue_a_valider,
                        nombre_ue_validees,
                        nombre_note_eliminatoire,
                        statut_au_suivante,
                        id_niveau_suivant
                    )
                    SELECT
                        id_au,
                        id_parcours,
                        id_niveau,
                        id_etudiants,
                        id_examen_par_au,
                        id_session_examen,
                        nom_session_examen,
                        type_session_retenue,
                        coefficient,
                        id_unite_enseignement,
                        id_element_constitutif,
                        im,
                        note_ue,
                        note_ec,
                        valide,
                        statut,
                        a_passe_examen,
                        date_annulation_inscription,
                        total,
                        total_coefficient,
                        moyenne_passage,
                        moyenne,
                        nombre_ue,
                        nombre_ue_a_valider,
                        nombre_ue_validees,
                        nombre_note_eliminatoire,
                        statut_au_suivante,
                        niveau_suivant
                    FROM
                        v_calcul_resultats_avant_deliberation
                    WHERE
                        id_au = ?;

            ', [$id_au]);

            DB::statement('
                refresh materialized view v_resultats_avant_deliberation;
            ');

            DB::statement('
                INSERT INTO resultats_definitifs (
                    id_au,
                    id_parcours,
                    id_niveau,
                    cycle,
                    nom_niveau,
                    rang,
                    nom_niveau_long,
                    id_examen_par_au,
                    id_session_examen,
                    nom_session_examen,
                    type_session_retenue,
                    id_etudiants,
                    im,
                    date_annulation_inscription,
                    statut,
                    id_ue,
                    coef,
                    id_ec,
                    note_ue,
                    note_ec,
                    valide,
                    total,
                    total_coefficient,
                    moyenne,
                    moyenne_passage,
                    nombre_ue,
                    nombre_ue_a_valider,
                    nombre_ue_validees,
                    nombre_note_elim,
                    statut_au_suivante,
                    id_niveau_suivant,
                    intitule,
                    nom_parcours,
                    nom_niveau_suivant,
                    rang_suivant,
                    cycle_suivant,
                    nom_etudiant,
                    prenoms,
                    date_naissance,
                    lieu_naissance,
                    nom_unite_enseignement,
                    nom_element_constitutif,
                    a_passe_examen
                )
                SELECT
                    id_au,
                    id_parcours,
                    id_niveau,
                    cycle,
                    nom_niveau,
                    rang,
                    nom_niveau_long,
                    id_examen_par_au,
                    id_session_examen,
                    nom_session_examen,
                    type_session_retenue,
                    id_etudiants,
                    im,
                    date_annulation_inscription,
                    statut,
                    id_unite_enseignement AS id_ue,
                    coefficient AS coef,
                    id_element_constitutif AS id_ec,
                    note_ue,
                    note_ec,
                    valide,
                    total,
                    total_coefficient,
                    moyenne,
                    moyenne_passage,
                    nombre_ue,
                    nombre_ue_a_valider,
                    nombre_ue_validees,
                    nombre_note_eliminatoire AS nombre_note_elim,
                    statut_au_suivante,
                    id_niveau_suivant,
                    intitule,
                    nom_parcours,
                    nom_niveau_suivant,
                    rang_suivant,
                    cycle_suivant,
                    nom,
                    prenoms,
                    date_naissance,
                    lieu_naissance,
                    nom_unite_enseignement,
                    nom_element_constitutif,
                    a_passe_examen
                FROM
                    v_resultats_avant_deliberation
                WHERE id_au = ?;
            ', [$id_au]);

            } catch (\Throwable $th) {
                throw $th;
            }
        });





    }
    //liste d'appel au repechage
    public static function get_liste_appel($id_au){
        $liste_globale = DB::select("
            select ROW_NUMBER () OVER (PARTITION BY id_parcours, id_niveau, id_unite_enseignement ORDER BY  im) as \"N°\", * from v_liste_repechage
            where id_au = ? and (valide = 'N' or valide = 'E') and im is not null
            order by  id_parcours asc, id_niveau asc, id_examen_par_au asc, id_unite_enseignement asc, \"N°\" asc
        ", [$id_au]);

        $ligne1 = $liste_globale[0];

        $res = [];

        $id_parcours_prec = $ligne1->id_parcours;
        $id_niveau_prec = $ligne1->id_niveau;
        $id_ue_prec = $ligne1->id_unite_enseignement;

        $liste_etu = [];
        $etu;

        $liste_courante = new stdClass();
        $liste_courante->intitule = $ligne1->intitule;
        $liste_courante->nom_parcours = $ligne1->nom_parcours;
        $liste_courante->nom_ue = $ligne1->nom_unite_enseignement;
        $liste_courante->nom_niveau = $ligne1->nom_niveau;


        $id_parcours;
        $id_niveau;
        $id_ue;

        foreach($liste_globale as $ligne_courante){
            $id_parcours = $ligne_courante->id_parcours;
            $id_niveau = $ligne_courante->id_niveau;
            $id_ue = $ligne_courante->id_unite_enseignement;

            if($id_parcours != $id_parcours_prec || $id_niveau != $id_niveau_prec || $id_ue != $id_ue_prec){
                $liste_courante->liste_etu = $liste_etu;
                $res[] = $liste_courante;

                $liste_courante = new stdClass();
                $liste_courante->intitule = $ligne_courante->intitule;
                $liste_courante->nom_parcours = $ligne_courante->nom_parcours;
                $liste_courante->nom_ue = $ligne_courante->nom_unite_enseignement;
                $liste_courante->nom_niveau = $ligne_courante->nom_niveau;

                $liste_etu = [];

            }

            $etu = new stdClass();
            $etu->N° = $ligne_courante->N°;
            $etu->im = $ligne_courante->im;
            $etu->nom = $ligne_courante->nom;
            $etu->prenoms = $ligne_courante->prenoms;
            $liste_etu[] = $etu;

            $id_parcours_prec = $id_parcours;
            $id_niveau_prec = $id_niveau;
            $id_ue_prec = $id_ue;


        }
        $liste_courante->liste_etu = $liste_etu;
        $res[] = $liste_courante;

        return $res;
    }


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
        $etu->nombre_ue_a_valider = $ligne1->nombre_ue_a_valider;
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
                $etu->nombre_ue_a_valider = $resultat->nombre_ue_a_valider;
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
            INSERT INTO  resultats_avant_repechage(id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire, decision)

            SELECT  id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire, decision

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
