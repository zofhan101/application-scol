<?php

namespace App\Http\Controllers\AU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AU\AU;
use App\Services\AUService;

class AUcontroller extends Controller
{

    protected $au_service;

    public function __construct(AUService $au_service){
        $this->au_service = $au_service;
    }

    public function cloture_au(Request $request){
        AU::clore_au($request->input('id_au'));
        return redirect(route('au_en_cours'));
    }

    public function auForm(){
        return view('AU.au_form');
    }

    public function au_en_cours(){
        try {
            $au_en_cours = AU::get_au_en_cours();
            return view('AU.au_en_cours',['au'=>$au_en_cours]);
        } catch (\Exception $th) {
            return view('AU.au_en_cours',['error'=>$th->getMessage()]);
        }

    }

    public function ouvrir_au(Request $request){
        $request->validate([
            'intitule' => ['required'],
        ]);
        $intitule = $request->input('intitule');
        try {
            $this->au_service->creer_au($intitule);
            return view('AU.au_form',[
                'success' => 'A.U. créée avec succès'
            ]);

        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return redirect()->back()->withErrors(['intitule' => $ex->getMessage()])->withInput();
        }

    }
}
