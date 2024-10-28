<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;
use Exception;

class Operation_sur_examen
{
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

    public static function get_saisies_ouvertes($id_au){
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
            if($operations[0]->date_ouverture_saisie_note != null)
            throw new Exception('Erreur: la saisie des notes a déjà été ouverte pour cet  examen');
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
