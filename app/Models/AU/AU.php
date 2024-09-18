<?php

namespace App\Models\AU;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class AU extends Model
{
    use HasFactory;

    protected $table = 'au';
    protected $primaryKey = 'id_au';

    protected $fillable = [
       'intitule'
    ];

    public static function clore_au($id_au){
        $au =  AU::find($id_au);
        if($au != null){
            $au->cloture = date('Y-m-d');
            $au->save();
        }

    }

    public static function get_au_en_cours(){
        $au_en_cours = AU::where('cloture','=', null)->get();

        if($au_en_cours->isEmpty()){
            throw new Exception('Désolé! Aucune A.U. n\'est en cours. Veuillez contacter votre administrateur pour en ouvrir une.');
        }
        else{
            return $au_en_cours[0];
        }
    }
}
