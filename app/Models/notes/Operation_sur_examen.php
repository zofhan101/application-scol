<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;

class Operation_sur_examen
{
    public static function get_operation_by_id_examen_par_au($id_examen_par_au){
        return DB::select('
        select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
    }

    public static function verrouiller_saisie_note($id_examen_par_au, $id_user){
        $operations = DB::select('
            select * from operation_par_examen where id_examen_par_au = ?
        ', [$id_examen_par_au]);
        $operation = $operations[0];
        if($operation->date_cloture_saisie_note == null){
            try {
                DB::update('
                    update operation_par_examen set date_cloture_saisie_note = ? , id_user_date_cloture_saisie_note = ? where id_examen_par_au = ?
                ', [date('Y-m-d'), $id_user, $id_examen_par_au]);

            } catch (\Throwable $th) {
                throw $th;
            }
        }
        else{
            throw new Exception("Cette saisie a déjà été cloturée ");
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
        try {
            DB::insert('
            insert into operation_par_examen(id_examen_par_au, date_ouverture_saisie_note, id_user_date_ouverture_saisie_note) values(?,?,?)
        ', [$id_examen_par_au, date('Y-m-d'), $id_user]);

        } catch (\Throwable $th) {
           throw $th;
        }
    }

    public static function get_operation_sur_examen($id_examen_par_au){
        $operations = DB::select('select * from operation_par_examen where id_examen_par_au = ?', [$id_examen_par_au]);

        if(empty($operations))
            return [];
        return $operations[0];
    }
}
