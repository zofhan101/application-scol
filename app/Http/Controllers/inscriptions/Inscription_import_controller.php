<?php

namespace App\Http\Controllers\inscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\mention_parcours\Parcours;
use App\Models\inscription\Selectionnes;
use App\Models\AU\AU;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Rules\inscription\ValidationSelectionnes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;

class Inscription_import_controller extends Controller
{
    public function import_selectionnes(Request $request){
        $request->validate([
            'parcours' => ['required'],
            'fichier_excel' =>['required','file','mimes:xlsx',new ValidationSelectionnes]
        ]);
        $id_parcours = $request->input('parcours');


        // récupération de l'au en cours
        $au = AU::where('cloture', null)->first();
        $id_au = $au->id_au;

        //la personne connectée
        $user = Auth::user();
        $id_user = $user->id;

        $sel = new Selectionnes();
        $doublons = [];
        
        // import et écriture dans la base de données
        fastexcel()->import($request->file('fichier_excel'), function($ligne) use (&$doublons,$id_user,$id_au, $id_parcours,&$sel){

            $response = rescue(
                function() use ($ligne,$id_au,$id_parcours,&$sel,$id_user){
                    $sel = new Selectionnes();
                    $sel->nom = $ligne['Nom'];
                    $sel->prenoms = $ligne['Prenom'];
                    $sel->num_bacc = $ligne['Numero_bacc'];
                    $sel->id_parcours = $id_parcours;
                    $sel->id_au = $id_au;
                    $sel->id_agent = $id_user;
                    echo $sel->save();

                    return true;
                },
                function(UniqueConstraintViolationException $ex) use(&$sel){
                    //var_dump($sel);
                    return $sel;
                },
                false
            );

            if($response instanceof Selectionnes){
                array_push($doublons,$response->replicate());
            }


        });
        //var_dump($doublons);
        if(empty($doublons)){
            return view('inscriptions/import_selectionnes',['success'=>'Import effectué avec succès','parcours'=>Parcours::all()]);
        }
        else{
            //var_dump($doublons);
            return view('inscriptions/import_selectionnes',['doublons'=>$doublons,'parcours'=>Parcours::all()]);
        }

    }

    public function modele_selectionnes(){

        $filePath = storage_path('fichiers/modele_import_selectionnes.xlsx');
        return response()->download($filePath);
    }

    public function import_selectionnes_page(){
        //récupération des mentions
        $parcours = Parcours::all();

        return view('inscriptions/import_selectionnes',['parcours'=>$parcours]);
    }
}
