<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\AU\AU;
use Exception;

class AUService{
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
