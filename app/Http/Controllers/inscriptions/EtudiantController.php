<?php

namespace App\Http\Controllers\inscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\inscription\Etudiant;
use App\Models\inscription\Province;
use App\Models\inscription\Serie;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Models\inscription\Nationalite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use stdClass;

class EtudiantController extends Controller
{   //informations sur les parents
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

        $etu = $request->session()->get('etu_modif');

        $etu->pere = $request->input('nom_pere');
        $etu->profession_pere = $request->input('profession_pere');
        $etu->tel_pere = $request->input('contact_pere');
        $etu->adresse_pere = $request->input('adresse_pere');

        $etu->mere = $request->input('nom_mere');
        $etu->profession_mere = $request->input('profession_mere');
        $etu->tel_mere = $request->input('contact_mere');
        $etu->adresse_mere = $request->input('adresse_mere');

        $user = Auth::user();
        $etu->id_agent = $user->id;

        $info_photo = Session::get('info_photo');
        if($info_photo != null){
            $nom_fichier = "photo_".$etu->im.".".$info_photo->photoExtension;
            $chemin = storage_path('photo_etudiants/' . $nom_fichier);
            file_put_contents($chemin, $info_photo->photoContent);
            $etu->photo = $chemin;
        }


        $etu->save();
        session()->forget('etu_modif');
        session()->forget('info_photo');

        return redirect(route('maj_etu_search_form'))->with('success', 'Mise à jour effectuée');

    }

    // baccalauréat de l'étudiant
    public function form_bacc(Request $request){
        $request->validate([
            'serie' => ['required','exists:serie,id_serie'],
            'province' => ['required', 'exists:province,id_province'],
            'annee_bacc'=> ['required', 'numeric','digits:4'],
        ]);


        $etu = $request->session()->get('etu_modif');

        $etu->id_serie = $request->input('serie');
        $etu->id_province = $request->input('province');
        $etu->annee_bacc = $request->input('annee_bacc');

        return redirect(route('etudiant.form_parents'));

    }

    //identite de l'etudiant
    public function form_identite(Request $request){
        $request->validate([
            'nationalite' => ['required','exists:nationalites,id_nationalites'],
            'adresse' => ['required', 'max:255'],
            'contact'=> ['required', 'numeric','digits:10'],
            'email'=>['nullable','email'],
            'date_delivrance'=>['nullable','date'],
            'lieu_delivrance'=>['nullable','max:255'],
            'type_pi'=>['nullable',Rule::in(['cin','pass'])],
            'photo'=>['nullable', 'file','mimes:jpg,png,jpeg', 'max:2048']
        ]);

        // enregistrer ces informations dans la session
        $etu = $request->session()->get('etu_modif');

        $etu->id_nationalite = $request->input('nationalite');
        $etu->type_piece_identite = $request->input('type_pi');
        $etu->num_piece_identite = $request->input('p_identite');
        $etu->date_delivrance = $request->input('date_delivrance');
        $etu->lieu_delivrance = $request->input('lieu_delivrance');
        $etu->adresse = $request->input('adresse');
        $etu->telephone = $request->input('contact');
        $etu->email = $request->input('email');

        $photo = $request->file('photo');
        if($photo != null){
            $photoContent = file_get_contents($photo->getRealPath());
            $info_photo = new stdClass();
            $info_photo->photoContent = $photoContent;
            $info_photo->photoExtension = $photo->getClientOriginalExtension();
            Session::put('info_photo', $info_photo);

        }

        return redirect(route('etudiant.form_bacc'));


    }

    //informations sur l'étudiant
    public function form_etudiant(Request $request){
        $request->validate([
            'nom' => ['required','max:255'],
            'prenoms' => ['required', 'max:255'],
            'sexe'=> ['required', Rule::in(['m','f'])],
            'dtn' => ['required', 'date'],
            'ldn' => ['required', 'max:255'],
            'est_officier'=>['required', Rule::in(['0','1'])]
        ]);

        // enregistrer ces informations dans la session
        $etu = $request->session()->get('etu_modif');

        $etu->nom = $request->input('nom');
        $etu->prenoms = $request->input('prenoms');
        $etu->sexe = $request->input('sexe');
        $etu->date_naissance = $request->input('dtn');
        $etu->lieu_naissance = $request->input('ldn');
        $etu->est_officier = $request->input('est_officier');

        return redirect(route('form_identite_modif_form'));

    }


    //recherche d'étudiant
    public function search_etudiant(Request $request){
        $request->validate([
            'matricule' => ['required','numeric', 'exists:etudiants,im'],
        ]);

        $matricule = $request->input('matricule');
        $etudiant = Etudiant::where('im',$matricule)->first();
        session(['etu_modif'=>$etudiant]);
        //var_dump($etudiant);

        return redirect(route('form_etudiant_modif_form'));
    }
}
