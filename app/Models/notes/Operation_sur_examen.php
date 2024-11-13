<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;
use Exception;
use stdClass;

class Operation_sur_examen
{
    //calcul de résultat

    public static function get_resultats_eval_back($id_au, $id_parcours, $id_niveau, $id_examen_par_au){
        $resultats_base = DB::select('
            select * from v_note_eval_complet
            where id_au = ? and id_parcours = ? and id_niveau = ? and id_examen_par_au = ?
            order by im asc, id_unite_enseignement asc , id_element_constitutif asc
        ', [$id_au, $id_parcours, $id_niveau, $id_examen_par_au]);

        $ligne1 = $resultats_base[0];

        $etudiants = [];
        $etu = new stdClass();
        $etu->id_etudiant = $ligne1->id_etudiants;
        $etu->im = $ligne1->im;
        $etu->nom = $ligne1->nom;
        $etu->prenoms = $ligne1->prenoms;
        $etu->date_annulation = $ligne1->date_annulation_inscription;
        $id_etudiant;
        $id_etudiant_prec = $ligne1->id_etudiants;

        $ues = [];
        $ue = new stdClass();
        $ue->id_ue = $ligne1->id_unite_enseignement;
        $ue->nom_ue = $ligne1->nom_unite_enseignement;
        $ue->note_ue = $ligne1->note_ue;
        $ue->validation = $ligne1->valide;
        $id_ue;
        $id_ue_prec = $ligne1->id_unite_enseignement;
        $ecs = [];
        $ec;
        foreach($resultats_base as $resultat){
            $id_etudiant = $resultat->id_etudiants;
            $id_ue = $resultat->id_unite_enseignement;



            if($id_etudiant != $id_etudiant_prec){

                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
                $ue->validation = $resultat->valide;
                $ecs  = [];

                $etu->ues = $ues;
                $etudiants[] = $etu;

                $etu = new stdClass();
                $etu->id_etudiant = $resultat->id_etudiants;
                $etu->im = $resultat->im;
                $etu->nom = $resultat->nom;
                $etu->prenoms = $resultat->prenoms;
                $etu->date_annulation = $resultat->date_annulation_inscription;
                $ues = [];
            }
            else if($id_ue != $id_ue_prec){
                $ue->ecs = $ecs;
                $ues[] = $ue;

                $ue = new stdClass();
                $ue->id_ue = $id_ue;
                $ue->nom_ue = $resultat->nom_unite_enseignement;
                $ue->note_ue = $resultat->note_ue;
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
        }

        $ue->ecs = $ecs;
        $ues[] = $ue;
        $etu->ues = $ues;
        $etudiants[] = $etu;

        return $etudiants;
    }

    public static function get_resultats_eval($id_au, $id_parcours, $id_niveau, $id_examen_par_au){
        DB::statement('
            select creer_v_resultats_eval(?, ?, ?, ?)
        ',[$id_au, $id_parcours, $id_niveau, $id_examen_par_au]);


        $resultats_eval =  DB::select('select * from v_resultats_eval');
        return $resultats_eval;

    }

    public static function verrouiller_resultats($id_examen_par_au, $id_user){
        DB::update('update operation_par_examen set date_resultats = ? , id_user_date_resultats = ?
        ',[date('Y-m-d'), $id_user]);
    }

    public static function remplir_note_eval($id_examen_par_au){
        DB::statement('
            insert into note_eval(id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide)
            select id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation, coefficient, id_unite_enseignement, id_ue_ec, id_element_constitutif, im, id_etudiants, note_ec, note_ue, valide
            from v_note_validation_ue_avec_ec
            where id_examen_par_au = ?;
        ',[$id_examen_par_au]);
    }

    public static function get_session_examen($id_examen_par_au){
        $session = DB::select('
            select se.id_session_examen, nom_session_examen, type_session, id_examen_par_au, id_au
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
        ');
        return $session[0];
    }
        //anomalies dans les saisies
    public static function get_anomalies_saisie($id_examen_par_au){
        // récupérer  le anomalies
        $anomalies =  DB::select('
            select id_ue_ec_matricule, numero_matricule, matricule, id_ue_ec_note, numero_note, note, id_examen_par_au
            from v_correspondance_note_matricule
            where id_examen_par_au = ? and (matricule is  null or note is null);
        ', [$id_examen_par_au]);

        $matricule_abs =[];
        $note_abs = [];
        foreach($anomalies as $anomalie){
            if($anomalie->matricule == null)
                $matricule_abs[] = $anomalie;
            else if($anomalie->note == null)
                $note_abs[] = $anomalie;
        }

        $res=[$matricule_abs, $note_abs];
        return $res;
    }

    //statistiques de saisie et de vérification de notes et matricules
    public static function get_nbr_verifies_entete($id_ue_ec){
        $nbr_verifies = DB::scalar('
            select count(numero)
            from barcode_matricule
            where id_ue_ec = ? and verifie = true
        ', [$id_ue_ec]);
        return $nbr_verifies;
    }

    public static function get_nbr_verifies_note($id_ue_ec){
        $nbr_verifies = DB::scalar('
            select count(numero)
            from barcode_notes
            where id_ue_ec = ? and verifie = true
        ', [$id_ue_ec]);
        return $nbr_verifies;
    }

    public static function get_nbr_enregistres_entete($id_ue_ec){
        $nbr_enregistres = DB::scalar('
            select count(matricule)
            from barcode_matricule
            where id_ue_ec = ?
        ', [$id_ue_ec]);
        return $nbr_enregistres;
    }

    public static function get_nbr_enregistres_note($id_ue_ec){
        $nbr_enregistres = DB::scalar('
            select count(numero)
            from barcode_note
            where id_ue_ec = ?
        ', [$id_ue_ec]);
        return $nbr_enregistres;
    }

    //nombre d'inscrits par rapport à un EC (qui est déterminé par une AU, parcours, nivau et UE)
    public static function get_nbr_inscrits($id_ue_ec){
        $inscrits = DB::scalar('
            select nbr_inscrits
            from v_liste_ue_ec_avec_nbr_inscrits
            where id_ue_ec = ?
        ', [$id_ue_ec]);
        return $inscrits;
    }

    //vérification des en-têtes
    public static function modifier_matricule($barcode, $matricule){
        $values = explode("-", $barcode);
        if(count($values) != 2)
            throw new Exception('ERREUR à la modification d\'un matricule: code-barres invalide');

        $lignes = DB::select('
            select * from barcode_matricule where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);
            if(empty($lignes)){
                throw new Exception("ERREUR: Aucun matricule n'a encore été enregistré pour ce code-barres.");
            }

            try {
                DB::update('
                update barcode_matricule set matricule = ? where id_ue_ec = ? and numero = ?
            ', [$matricule, $values[0], $values [1]]);
            } catch (\Exception $th) {
                throw $th;
            }

    }

    public static function get_matricule($barcode){
        $values = explode("-", $barcode);
        if(count($values) != 2)
            throw new Exception('ERREUR à la récupération d\'un matricule: code-barres invalide');
        try {
            $matricule = DB::scalar('
            select matricule from barcode_matricule where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);
            if($matricule == null ){
                throw new Exception("ERREUR: Aucun matricule n'a encore été enregistré pour ce code-barres");
            }

            DB::update('
                update barcode_matricule set verifie = TRUE where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);

            return $matricule;
        } catch (Exception $th) {
            throw $th;
        }
    }

    public static function verrouiller_verification_entete($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
        if(empty($operations))
            throw new \Exception('ERREUR: les operations d\'ouverture et de verrouillage par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_verification_en_tete != null && $operation->date_cloture_verification_en_tete == null){
                try {
                    DB::update('
                        update operation_par_examen set date_cloture_verification_en_tete = ?, id_user_date_cloture_verification_en_tete = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);
                } catch (\Exception $th) {
                    throw $th;
                }
            }
            else if($operation->date_ouverture_verification_en_tete == null){
                throw new \Exception('ERREUR: la vérification des en-têtes n\'a pas encore été cloturée pour cet examen');
            }
            else if($operation->date_cloture_verification_en_tete != null){
                throw new \Exception('ERREUR: la vérification des en-têtes a déjà été cloturée pour cet examen');
            }
        }
    }

    public static function ouvrir_verification_entete($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
        if(empty($operations))
            throw new \Exception('ERREUR: les operations d\'ouverture et de verrouillage par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_cloture_saisie_en_tete != null && $operation->date_ouverture_verification_en_tete == null){
                try {
                    DB::update('
                        update operation_par_examen set date_ouverture_verification_en_tete = ?, id_user_date_ouverture_verification_en_tete = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);
                } catch (\Exception $th) {
                    throw $th;
                }
            }
            else if($operation->date_cloture_saisie_note == null){
                throw new \Exception('ERREUR: la saisie des en-tetes n\'a pas encore été cloturée pour cet examen');
            }
            else if($operation->date_ouverture_verification_note != null){
                throw new \Exception('ERREUR: la vérification des en-têtes a déjà été ouverte pour cet examen');
            }
        }
    }

    //saisie des entetes
    public static function enregistrer_entete($barcode, $matricule){
        $values = explode("-", $barcode);

        DB::insert('
            insert into barcode_matricule(id_ue_ec, numero, matricule) values(?,?,?)
        ', [$values[0], $values[1], $matricule]);
    }

    public static function verrouiller_saisie_entete($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);


        if(empty($operations))
            throw new \Exception('ERREUR: les operations d\'ouverture et de verrouillage de la saisie des entetes par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_saisie_en_tete != null && $operation->date_cloture_saisie_en_tete == null){
                try {
                    DB::update('
                        update operation_par_examen set date_cloture_saisie_en_tete = ? , id_user_date_cloture_saisie_en_tete = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);

                } catch (\Throwable $th) {
                    throw $th;
                }
            }
            else if($operation->date_ouverture_saisie_en_tete == null){
                throw new Exception("ERREUR: la saisie des en-tetes n'a pas encore été ouverte pour cet examen");
            }
            else if($operation->date_cloture_saisie_note != null){
                throw new Exception("ERREUR: la saisie des en-têtes a déjà été cloturée pour cet examen");
            }

        }
    }

    public static function ouvrir_saisie_entete($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au] );
        if(empty($operations) == true ){
            try {
                DB::insert('
                    insert into operation_par_examen(id_examen_par_au, date_ouverture_saisie_en_tete, id_user_date_ouverture_saisie_en_tete) values(?,?,?)
            ', [$id_examen_par_au, date('Y-m-d'), $id_user]);

            } catch (\Exception $th) {
               throw $th;
            }
        }
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_saisie_en_tete != null)
                throw new Exception('ERREUR: la saisie des en-têtes pour cette évaluation a déjà été ouverte');
            else{
                try {
                    DB::update('
                        update operation_par_examen set date_ouverture_saisie_en_tete = ?, id_user_date_ouverture_saisie_en_tete = ?
                ', [date('Y-m-d'), $id_user]);

                } catch (\Exception $th) {
                   throw $th;
                }
            }

        }
    }

    //vérification des notes
    public static function modifier_note($barcode, $note){
        $values = explode("-", $barcode);
        if(count($values) != 2)
            throw new Exception('Erreur à la modification d\'une note: code-barres invalide');

        $lignes = DB::select('
            select  * from barcode_note where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);
            if(empty($lignes)){
                throw new Exception("Erreur: Aucune note n'a encore été enregistrée pour ce code-barres.");
            }

            try {
                DB::update('
                update barcode_note set note = ? where id_ue_ec = ? and numero = ?
            ', [$note, $values[0], $values [1]]);
            } catch (\Exception $th) {
                throw $th;
            }

    }

    public static function get_note($barcode){
        $values = explode("-", $barcode);
        if(count($values) != 2)
            throw new Exception('Erreur à la récupération d\'une note: code-barres invalide');
        try {
            $note = DB::scalar('
            select  note from barcode_note where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);
            if($note == null ){
                throw new Exception("Erreur: Aucune note n'a encore été enregistrée pour ce code-barres");
            }

            DB::update('
                update barcode_note set verifie = TRUE where id_ue_ec = ? and numero = ?
            ', [$values[0], $values [1]]);

            return $note;
        } catch (Exception $th) {
            throw $th;
        }



    }

    public static function verrouiller_verification_note($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
        if(empty($operations))
            throw new \Exception('Erreur: les operations d\'ouverture et de verrouillage par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_verification_note != null && $operation->date_cloture_verification_note == null){
                try {
                    DB::update('
                        update operation_par_examen set date_cloture_verification_note = ?, id_user_date_cloture_verification_note = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);
                } catch (\Exception $th) {
                    throw $th;
                }
            }
            else if($operation->date_ouverture_verification_note == null){
                throw new \Exception('Erreur: la vérification des notes n\'a pas encore été cloturée pour cet examen');
            }
            else if($operation->date_cloture_verification_note != null){
                throw new \Exception('Erreur: la vérification des notes a déjà été cloturée pour cet examen');
            }
        }
    }

    public static function ouvrir_verification_note($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
        if(empty($operations))
            throw new \Exception('Erreur: les operations d\'ouverture et de verrouillage par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_cloture_saisie_note != null && $operation->date_ouverture_verification_note == null){
                try {
                    DB::update('
                        update operation_par_examen set date_ouverture_verification_note = ?, id_user_date_ouverture_verification_note = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);
                } catch (\Exception $th) {
                    throw $th;
                }
            }
            else if($operation->date_cloture_saisie_note == null){
                throw new \Exception('Erreur: la saisie des notes n\'a pas encore été cloturée pour cet examen');
            }
            else if($operation->date_ouverture_verification_note != null){
                throw new \Exception('Erreur: la vérification des notes a déjà été ouverte pour cet examen');
            }
        }
    }

    public static function get_operation_by_id_examen_par_au($id_examen_par_au){
        return DB::select('
        select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
    }

    //saisie des notes
    public static function verrouiller_saisie_note($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);


        if(empty($operations))
            throw new \Exception('Erreur: les operations d\'ouverture et de verrouillage par rappport à cet examen sont absents');
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_saisie_note != null && $operation->date_cloture_saisie_note == null){
                try {
                    DB::update('
                        update operation_par_examen set date_cloture_saisie_note = ? , id_user_date_cloture_saisie_note = ? where id_examen_par_au = ?
                    ', [date('Y-m-d'), $id_user, $id_examen_par_au]);

                } catch (\Throwable $th) {
                    throw $th;
                }
            }
            else if($operation->date_ouverture_saisie_note == null){
                throw new Exception("Erreur: la saisie des notes n'a pas encore été ouverte pour cet examen");
            }
            else if($operation->date_cloture_saisie_note != null){
                throw new Exception("Erreur: la saisie des notes a déjà été cloturée pour cet examen");
            }

        }
    }

    public static function enregistrer_note($barcode, $note){
        $values = explode("-", $barcode);

        DB::insert('
            insert into barcode_note(id_ue_ec, numero, note) values(?,?,?)
        ', [$values[0], $values[1], $note]);
    }

    public static function get_operations_par_examen($id_au){
        $operations = DB::select('
            select * from v_operation_par_examen_par_au where id_au = ?
        ', [$id_au]);

        return $operations;
    }

    public static function ouvrir_saisie_note($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au] );
        if(empty($operations) == false ){
            if($operations[0]->date_ouverture_saisie_note == null){
                DB::update('
                    update operation_par_examen set date_ouverture_saisie_note = ?, id_user_date_ouverture_saisie_note = ? where id_examen_par_au = ?
                ', [date('Y-m-d'), $id_user, $id_examen_par_au]);
            }
            else {
                throw new Exception('ERREUR: saisie des notes déjà ouverte pour l\'examen choisi');
            }
        }
        else{
            try {
                DB::insert('
                insert into operation_par_examen(id_examen_par_au, date_ouverture_saisie_note, id_user_date_ouverture_saisie_note) values(?,?,?)
            ', [$id_examen_par_au, date('Y-m-d'), $id_user]);

            } catch (\Exception $th) {
               throw $th;
            }
        }

    }

    public static function get_operation_sur_examen($id_examen_par_au){
        $operations = DB::select('select * from operation_par_examen where id_examen_par_au = ?', [$id_examen_par_au]);

        if(empty($operations))
            return [];
        return $operations[0];
    }
}
