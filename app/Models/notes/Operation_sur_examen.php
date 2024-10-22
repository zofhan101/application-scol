<?php

namespace App\Models\notes;

use Illuminate\Support\Facades\DB;

class Operation_sur_examen
{

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
