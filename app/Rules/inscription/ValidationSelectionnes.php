<?php

namespace App\Rules\inscription;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Rap2hpoutre\FastExcel\FastExcel;
use Exception;

class ValidationSelectionnes implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    protected static function check_num($datum){
        if($datum['Numero_bacc'] === "")
            throw new Exception("Numéro au baccalauréat absent");
    }


    protected static function check_nom($datum){
        if($datum['Nom'] === "")
            throw new Exception("Nom du candidat absent");
    }

    protected static function check($datum){
        $erreur=[];
        try {
            ValidationSelectionnes::check_num($datum);
        } catch (\Exception $th) {
            $erreur[] = $th->getMessage();
        }

        try {
            ValidationSelectionnes::check_nom($datum);
        } catch (\Exception $th) {
            $erreur[] = $th->getMessage();
        }

        if(!empty($erreur)){

            throw new Exception(implode(">>>",$erreur));
        }
    }

     public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $erreur=[];
        $data =  fastexcel()->import($value);
        //var_dump($data);
        //parcours de la variable data
        $datum;
        for($i=0; $i<count($data); $i++){
            $datum = $data[$i];
            try {
                ValidationSelectionnes::check($datum);
            } catch (\Exception $th) {
                $erreur[] = "Ligne ".strval($i+2).":>>>".$th->getMessage();
            }
        }

        if(!empty($erreur))
            $fail(implode("###",$erreur));

    }




}
