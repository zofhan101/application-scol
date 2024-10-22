<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\AU\AU;
use Illuminate\Support\Facades\DB;
use Exception;

class AUService{

    public function save_exam($session, $id_au){
        $existence = DB::select('
            select * from examen_par_au where id_session_examen = ? and id_au = ?
        ',[$session, $id_au]
    );

        if(empty($existence)){
            DB::insert('
                insert into examen_par_au(id_session_examen, id_au) values(?,?)
            ',[$session, $id_au]);
        }

    }

    public function get_liste_examens(){
        $examens = DB::select('
            select * from session_examen;
        ');
        return $examens;

    }

    public function creer_au($intitule){
        //checker s'il n'y a pas une autre AU non cloturee
        $autres_au = AU::where('cloture', '=', null)->get();

        if(! $autres_au->isEmpty()){
            throw new Exception('A.U. non encore cloturées retrouvées');
        }
        else{
            $new_au =  new AU;
            $new_au->intitule = $intitule;
            $new_au->ouverture = date('Y-m-d');
            //var_dump($new_au);
            $new_au->save();
        }

    }
}
