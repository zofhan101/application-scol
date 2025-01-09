<?php

namespace App\Rules\inscription;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\AU\AU;
use App\Models\inscription\Selectionnes;

class AdmissionExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $num_bacc = session('new_etu')->num_bacc;
        $au = AU::get_au_en_cours();
        $id_au = $au->id_au;
        try {
            Selectionnes::admission_exists($id_au,$num_bacc, $value);
        } catch (\Exception $th) {
            $fail($th->getMessage());
            //throw $th;
        }
    }
}
