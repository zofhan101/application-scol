<?php

namespace App\Http\Controllers\inscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AU\AU;
use App\Models\inscription\Selectionnes;
use App\Models\inscription\Etudiant;
use App\Models\inscription\Province;
use App\Models\inscription\Serie;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Rules\inscription\AdmissionExists;
use App\Models\inscription\Nationalite;
use App\Models\inscription\Autre_inscription;
use App\Models\inscription\Inscription;



class Inscription_controller extends Controller
{
    public function inscription(Request $request){
        $request->validate([
            //pere
            'autre_etablissement' => ['nullable','max:255'],
            'autre_annee_etude' => ['nullable', 'max:20'],
        ]);

        $new_etu = $request->session()->get('new_etu');

        $new_etu->autre_etab = $request->input('autre_etablissement');
        $new_etu->autre_ae = $request->input('autre_annee_etude');

        //Inscription
        Etudiant::inscrire($new_etu);

        session()->forget('new_etu');

        return view('inscriptions/check_admission',['success'=>'Inscription effectuée']);
    }

    public function form_parents(Request $request){
        $request->validate([
            //pere
            'nom_pere' => ['required','max:255'],
            'profession_pere' => ['required', 'max:50'],
            'contact_pere'=> ['required', 'numeric','digits:10'],
            'adresse_pere'=> ['required', 'max:255'],
            //mere
            'nom_mere' => ['required','max:255'],
            'profession_mere' => ['required', 'max:50'],
            'contact_mere'=> ['required', 'numeric','digits:10'],
            'adresse_mere'=> ['required', 'max:255'],
        ]);

        $new_etu = $request->session()->get('new_etu');

        $new_etu->nom_pere = $request->input('nom_pere');
        $new_etu->profession_pere = $request->input('profession_pere');
        $new_etu->contact_pere = $request->input('contact_pere');
        $new_etu->adresse_pere = $request->input('adresse_pere');

        $new_etu->nom_mere = $request->input('nom_mere');
        $new_etu->profession_mere = $request->input('profession_mere');
        $new_etu->contact_mere = $request->input('contact_mere');
        $new_etu->adresse_mere = $request->input('adresse_mere');

        return redirect('inscription/form_autres_inscriptions');

    }

    public function form_bacc(Request $request){
        $request->validate([
            'serie' => ['required','exists:serie,id_serie'],
            'province' => ['required', 'exists:province,id_province'],
            'annee_bacc'=> ['required', 'numeric','digits:4'],
        ]);


        $new_etu = $request->session()->get('new_etu');

        $new_etu->id_serie = $request->input('serie');
        $new_etu->id_province = $request->input('province');
        $new_etu->annee_bacc = $request->input('annee_bacc');

        return redirect('inscription/form_parents');

    }

    public function form_bacc_page(){
        $series = Serie::all();
        $provinces =  Province::all();

        return view('inscriptions/form_bacc',['series'=>$series,
                                            'provinces'=>$provinces]);
    }

    public function form_identite(Request $request){
        $request->validate([
            'nationalite' => ['required','exists:nationalites,id_nationalites'],
            'adresse' => ['required', 'max:255'],
            'contact'=> ['required', 'numeric','digits:10'],
            'date_delivrance'=>['nullable','date'],
            'lieu_delivrance'=>['nullable','max:255'],
            'type_pi'=>['nullable',Rule::in(['cin','pass'])]
        ]);

        // enregistrer ces informations dans la session
        $new_etu = $request->session()->get('new_etu');

        $new_etu->id_nationalite = $request->input('nationalite');
        $new_etu->type_pi = $request->input('type_pi');
        $new_etu->num_pi = $request->input('p_identite');
        $new_etu->date_delivrance = $request->input('date_delivrance');
        $new_etu->lieu_delivrance = $request->input('lieu_delivrance');
        $new_etu->adresse = $request->input('adresse');
        $new_etu->contact = $request->input('contact');

        return redirect('inscription/form_bacc');
    }

    public function form_identite_page(){
        $nationalites = Nationalite::all();
        return view('inscriptions/form_identite',['nationalites' =>$nationalites]);
    }
    public function form_etudiant(Request $request){
        $request->validate([
            'nom' => ['required','max:255'],
            'prenoms' => ['required', 'max:255'],
            'sexe'=> ['required', Rule::in(['m','f'])],
            'dtn' => ['required', 'date'],
            'ldn' => ['required', 'max:255'],
            'est_officier'=>['required']
        ]);

        // enregistrer ces informations dans la session
        $new_etu = $request->session()->get('new_etu');

        $new_etu->nom_candidat = $request->input('nom');
        $new_etu->prenom_candidat = $request->input('prenoms');
        $new_etu->sexe = $request->input('sexe');
        $new_etu->dtn = $request->input('dtn');
        $new_etu->ldn = $request->input('ldn');
        $new_etu->est_officier = $request->input('est_officier');

        //rediriger vers le formualaire identite
        return redirect('inscription/form_identite');

    }

    public function choix_parcours(Request $request){
        $request->validate([
            'matricule' => ['required','integer', 'unique:etudiants,im'],
            'parcours' => ['required','integer',new AdmissionExists]
        ]);

        // stocker dans l'objet session new_etu le matricule et l'id_parcours
        $new_etu = $request->session()->get('new_etu');
        $new_etu->matricule = $request->input('matricule');
        $new_etu->id_parcours = $request->input('parcours');

        //var_dump( $request->session()->get('new_etu'));
        // passer vers le formulaire etu
        return redirect('inscription/form_etudiant');

    }

    public function verifier_admission(Request $request){
        $request->validate([
            'num_bacc' => ['required']
        ]);
        $num_bacc = $request->input('num_bacc');
        $au = AU::get_au_en_cours();
        try{
            $parcours = Selectionnes::check_admission($au->id_au,$num_bacc);
            $new_etu = (object)['nom_candidat'=>$parcours[0]->nom, 'prenom_candidat'=>$parcours[0]->prenoms, 'num_bacc'=>$parcours[0]->num_bacc];
            session(['new_etu'=>$new_etu]);
            return view('inscriptions/choix_parcours',['parcours'=>$parcours]);
        }
        catch(Exception $e){
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
