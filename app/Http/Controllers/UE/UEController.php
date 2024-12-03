<?php

namespace App\Http\Controllers\UE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\mention_parcours\Niveau;
use App\Models\mention_parcours\Parcours;
use App\Models\UE\Unite_enseignement;
use App\Models\UE\Element_constitutif;
use Illuminate\Http\Response;
use App\Models\AU\AU;
use PDF;


class UEController extends Controller
{
    public function get_liste_ec(Request $request){
        $request->validate([
            'id_niveau' => ['required', 'numeric', 'exists:niveau,id_niveau'],
            'id_parcours'=>['required', 'numeric', 'exists:parcours,id_parcours'],
            'id_ue' => ['required', 'numeric', 'exists:unite_enseignement,id_unite_enseignement']
        ]);

        $id_niveau = $request->input('id_niveau');
        $id_parcours = $request->input('id_parcours');
        $id_ue = $request->input('id_ue');

        try {
            $au_courant = AU::get_au_en_cours();
            $liste_ec = Unite_enseignement::get_liste_ec($id_parcours, $id_niveau, $id_ue, $au_courant->id_au);
            return response()->json(["ecs" => $liste_ec], 200);
        } catch (\Exception $th) {
            return response()->json(["errors" =>["autres" => $th->getMessage()] ], 500);
        }

    }

    public function get_liste_ue(Request $request){
        $request->validate([
            'id_niveau' => ['required', 'numeric', 'exists:niveau,id_niveau'],
            'id_parcours'=>['required', 'numeric', 'exists:parcours,id_parcours']
        ]);
        $id_niveau = $request->input('id_niveau');
        $id_parcours = $request->input('id_parcours');

        try {
            $au_courant = AU::get_au_en_cours();
            $liste_ue = Unite_enseignement::get_liste_ue($id_parcours, $id_niveau, $au_courant->id_au);
            return response()->json(["ues" => $liste_ue], 200);
        } catch (\Exception $th) {
            return response()->json(["errors" =>["autres" => $th->getMessage()] ], 500);
        }

    }

    public function down_barcode(Request $request){
        $liste_code_barre = session('liste_code_barre');
        $id_ue_ec = $request->input('id_ue_ec');
        $ue_ec = $liste_code_barre[$id_ue_ec];
        $en_plus = $liste_code_barre['en_plus'];
        //var_dump($ue_ec);
        $pdf = PDF::loadview('copies_examen/codes_barres', ['ue_ec'=>$ue_ec, 'en_plus'=>$en_plus]);
        return $pdf->stream('codes-barre-'.$ue_ec->intitule.'-'.$ue_ec->nom_session_examen.'-'.$ue_ec->nom_parcours.'-'.$ue_ec->nom_niveau.'-'.$ue_ec->nom_unite_enseignement.'-'.$ue_ec->nom_element_constitutif.'.pdf');
    }

    public function get_liste_ue_ec_code_barre(){
        $au_courant = AU::get_au_en_cours();
        try {
            $liste_ue_ec = Unite_enseignement::get_liste_ue_ec_code_barre($au_courant->id_au);

            session(['liste_code_barre'=> $liste_ue_ec[1]]);

            return view('copies_examen/liste_ue_ec', ['liste_ue_ec'=>$liste_ue_ec[0]]);
        } catch (\Throwable $th) {
            return view('copies_examen/liste_ue_ec', ['liste_ue_ec'=>[]]);
        }

    }

    public function supprimer_ue_ec(Request $request){
        $data = $request->all();


      //return response()->json($data);

       try {
            Unite_enseignement::supprimer_ue_ec($data['suppressions']);
            return response()->json(
                ['message'=>'Suppression effectuée'],
                200
            );
       } catch (\Throwable $th) {
            return response()->json(
                ['message'=>$th->getMessage(),],
                500
            );
       }

    }


    public function ajouter_ue_ec(Request $request){
        $data = $request->all();


      //return response()->json($data);

       try {
            Unite_enseignement::ajouter_ue_ec($data);
            return response()->json(
                ['message'=>'Enregistrement effectué'],
                200
            );
       } catch (\Exception $th) {
            return response()->json(
                ['message'=>$th->getMessage(),],
                500
            );
       }

    }

    public function liste_ue_ec(Request $request){
        $request->validate([
            'id_niveau' => ['required', 'numeric', 'exists:niveau,id_niveau'],
            'id_parcours'=>['required', 'numeric', 'exists:parcours,id_parcours']
        ]);
        $id_niveau = $request->input('id_niveau');
        $id_parcours = $request->input('id_parcours');

        $au = AU::get_au_en_cours();

        try {
            $liste_ue_ec = Unite_enseignement::get_liste_ue_ec($id_parcours, $id_niveau, $au->id_au);
            return response()->json(
                ['ue_ec'=>$liste_ue_ec],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>$th->getMessage()],
                500
            );
        }


    }

    public function liste_ec(){
        $ecs = Element_constitutif::orderBy('nom_element_constitutif', 'asc')->get();
        return response()->json(
            ['ecs'=>$ecs],
            200
        );
    }

    public function create_ec(Request $request){
        $request->validate([
            'nom_ec' => ['required', 'max:255'],
        ]);

        $nom_ec = $request->input('nom_ec');
        try {
            Element_constitutif::create(
                ['nom_element_constitutif'=>$nom_ec]
            );
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>$th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            ['message'=>'E.C. créée avec succès'],
            Response::HTTP_CREATED
        );
    }

    public function liste_ue(){
        $ues = Unite_enseignement::orderBy('nom_unite_enseignement', 'asc')->get();
        return response()->json(
            ['ues'=>$ues],
            200
        );
    }

    public function create_ue(Request $request){
        $request->validate([
            'nom_ue' => ['required', 'max:255'],
        ]);

        $nom_ue = $request->input('nom_ue');
        try {
            Unite_enseignement::create(
                ['nom_unite_enseignement'=>$nom_ue]
            );
        } catch (\Throwable $th) {
            return response()->json(
                ['message'=>$th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            ['message'=>'U.E. créée avec succès'],
            Response::HTTP_CREATED
        );
    }

    public function ue_form(){
        //récupération de tous les parcours existants
        $parcours = Parcours::all();

        //Niveaux correspondants au premier parcours récupéré;
        $id_parcour_1 = $parcours[0]->id_parcours;
        $niveaux =  Niveau::get_niveaux_parcours($id_parcour_1);

        //récupération de tous les EC et UE préexistantes
        $UE = Unite_enseignement::orderBy('nom_unite_enseignement', 'asc')->get();
        $EC = Element_constitutif::orderBy('nom_element_constitutif', 'asc')->get();

        //récupération des sessions d'examen
        $exams;
        try {
            $exams = AU::get_liste_examens();
        } catch (\Throwable $th) {
            return redirect(route('create_exam_form'))->With('error', $th->getMessage());
        }

        return view('unites_enseignement.crud_ue',
            [
                'parcours'=>$parcours,
                'niveaux'=>$niveaux,
                'UE'=>$UE,
                'EC'=>$EC,
                'exams'=>$exams
            ]
        );

    }
}
