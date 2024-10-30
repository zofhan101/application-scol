<?php

namespace App\Rules\notes;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\AU\AU;

class IsMatriculeValide implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $au_courant = AU::get_au_en_cours();
        $inscriptions = DB::select('
            select id_etudiants, im, id_au, intitule  from v_inscrits where im = ? and id_au = ?
        ', [$value, $au_courant->id_au]);

        if(empty($inscriptions)){
            $fail('ERREUR: étudiant non inscrit à cette A.U.');
        }
    }
}
