<?php

namespace App\Rules\notes;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class IsNoteValide implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //récuperation de note max
        //comparaison de  value avec note max
        $note_max = DB::scalar('select note_max from note_max order by id_note_max desc limit 1');
        if($value  < 0)
            $fail('La note ne doit pas être négative');
        else if($value > $note_max)
            $fail('La note ne doit pas dépasser '.$note_max);
    }
}
