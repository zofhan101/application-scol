<?php

namespace App\Rules\notes;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use stdClass;

class IsBarcodeValide implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //le code barres doit au moins contenir un -
        if(str_contains($value, '-')){
            //spliter le code-barres
            //récupérer les deux partie
            $table = explode("-", $value);

            $id_ue_ec = $table[0];
            $num = $table[1];
            // chaque partie doit être un entier
            if(filter_var($id_ue_ec, FILTER_VALIDATE_INT) == true && filter_var($num, FILTER_VALIDATE_INT) == true){
                // la première valeur est une clé primaire dans la table ue_ec...
                $ue_ecs = DB::select('
                    select * from ue_ec_parcours_niveau_au where id_ue_ec = ?
                ', [$id_ue_ec]);

                if(empty($ue_ecs)){
                    $fail('Code-barres invalide: association UE EC inexistante');
                }
                else{

                    // la seconde valeur est comprise entre 1 et nbr inscrits + codes barres en plus

                    $nbr_inscrits = DB::scalar('
                        select nbr_inscrits from v_liste_ue_ec_avec_nbr_inscrits where id_ue_ec = ?
                    ', [$id_ue_ec]);

                    $en_plus  = DB::scalar('
                        select cte_codes_barres_en_plus from cte_codes_barres_en_plus order by id_cte_codes_barres_en_plus desc limit 1
                    ');

                    if($num < 1)
                        $fail('Code-barres invalide: numero inferieur à 1');
                    else if($num > $nbr_inscrits + $en_plus)
                        $fail('Code-barres invalide: numero surperieur à '.$nbr_inscrits + $en_plus);
                }
            }
            else{
                $fail('Code-barres invalide: valeurs non numériques utilisées');
            }
        }else{
            $fail('Codes-barres invalide: - absent');
        }
    }
}
