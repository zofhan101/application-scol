<?php

namespace App\Http\Controllers\notes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\notes\operation_sur_resultats;
use App\Models\AU\AU;


class NoteController extends Controller
{
    public function ouverture_verrouillage_saisie(){
        //recupération des examens et des opérations
        try {
            $operations = operation_sur_resultats::all();
            $examens = AU::get_liste_examens();
            return view('notes/ouverture_verrouillage',['operations'=>$operations, 'examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/ouverture_verrouillage',['error'=>$th->getMessage()]);
        }
    }
}
