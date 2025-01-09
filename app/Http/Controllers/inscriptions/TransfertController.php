<?php

namespace App\Http\Controllers\inscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AU\AU;
use App\Models\inscription\Etudiant;
use App\Models\mention_parcours\Parcours;
use App\Models\inscription\Autre_inscription;
use App\Models\mention_parcours\Niveau;
use App\Models\parametres\Autre_Etablissement;
use Illuminate\Validation\Rule;
use App\Models\inscription\Nationalite;
use App\Models\inscription\Serie;
use App\Models\inscription\Province;


class TransfertController extends Controller
{
    public function inscription(Request $request){
        $request->validate([
            //pere
            'autre_etablissement' => ['nullable','max:255'],
            'autre_annee_etude' => ['nullable', 'max:20'],
        ]);

        $new_etu = $request->session()->get('etu_transfert');
        $niveau_inscription = session('niveau_inscription');

        $new_etu->date_premiere_inscription = date('Y-m-d');
        $autre_inscription = new Autre_inscription();
        $autre_inscription->etablissement = $request->input('autre_etablissement');
        $autre_inscription->niveau = $request->input('autre_annee_etude');

        //Inscription
        Etudiant::transfert($new_etu, $autre_inscription, $niveau_inscription);

        session()->forget('etu_modif');
        session()->forget('niveau_inscription');
        return redirect(route('transfert_etu_form'))->with('success', 'Inscription par transfert effectuée');
    }

    //informations sur les parents
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

        $etu = $request->session()->get('etu_transfert');

        $etu->pere = $request->input('nom_pere');
        $etu->profession_pere = $request->input('profession_pere');
        $etu->tel_pere = $request->input('contact_pere');
        $etu->adresse_pere = $request->input('adresse_pere');

        $etu->mere = $request->input('nom_mere');
        $etu->profession_mere = $request->input('profession_mere');
        $etu->tel_mere = $request->input('contact_mere');
        $etu->adresse_mere = $request->input('adresse_mere');



        return redirect(route('autres_inscriptions_f'));

    }

    // baccalauréat de l'étudiant
    public function form_bacc(Request $request){
        $request->validate([
            'serie' => ['required','exists:serie,id_serie'],
            'province' => ['required', 'exists:province,id_province'],
            'annee_bacc'=> ['required', 'numeric','digits:4'],
        ]);


        $etu = $request->session()->get('etu_transfert');

        $etu->id_serie = $request->input('serie');
        $etu->id_province = $request->input('province');
        $etu->annee_bacc = $request->input('annee_bacc');

        return redirect(route('form_parents_f'));

    }

    public function form_bacc_f(){
        $series = Serie::all();
        $provinces =  Province::all();

        return view('transfert/form_bacc',['series'=>$series, 'provinces'=>$provinces]);
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
            'photo'=>['required', 'file','mimes:jpg,png,jpeg', 'max:2048']
        ]);

        // enregistrer ces informations dans la session
        $etu = $request->session()->get('etu_transfert');

        $etu->id_nationalite = $request->input('nationalite');
        $etu->type_piece_identite = $request->input('type_pi');
        $etu->num_piece_identite = $request->input('p_identite');
        $etu->date_delivrance = $request->input('date_delivrance');
        $etu->lieu_delivrance = $request->input('lieu_delivrance');
        $etu->adresse = $request->input('adresse');
        $etu->telephone = $request->input('contact');
        $etu->email = $request->input('email');

        $photo = $request->file('photo');
        $photoContent = file_get_contents($photo->getRealPath());
        $etu->photoContent = $photoContent;
        $etu->photoExtension = $photo->getClientOriginalExtension();

        return redirect(route('form_bacc_f'));
    }

    public function form_identite_f(){
        $nationalites = Nationalite::all();

        //rediriger vers le formualaire identite
        return view('transfert/form_identite',['nationalites' =>$nationalites]);

    }


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
        $etu = $request->session()->get('etu_transfert');

        $etu->nom = $request->input('nom');
        $etu->prenoms = $request->input('prenoms');
        $etu->sexe = $request->input('sexe');
        $etu->date_naissance = $request->input('dtn');
        $etu->lieu_naissance = $request->input('ldn');
        $etu->est_officier = $request->input('est_officier');

        return redirect(route('form_identite_f'));

    }

    public function transfert_etu(Request $request){
        $request->validate([
            'etablissement' => ['required','numeric', 'exists:autres_etablissements,id_autre_etablissement'],
            'annee_universitaire_transfert'=>['required', 'numeric','exists:niveau,id_niveau'],
            'niveau_transfert'=>['required', 'numeric', 'exists:niveau,id_niveau'],
            'parcours' => ['required', 'numeric', 'exists:parcours,id_parcours'],
            'matricule'=>['required', 'numeric', 'unique:etudiants,im']
        ]);

        $id_niveau_transfert = $request->input('niveau_transfert');
        $id_parcours = $request->input('parcours');

        $new_etu = new Etudiant();
        $new_etu->im = $request->input('matricule');
        $new_etu->id_etablissement_transfert = $request->input('etablissement');
        $new_etu->id_au_transfert = $request->input('annee_universitaire_transfert');
        $new_etu->id_niveau_transfert = $id_niveau_transfert;
        $new_etu->id_parcours = $id_parcours;

        $niveau_inscription;
        // récupération du niveau suivant pour l'inscription
        try {
            $niveau_inscription = Niveau::get_niveau_suivant($id_niveau_transfert, $id_parcours);

            //vérifier si un transfert est autorisé pour ce niveau dans ce parours
            Niveau::check_transfert_autorise($niveau_inscription);

        } catch (\Exception $ex) {
            return redirect()->back()->With('error', $ex->getMessage())
                                    ->WithInput();
        }

        session([
                'etu_transfert'=>$new_etu,
                'niveau_inscription'=>$niveau_inscription
            ]);

        return  redirect(route('form_etudiant_f'));

    }

    public function transfert_etu_form(){
        $au_fermees = AU::get_au_fermees();
        $niveaux_transfert = Niveau::all();
        $parcours = Parcours::all();
        $etablissements = Autre_Etablissement::all();

        return view('transfert/form_transfert',[
                        'aus'=>$au_fermees,
                        'niveaux'=>$niveaux_transfert,
                        'parcours'=>$parcours,
                        'etablissements'=>$etablissements
                    ]);
    }
}
