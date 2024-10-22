<?php

namespace App\Http\Controllers\notes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\notes\operation_sur_resultats;
use App\Models\AU\AU;
use App\Models\notes\Operation_sur_examen;
use Illuminate\Support\Facades\Auth;



class NoteController extends Controller
{
    public function enregister_note(){
        $request->validate([
            'barcode' => ['required','string', 'exists:parcours,id_parcours'],
            'note' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
    }

    public function ouvrir_saisie_note(Request $request){
        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::ouvrir_saisie_note($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de saisie effectuée"], 200);
        } catch (\Throwable $th) {
            return response()->json(['message'=>$th], 500);
        }

    }

    public function get_operation_par_examen(Request $request){
        $id_examen_par_au = $request->input('id_examen_par_au');
        $operation_examen = Operation_sur_examen::get_operation_sur_examen($id_examen_par_au);
        return response()->json(['operation'=>$operation_examen], 200);
    }

    public function controle_saisie_note(){
        //recupération des examens et des opérations
        try {
            $examens = AU::get_liste_examens();
            return view('notes/controle_saisie_note',['examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/controle_saisie_note',['error'=>$th->getMessage()]);
        }
    }
}
