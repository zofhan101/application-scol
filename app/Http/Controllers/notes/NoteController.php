<?php

namespace App\Http\Controllers\notes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\notes\operation_sur_resultats;
use App\Models\AU\AU;
use App\Models\notes\Operation_sur_examen;
use App\Models\notes\Operation_sur_au;
use Illuminate\Support\Facades\Auth;
use App\Rules\notes\IsNoteValide;
use App\Rules\notes\IsBarcodeValide;
use App\Rules\notes\IsMatriculeValide;
use Illuminate\Database\UniqueConstraintViolationException;
use Exception;
use Illuminate\Support\Facades\Session;
use App\Models\UE\Unite_enseignement;
use App\Models\inscription\Inscription;
use App\Models\mention_parcours\Parcours;
use App\Models\mention_parcours\Niveau;
use Excel;
use App\Exports\ResultatsEvalExport;
use App\Exports\AllResultsExport;
use App\Exports\ListeRepechageAllExport;
use App\Exports\ListeAppelAllExport;
use App\Exports\ListeAdmissionAllExport;
use App\Exports\ListesRedoublantsAllExport;
use App\Exports\ListeTriplantsAllExport;
use App\Exports\EntExport;
use PDF;
use App\Models\inscription\Etudiant;
use App\Imports\PacesImport;




class NoteController extends Controller
{
    //import des résultats du concours PACES
    public function import_resultats_paces_form(){
        $parcours = Parcours::all();
        return view('notes/import_resultats_paces_form', ['parcours'=>$parcours]);
    }

    public function import_resultats_paces(Request $request){
        //récupération du parcours dont on importe le résultat et le fichier excel
        $request->validate([
            'parcours' => ['required', 'numeric', 'exists:parcours,id_parcours'],
            'fichier_excel' =>['required','file','mimes:xlsx']
        ]);

        $id_parcours = $request->input('parcours');
        $fichier = $request->file('fichier_excel');

        //récupération de l'année universitaire en cours
        $au = AU::get_au_en_cours();
        $id_au = $au->id_au;

        //récupération du niveau PACES
        $paces = Niveau::where('rang', 1)->first();
        $id_niveau = $paces->id_niveau;

        //récupération du parcours concerné
        $parcours = Parcours::find($id_parcours);

        // le niveau l2
        $l2 = Niveau::where('rang', 2)->first();

        //le concours paces
        $epa = AU::get_concours_paces($id_au);

        //l'utilisateur en cours
        $user = Auth::user();

        //controle d'existence préalable de cette opération d'importation
        $operations_par_import = Operation_sur_au::get_operation_par_import($id_au, $id_parcours);
        if(!empty($operations_par_import)){
            return redirect()->back()->with("error", "ERREUR: impossible de réaliser l'importation des résultats du concours PACES pour le parcours sélectionné car elle a déjà été faite.");
        }

        //chargement du fichier en mémoire
        $fileContent = Excel::toArray(null, $fichier)[0];
        $ligne1 = $fileContent[0];

        //controle de l'entête du fichier (liste des matières présentes)
        // Charger les matières depuis la base de données
        $subjectsInDatabase = Unite_enseignement::get_liste_ue($id_parcours, $id_niveau, $id_au);

        // Filtrer les colonnes entre AF et avant "moyenne": liste des matières présentes dans le fichier
        $subjectsInFile = $this->getSubjectsBetweenAFAndBeforeMoyenne($ligne1);

        //liste des matières manquantes
        $orderedSubjects = $this->getMissingSubjectsAndSubjectIndexInFile($subjectsInDatabase, $subjectsInFile);
        $missingSubjects = $orderedSubjects[0];
        if(!empty($missingSubjects)){
            return redirect()->back()
            ->with("error", "ERREUR: matières manquantes dans le fichier détectées")
            ->with("manquantes", $missingSubjects);
        }

        $foundSubjects = $orderedSubjects[1];

        // CONTROLE DU CONTENU DU FICHIER
        $erreurs_fichier = Operation_sur_examen::controler_fichier(array_slice($fileContent, 1), $foundSubjects, $id_parcours);
        if(!empty($erreurs_fichier)){
            return redirect()->back()
            ->with("error", "ERREUR: anomalies détctées dans le contenu du fichier")
            ->with("erreurs_fichier", $erreurs_fichier);
        }

        // IMPORTATION DES DONNEES

        Excel::import(new PacesImport($au, $parcours, $paces, $epa, $l2, $foundSubjects, $user), $fichier);

        return redirect()->back()->with('success', 'Import effectué avec succès');

    }


    private function getMissingSubjectsAndSubjectIndexInFile($subjectsInDatabase, $subjectsInFile){
        $found;
        $missingSubjects = [];
        $foundSubjects = [];

        foreach($subjectsInDatabase as $subjectDB){
            $found = false;
            foreach($subjectsInFile as $subjectFile){
                if(strtolower($subjectDB->nom_unite_enseignement) == strtolower($subjectFile[0])){
                    $found = true;
                    $foundSubjects[] = [$subjectDB, $subjectFile[1]];
                }
            }
            if($found == false){
                $missingSubjects[] = $subjectDB;
            }



            $found = false;
        }
        return [$missingSubjects, $foundSubjects];
    }


    private function getSubjectsBetweenAFAndBeforeMoyenne($headerRow)
    {
        $subjects = [];
        $startAdding = false;
        $value = '';

        for($i = 32;  strtolower($value) != 'moyenne'; $i=$i+2) {
            $value = $headerRow[$i];
            if(strtolower($value) != 'moyenne')
                $subjects[] = [$value, $i];
        }

        return $subjects;
    }

    // statistique pour alimenter le diagramme en baton (nombre des admis redoublant tripant et exclu)
    public function getNombreAdmis(Request $request){
        $request->validate([

            "id_au"=> ['required', 'numeric' , 'exists:au,id_au'],
            "id_parcours" =>['required','numeric','exists:parcours,id_parcours'],
            "id_niveau"=> ['required' , 'numeric' , 'exists:niveau,id_niveau'],

        ]);

        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $id_au = $request->input('id_au');
        try{

            $results = Operation_sur_au::getAdmis($id_parcours,$id_niveau,$id_au);
            return response()->json($results , 200);
        }
        catch(\Throwable $th){
            return response()->json(['errors' => ["message" => $th]], 500);
        }

    }
    // statistique pour alimenter le donut(nombre de validé eliminatoire ou non validé dans une matiere)
    public function getNombreValide(Request $request){
        $request->validate([
            "id_au"=> ['required', 'numeric' , 'exists:au,id_au'],
            "id_parcours" =>['required','numeric','exists:parcours,id_parcours'],
            "id_niveau"=> ['required' , 'numeric' , 'exists:niveau,id_niveau'],
            "id_ue" => ['required' , 'numeric' , 'exists:unite_enseignement,id_unite_enseignement']
        ]);

        $id_parcours = $request->input('id_parcours');
        $id_au = $request->input('id_au');
        $id_niveau = $request->input('id_niveau');
        $id_ue = $request->input('id_ue');

        try{
            $results = Operation_sur_au::getNombreValide($id_au,$id_parcours,$id_niveau,$id_ue);
            return response()->json($results , 200);
        }
        catch(\Throwable $th){
            return response()->json(['errors' => ["message" => $th]], 500);
        }

    }
    // statistique de nombre d'inscrits
    public function getNombreInscrits(Request $request){
        $request->validate([
            "id_au"=> ['required', 'numeric' , 'exists:au,id_au'],
            "id_parcours" =>['required','numeric','exists:parcours,id_parcours'],
            "id_niveau"=> ['required' , 'numeric' , 'exists:niveau,id_niveau']
        ]);

        $id_parcours = $request->input('id_parcours');
        $id_au = $request->input('id_au');
        $id_niveau = $request->input('id_niveau');

        try{
            $results = Operation_sur_au::getNombreInscrits($id_au,$id_parcours,$id_niveau);
            return response()->json($results , 200);
        }
        catch(\Throwable $th){
            return response()->json(['errors' => ["message" => $th]], 500);
        }
    }

    // prendre la combinaison ue ec session pour un parcours et un niveau donné
    public function getUeEcSession(Request $request){
        $au = AU::get_au_en_cours();
        $id_au = $au->id_au;
        $request->validate([
            'id_parcours' =>['required','numeric','exists:parcours,id_parcours'],
            'id_niveau' =>['required','numeric','exists:niveau,id_niveau']
        ]);

        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        try{
            $ueEcSession = Operation_sur_examen::getUeEcSession($id_parcours,$id_niveau,$id_au);

            return response()->json($ueEcSession , 200);


        }
        catch(\Throwable $th){
            return response()->json(['errors' => ["message" => $th->getMessage()]], 500);
        }


    }
    public function liste_Parcours(){
        $results = Parcours::all();

        return view('notes/enregistrer_notes_stage',[ "parcours" => $results] );
    }

    //enregistrement des notes de stages et TP
    public function save_notes(Request $request){
        $request->validate([
            'im' => ['required','numeric','exists:etudiants,im'],
            'note' => ['required','numeric',new IsNoteValide],
            'id_ue_ec' => ['required','exists:ue_ec_parcours_niveau_au']
        ]);
        $im = $request->input('im');
        $note = $request->input('note');
        $id_ue_ec = $request->input('id_ue_ec');

        Operation_sur_examen::enregistrer_note_stage($id_ue_ec ,$im ,$note);

        return response()->json(['message' =>"note enregistrée"], 200);
    }


    //export ENT des résultats
    public function down_resultats_ent_form(){
        $aus = AU::all();
        return view('notes/down_resultats_ent_form', ['aus'=>$aus]);
    }

    public function down_resultats_ent(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);
        $id_au = $request->input('id_au');
        $au = AU::find($id_au);

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: exportation ENT des résultats impossible car aucune opération de génération des résultats n'a été trouvée pour l'A.U  sélectionnée");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_definitifs != null){
                //télécharger le listes
                $titre = "résultats_ent_".$au->intitule;
                try {
                    $resultats = Operation_sur_au::get_data_export_ent($id_au);
                    return Excel::download(new EntExport($resultats), $titre.'.csv');

                } catch (\Throwable $th) {
                    return redirect()->back()->with("error", $th->getMessage());
                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: téléchargement des listes des exclus impossible car les résultats définitifs n'ont pas encore été préparés  pour l'A.U. sélectionnée");
            }
        }


    }

    // rehcherche et affichage du dossier complet d'un étudiant
    public function get_infos_etudiant(Request $request){
        $request->validate([
            'im' => ['required','numeric', 'exists:etudiants,im'],
        ]);
        $im = $request->input('im');

        $resultats = [];

        try {
            $resultats = Operation_sur_au::get_resultats_by_im($im);
        } catch (\Exception $th) {

        }

        $etudiant = Etudiant::where('im', $im)->first();

        if($etudiant != null){
            $id_parcours = $etudiant->id_parcours;
            $parcours = Parcours::find($id_parcours);
            $mention = Parcours::get_mention_by_id_parcours($id_parcours);
            return view('notes/infos_etudiant', [
                'resultats'=> $resultats,
                'etudiant'=> $etudiant,
                'mention'=> $mention,
                'parcours'=>$parcours
            ]);
        }
        else{
            return view('notes/missing_student', [
                'error'=> 'ERREUR: Etudiant inexistant'
            ]);
        }

    }

    //téléchargement des releves de note d'un étudiant
    public function down_releve_notes(Request $request){
        $request->validate([
            'im' => ['required','numeric', 'exists:etudiants,im'],
        ]);
        $im = $request->input('im');
        try {
            $resultats = Operation_sur_au::get_resultats_by_im($im);
            $id_parcours = $resultats[0][0]->id_parcours;
            $mention = Parcours::get_mention_by_id_parcours($id_parcours);

            $options = [
                'enable-local-file-access' => true,
                'disable-smart-shrinking' => true,
                'print-media-type' => true,
                'page-size'=>'A4'
            ];
            $pdf = PDF::loadview('notes/releve_notes', ['resultats'=>$resultats, 'mention'=>$mention])->setOptions($options);
            return $pdf->download('notes-'.$im.'.pdf');

        } catch (\Exception $th) {
            return redirect()->back()->with("error", "ERREUR: ".$th);
        }
    }

    // téléchargement des liste des exclus
    public function down_listes_exclus_form(){
        $au = AU::all();
        return view('notes/down_listes_exclus_form',[
            "aus" => $au
        ]);
    }

    public function down_listes_exclus(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $au = AU::find($id_au);

        $titre = "Listes_exclus"."_".$au->intitule;

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: téléchargement des listes des exclus impossible car aucune opération de génération des résultats n'a été trouvée pour l'A.U  sélectionnée");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_definitifs != null){
                //télécharger le listes

                try {
                    $listes = Operation_sur_au::get_listes_exclus($id_au);
                    return Excel::download(new ListeExclusAllExport($listes), $titre.'.xlsx');

                } catch (\Throwable $th) {
                    return redirect()->back()->with("error", $th->getMessage());
                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: téléchargement des listes des exclus impossible car les résultats définitifs n'ont pas encore été préparés  pour l'A.U. sélectionnée");
            }
        }



    }


    // téléchargement des liste des triplants
    public function down_listes_triplants_form(){
        $au = AU::all();
        return view('notes/down_listes_triplants_form',[
            "aus" => $au
        ]);
    }

    public function down_listes_triplants(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $au = AU::find($id_au);

        $titre = "Listes_triplants"."_".$au->intitule;

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: téléchargement des listes des triplants impossible car aucune opération de génération des résultats n'a été trouvée pour l'A.U  sélectionnée");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_definitifs != null){
                //télécharger le listes

                try {
                    $listes = Operation_sur_au::get_listes_triplants($id_au);
                    return Excel::download(new ListeTriplantsAllExport($listes), $titre.'.xlsx');

                } catch (\Throwable $th) {
                    return redirect()->back()->with("error", $th->getMessage());
                }



            }
            else{
                return redirect()->back()->with("error", "ERREUR: téléchargement des listes des triplants impossible car les résultats définitifs n'ont pas encore été préparés  pour l'A.U. sélectionnée");
            }
        }



    }


    // téléchargement des liste des redoublants
    public function down_listes_redoublants_form(){
        $au = AU::all();
        return view('notes/down_listes_redoublants_form',[
            "aus" => $au
        ]);
    }

    public function down_listes_redoublants(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $au = AU::find($id_au);

        $titre = "Listes_redoublants"."_".$au->intitule;

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: téléchargement des listes des redoublants impossible car aucune opération de génération des résultats n'a été trouvée pour l'A.U  sélectionnée");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_definitifs != null){
                //télécharger le listes

                try {
                    $listes = Operation_sur_au::get_listes_redoublants($id_au);
                    return Excel::download(new ListesRedoublantsAllExport($listes), $titre.'.xlsx');

                } catch (\Throwable $th) {
                    return redirect()->back()->with("error", $th->getMessage());
                }



            }
            else{
                return redirect()->back()->with("error", "ERREUR: téléchargement des listes des redoublants impossible car les résultats définitifs n'ont pas encore été préparés  pour l'A.U. sélectionné");
            }
        }



    }


    //préparation des résultats définitifs
    public function preparer_resultats_definitifs(){
        $au_courant = AU::get_au_en_cours();
        $user = Auth::user();

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($au_courant->id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: préparation des résultats définitifs impossible car aucune opération de génération des résultats annuels n'a été trouvée ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_deliberation != null && $operation->date_resultats_definitifs == null ){
                //récupérer les résultats
                try {
                    Operation_sur_au::preparer_resultats_definitifs($au_courant->id_au, $user->id);
                    return redirect()->back()->with("success", "Préparation des résultats définitifs effectuée");

                } catch (\Exception $th) {
                    return redirect()->back()->with("error", $th);

                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: résultats avant délibération non générés ou resultats définitifs déjà préparés ");
            }
        }
    }

    // téléchargement des liste des admis
     public function down_listes_admission(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $au = AU::find($id_au);

        $titre = "Listes_admission"."_".$au->intitule;

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: téléchargement des listes des admis impossible car aucune opération de génération des résultats n'a été trouvée pour l'A.U  sélectionnée");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_definitifs != null){
                //télécharger le listes

                try {
                    $listes = Operation_sur_au::get_listes_admission($id_au);
                    return Excel::download(new ListeAdmissionAllExport($listes), $titre.'.xlsx');

                } catch (\Throwable $th) {
                    return redirect()->back()->with("error", $th->getMessage());
                }



            }
            else{
                return redirect()->back()->with("error", "ERREUR: téléchargement des listes des admis impossible car les résultats définitifs n'ont pas encore été préparés  pour l'A.U. sélectionné");
            }
        }



    }

    public function down_listes_admissio_form(){
        $au = AU::all();
        return view('notes/down_liste_admis_form',[
            "aus" => $au
        ]);
    }

    //Consultation des résultats définitifs
    public function get_resultats_definitifs(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();

        $operations_par_deliberation = Operation_sur_au::get_operation_par_deliberation($id_au, $id_parcours, $id_niveau);
        if(empty($operations_par_deliberation)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels définitifs impossible car aucune opération de délibération n'a été trouvée pour les A.U., parcours et niveaux demandés ");
        }
        else{
            $operation = $operations_par_deliberation[0];
            if($operation->date_cloture_deliberation != null){
                //récupérer les résultats
                try {
                    $resultats = Operation_sur_au::get_resultats_definitifs($id_au, $id_parcours, $id_niveau);
                    //passer les résultats à la vue
                    return view('notes/resultats_definitifs',[
                        "resultats" => $resultats,
                    ]);
                } catch (\Exception $th) {
                    return redirect()->back()->with("error", $th);

                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels définitifs impossible car la délibération n'est pas encore cloturée pour les A.U., parcours et niveau selectionnés ");
            }
        }
    }

    public function get_resultats_definitifs_form(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/resultats_definitifs_form',
        [
            "aus" => $au,
            "parcours" => $parcours
        ]);

    }


    // délibération

    public function admettre_etudiant(Request $request){
        $request->validate([
            'id_etudiant' => ['required','numeric', 'exists:etudiants,id_etudiants'],
            'rang_niveau' => ['required','numeric', 'exists:niveau,rang'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);

        $id_etudiant = $request->input('id_etudiant');
        $rang_niveau = $request->input('rang_niveau');

        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();
        $id_au = $au_courant->id_au;

        //vérifier l'ouverture de la délibération
        $operations = Operation_sur_au::get_operation_par_deliberation($id_au, $id_parcours, $id_niveau);
        if(empty($operations)){
            return response()->json(["errors"=>"ERREUR: aucune operation de délibération n' a été trouvée pour les pacours et niveau selectionnés"]);
        }
        else{
            $operation = $operations[0];
            if($operation->date_ouverture_deliberation != null){
                //délibération ouverte: admission
                Operation_sur_au::admettre_etudiant($id_au, $id_etudiant, $rang_niveau);
                return response()->json(["message"=>"Admssion effectuée"]);
            }
            else{
                return response()->json(["errors"=>"ERREUR: délibération non encore ouverte pour les AU, pacours et niveau selectionnés"]);
            }
        }


    }

    public function interface_deliberation_form(){
        $parcours = Parcours::all();
        return view('notes/interface_deliberation_form',
        ['parcours'=>$parcours]);
    }

    public function cloturer_deliberation(Request $request){
        $request->validate([
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();
        $user = Auth::user();
        $parcours = Parcours::all();



        try {
            Operation_sur_au::cloturer_deliberation($au_courant->id_au, $id_parcours, $id_niveau, $user->id_user);
            return view('notes/controle_deliberation',
                ['success'=>"MESSAGE: cloture de la délibération effectuée",
                'parcours'=>$parcours
            ]);
        } catch (\Exception $th) {
            return view('notes/controle_deliberation',
                ['error'=>"ERREUR: ".$th->getMessage(),
                'parcours'=>$parcours

            ]);
        }

    }

    public function ouvrir_deliberation(Request $request){
        $request->validate([
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();
        $user = Auth::user();
        $parcours = Parcours::all();

        try {
            Operation_sur_au::ouvrir_deliberation($au_courant->id_au, $id_parcours, $id_niveau, $user->id);
            return view('notes/controle_deliberation',
                ['success'=>"MESSAGE: ouverture de la délibération effectuée",
                 'parcours'=>$parcours
            ]);
        } catch (\Exception $th) {
            return view('notes/controle_deliberation',
                ['error'=>"ERREUR: ".$th->getMessage(),
                'parcours'=>$parcours
            ]);
        }


    }

    public function controle_deliberation(){
        $parcours = Parcours::all();
        return view('notes/controle_deliberation',
        ['parcours'=>$parcours]);
    }

    public function interface_deliberation(Request $request){

        $request->validate([
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);

        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();
        $id_au = $au_courant->id_au;

        try {
            // vérifier l'ouverture de la délibération
            $operations = Operation_sur_au::get_operation_par_deliberation($id_au, $id_parcours, $id_niveau);
            if(empty($operations)){
                return redirect()->back()->with("error", "ACCES REFUSE: aucune operation de délibération n' a été trouvée pour les pacours et niveau selectionnés");
            }
            else{
                $operation = $operations[0];
                if($operation->date_ouverture_deliberation != null){
                    //délibération ouverte: chargement et transfert des données
                    $data = Operation_sur_au::get_data_deliberation($id_au, $id_parcours, $id_niveau);
                    return view('notes/interface_deliberation',[
                        "datas" => $data,
                    ]);
                }
                else{
                    return redirect()->back()->with("error", "ACCES REFUSE: délibération non encore ouverte pour les AU, pacours et niveau selectionnés");
                }

            }
        } catch (\Exception $th) {
            return redirect()->back()->with("error", $th);
        }

    }

    //Consultation des résultats avant délibération
    public function get_resultats_avant_deliberation(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($au_courant->id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels avant délibération impossible car aucune opération de génération des résultats annuels n'a été trouvée ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_deliberation != null){
                //récupérer les résultats
                try {
                    $resultats = Operation_sur_au::get_resultats_avant_deliberation($id_au, $id_parcours, $id_niveau);
                    //passer les résultats à la vue
                    return view('notes/resultats_avant_deliberation',[
                        "resultats" => $resultats,
                    ]);
                } catch (\Exception $th) {
                    return redirect()->back()->with("error", $th);

                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels avant delibération impossible car ils n'ont pas encore été générés ");
            }
        }
    }

    public function get_resultats_avant_deliberation_form(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/get_resultats_avant_deliberation_form',[
            "aus" => $au,
            "parcours" => $parcours
        ]);

    }

    // téléchargement des lites d'appel
    public function down_liste_appel(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $au = AU::find($id_au);

        $titre = "Listes_appel_repêchage"."_".$au->intitule;

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: récupération de la liste d'appel impossible car les résultats annuels avant repêchage n'ont pas encore été générés ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_repechage != null){
                //récupérer les résultats
                $resultats = Operation_sur_au::get_liste_appel($id_au);

                //var_dump($resultats);
                return Excel::download(new ListeAppelAllExport($resultats), $titre.'.xlsx');

            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération de la liste d'appel impossible car les résultats annuels avant repêchage n'ont pas encore été générés ");
            }
        }

    }

    public function down_liste_appel_form(){
        $au = AU::all();
        return view('notes/down_liste_appel_form',[
            "aus" => $au
        ]);
    }

    // téléchargement des listes de repêchage
    public function down_liste_repechage(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
        ]);

        $id_au = $request->input('id_au');

        $parcours = Parcours::all();
        $au = AU::find($id_au);

        $resultats = [];
        $sous_titres = [];
        $sous_titre;

        $niveaux;
        $titre = "Listes_repêchage"."_".$au->intitule;

        $legendes = [];
        $legendes[] = "V = Validé";
        $legendes[] = "N = Non-Validé";
        $legendes[] = "E = Eliminatoire";


        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: récupération de la liste de repêchage impossible car les résultats annuels avant repêchage n'ont pas encore été générés ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_repechage != null){
                //récupérer les résultats
                foreach($parcours as $parcour){
                    $niveaux = Niveau::get_niveaux_parcours($parcour->id_parcours);
                    foreach($niveaux as $niveau){
                        $res = Operation_sur_au::get_liste_repechage($id_au, $parcour->id_parcours, $niveau->id_niveau);
                        if(empty($res) == false){
                            $resultats[] = $res;

                            $sous_titre = [];
                            $sous_titre[] = "Repêchage des évaluations";
                            $sous_titre[] = $niveau->nom_niveau." - ".$parcour->nom_parcours;
                            $sous_titre[] = "Année Universitaire ".$au->intitule;
                            $sous_titres[] = $sous_titre;
                        }

                    }
                }

                return Excel::download(new ListeRepechageAllExport($resultats, $sous_titres, $legendes), $titre.'.xlsx');

            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération de la liste de repêchage impossible car les résultats annuels avant repêchage n'ont pas encore été générés ");
            }
        }

    }

    public function down_liste_repechage_form(){
        $au = AU::all();
        return view('notes/down_liste_repechage_page',[
            "aus" => $au
        ]);
    }
    //affichage de la liste de repechage
    public function get_liste_repechage_page(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/liste_repechage_page',[
            "aus" => $au,
            "parcours" => $parcours
        ]);
    }

    public function get_liste_repechage(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: récupération de la liste de repêchage impossible car aucune opération de génération des résultats annuels n'a été trouvée ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_repechage != null){
                //récupérer les résultats
                try {
                    $resultats = Operation_sur_au::get_liste_repechage($id_au, $id_parcours, $id_niveau);
                    $au = AU::find($id_au);
                    $parcours = Parcours::find($id_parcours);
                    $niveau = Niveau::find($id_niveau);
                    //passer les résultats à la vue
                    return view('notes/liste_repechage',[
                        "resultats" => $resultats,
                        "au" => $au,
                        "parcours" => $parcours,
                        "niveau" => $niveau,
                    ]);
                } catch (\Exception $th) {
                    return redirect()->back()->with("error", $th->getMessage());

                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération de la liste de repêchage impossible car les résultats annuels avant repêchage n'ont pas encore été générés ");
            }
        }

    }

    // lecture des resultats de l'au avant le repechage
    public function get_resultats_avant_repechage_page(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/get_resultats_avant_repechage_page',[
            "aus" => $au,
            "parcours" => $parcours
        ]);

    }

    public function get_resultats_avant_repechage(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
        ]);
        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');

        $au_courant = AU::get_au_en_cours();

        $operations_par_au = Operation_sur_au::get_operation_by_id_au($au_courant->id_au);
        if(empty($operations_par_au)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels avant repêchage impossible car aucune opération de génération des résultats annuels n'a été trouvée ");
        }
        else{
            $operation = $operations_par_au[0];
            if($operation->date_resultats_avant_repechage != null){
                //récupérer les résultats
                try {
                    $resultats = Operation_sur_au::get_resultats_avant_repechage($id_au, $id_parcours, $id_niveau);
                    //passer les résultats à la vue
                    return view('notes/resultats_avant_repechage',[
                        "resultats" => $resultats,
                    ]);
                } catch (\Exception $th) {
                    return redirect()->back()->with("error", $th->getMessage());

                }
            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération des résultats annuels avant repêchage impossible car ils n'ont pas encore été générés ");
            }
        }
    }

    // génération des résultats sur toute l'AU
    public function generer_resultats_au(){
        $au_courant = AU::get_au_en_cours();

        //CONTROLE: resultats de toutes les évaluations de l'AU générés et résultats avant repechage non générés
        //examens à faire sur l'A.U.
        $examens = AU::get_liste_evaluations($au_courant->id_au);

        //operations sur les examens_individuels
        $operations_sur_examen = operation_sur_examen::get_operations_sur_eval($au_courant->id_au);

        if(empty($operations_sur_examen)){
            return response()->json(["error"=>"ERREUR: génération des résultats sur l'A.U. impossible car aucune opération d'ouverture et de cloture des saisies et vérification des notes/en-têtes d'examens n'a été trouvée: "], 422);
        }
        else if(count($operations_sur_examen) < count($examens)){
            return response()->json(["error"=>"ERREUR: génération des résultats sur l'A.U. impossible car la totalité des évaluations n'a pas encore été traitée "], 422);
        }
        else if(count($operations_sur_examen) == count($examens)){
            //boucler sur les operation et vérifier que les résultats individuels des evaluations sont générés
            foreach($operations_sur_examen as $operation){
                if($operation->date_resultats == null)
                    return response()->json(["error"=>"ERREUR: génération des résultats sur l'A.U. impossible car certains resultats d'examens n'ont pas encore été générés: ".$operation->nom_session_examen], 422);
            }
            $operations_sur_au = Operation_sur_au::get_operation_by_id_au($au_courant->id_au);
            if(empty($operations_sur_au)){
                Operation_sur_au::generer_resultats_au($au_courant->id_au);
                $user = Auth::user();
                Operation_sur_au::verrouiller_resultats($au_courant->id_au, $user->id_user);
                return response()->json(["message"=>"Génération des résultats avant le repêchage effectuée"], 200);
            }
            else if($operations_sur_au[0]->date_resultats_avant_repechage != null){
                return response()->json(["error"=>"ERREUR: génération des résultats sur l'A.U. selectionnée déjà effectuée"], 422);
            }

        }

    }

    public function generer_resultats_au_page(){
        $au_courant = AU::get_au_en_cours();
        //récupérer les opérations sur l'au
        $operation = Operation_sur_au::get_operation_by_id_au($au_courant->id_au);
        return view('notes/controle_resultats_au', ['operation'=>$operation, 'au'=>$au_courant]);
    }

    //génération des résultats des évaluations individuelles

    public function down_resultats_all(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_examen_par_au' => ['required','numeric', 'exists:examen_par_au,id_examen_par_au']
        ]);

        $id_au = $request->input('id_au');
        $id_examen_par_au = $request->input('id_examen_par_au');

        $parcours = Parcours::all();
        $eval = AU::get_examen_by_id($id_examen_par_au);
        $au = AU::find($id_au);

        $resultats = [];
        $sous_titres = [];
        $sous_titre;

        $niveaux;
        $titre = "resultats"."_".$au->intitule."_".$eval->nom_session_examen;

        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($id_examen_par_au);

        if(empty($operations)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car aucune opération d'ouverture et de cloture des saisies et vérification des notes/en-têtes n'a été trouvée ");
        }
        else{
            $operation = $operations[0];
            if($operation->date_resultats != null){
                foreach($parcours as $parcour){
                    $niveaux = Niveau::get_niveaux_parcours($parcour->id_parcours);
                    foreach($niveaux as $niveau){
                        $res = operation_sur_examen::get_resultats_eval($id_au, $parcour->id_parcours, $niveau->id_niveau, $id_examen_par_au);
                        if( empty($res) == false){
                            $resultats[] = $res;
                            $sous_titre = [];
                            $sous_titre[] = "Résusltats ".$eval->nom_session_examen;
                            $sous_titre[] = $niveau->nom_niveau." - ".$parcour->nom_parcours;
                            $sous_titre[] = "Année Universitaire ".$au->intitule;
                            $sous_titres[] = $sous_titre;

                        }

                    }
                }

                return Excel::download(new AllResultsExport($resultats, $sous_titres), $titre.'.xlsx');
            }
            else{
                return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car ils n'ont pas encore été générés ");

            }

        }


    }

    public function down_resultats_specifique(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
            'id_examen_par_au' => ['required','numeric', 'exists:examen_par_au,id_examen_par_au']
        ]);

        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');
        $id_examen_par_au = $request->input('id_examen_par_au');

        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($id_examen_par_au);
        if(empty($operations)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car aucune opération d'ouverture et de cloture des saisies et vérification des notes/en-têtes n'a été trouvée ");
        }
        else{
            $operation = $operations[0];
            if($operation->date_resultats != null){
                $resultats = operation_sur_examen::get_resultats_eval($id_au, $id_parcours, $id_niveau, $id_examen_par_au);
                $au = AU::find($id_au);
                $parcours = Parcours::find($id_parcours);
                $niveau = Niveau::find($id_niveau);
                $eval = AU::get_examen_by_id($id_examen_par_au);

                $sous_titres = [];
                $sous_titres[] = "Résusltats ".$eval->nom_session_examen;
                $sous_titres[] = $niveau->nom_niveau." - ".$parcours->nom_parcours;
                $sous_titres[] = "Année Universitaire ".$au->intitule;

                $titre = "resultats_".$parcours->nom_parcours." - ".$niveau->nom_niveau."_".$au->intitule."_".$eval->nom_session_examen;

                return Excel::download(new ResultatsEvalExport($resultats, $sous_titres), $titre.'.xlsx');

            }
            else if($operation->date_cloture_verification_note == null){
                return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car ils n'ont pas encore été générés ");

            }
        }

    }

    public function down_resultats_page(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/down_resultats_page',[
            "aus" => $au,
            "parcours" => $parcours
        ]);

    }

    public function get_resultats_page(){
        $au = AU::all();
        $parcours = Parcours::all();
        return view('notes/get_resultats_page',[
            "aus" => $au,
            "parcours" => $parcours
        ]);

    }

    public function get_resultats_eval(Request $request){
        $request->validate([
            'id_au' => ['required','numeric', 'exists:au,id_au'],
            'id_parcours' => ['required','numeric', 'exists:parcours,id_parcours'],
            'id_niveau' => ['required','numeric', 'exists:niveau,id_niveau'],
            'id_examen_par_au' => ['required','numeric', 'exists:examen_par_au,id_examen_par_au']
        ]);
        $id_au = $request->input('id_au');
        $id_parcours = $request->input('id_parcours');
        $id_niveau = $request->input('id_niveau');
        $id_examen_par_au = $request->input('id_examen_par_au');

        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($id_examen_par_au);
        if(empty($operations)){
            return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car aucune opération d'ouverture et de cloture des saisies et vérification des notes/en-têtes n'a été trouvée ");
        }
        else{
            $operation = $operations[0];
            if($operation->date_resultats != null){
                $resultats_eval = operation_sur_examen::get_resultats_eval_back($id_au, $id_parcours, $id_niveau, $id_examen_par_au);
                $au = AU::find($id_au);
                $parcours = Parcours::find($id_parcours);
                $niveau = Niveau::find($id_niveau);
                $eval = AU::get_examen_by_id($id_examen_par_au);

                return view('notes/resultats_eval',[
                    "resultats" => $resultats_eval,
                    "au" => $au,
                    "parcours" => $parcours,
                    "niveau" => $niveau,
                    "eval" => $eval
                ]);
            }
            else if($operation->date_resultats == null){
                return redirect()->back()->with("error", "ERREUR: récupération des résultats impossible car ils n'ont pas encore été générés ");

            }
        }
    }

    public function generer_resultats(Request $request){
        $request->validate([
            'id_examen_par_au' => ['required','numeric', 'exists:examen_par_au,id_examen_par_au']
        ]);
        $id_examen_par_au = $request->input('id_examen_par_au');
        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($id_examen_par_au);
        $id_user = Auth::user()->id_user;

        if(empty($operations)){
            return response()->json(["error"=>"ERREUR: génération des résultats impossible car aucune opération d'ouverture et de cloture des saisies et vérification des notes/en-têtes n'a été trouvée: "], 422);
        }
        else{
            $operation = $operations[0];
            if($operation->date_cloture_verification_note != null && $operation->date_cloture_verification_en_tete != null && $operation->date_resultats == null){
                $session = Operation_sur_examen::get_session_examen($id_examen_par_au);

                if($session->type_session == "eval"){
                    //Récupérer les éventuelles anomalies(oubli de saisie d'en tete ou  de note)
                    $anomalies = Operation_sur_examen::get_anomalies_saisie($id_examen_par_au);
                    if(empty($anomalies[0]) == false || empty($anomalies[1]) == false){
                        //afficher ces anomalies
                        return view('notes/anomalies_note', ['anomalies' =>$anomalies]);
                    }
                    else{
                        //absence d'anomalie ->génération des résultat
                        Operation_sur_examen::remplir_note_eval($id_examen_par_au);
                        Operation_sur_examen::verrouiller_resultats($id_examen_par_au, $id_user);
                        return response()->json(["message"=>"Génération des résultats effectuée"], 200);

                    }
                }
                else if($session->type_session == "repe"){
                    $au = AU::get_au_en_cours();
                    // CAS DE LA SESSION DE REPECHAGE
                    $operations_par_au = Operation_sur_au::get_operation_by_id_au($au->id_au);
                    if(empty($operations_par_au)){
                        return response()->json(["error"=>"ERREUR: génération des résultats de repêchage impossible car aucune opération de génération des résultats annuels n'a été trouvée "]);
                    }
                    else{
                        $operation = $operations_par_au[0];
                        if($operation->date_resultats_avant_repechage != null){
                            $anomalies = Operation_sur_examen::get_anomalies_saisie($id_examen_par_au);
                            if(empty($anomalies[0]) == false || empty($anomalies[1]) == false){
                                //afficher ces anomalies
                                return view('notes/anomalies_note', ['anomalies' =>$anomalies]);
                            }
                            else{
                                //absence d'anomalie -> génération des résultats


                                    Operation_sur_au::generer_resultats_avant_deliberation($au->id_au);
                                    Operation_sur_examen::verrouiller_resultats($id_examen_par_au, $id_user);
                                    Operation_sur_au::verrouiller_resultats_avant_deliberation($au->id_au, $id_user);
                                    return response()->json(["message"=>"Génération des résultats effectuée"], 200);



                            }
                        }
                        else{
                            return response()->json(["error"=>"ERREUR: génération des résultats de repêchage impossible car les résultats avant repêchage n'ont pas encore été générés "]);

                        }
                    }



                }
                else{
                    // cas du concours PACES qui est géré par une autres application
                    return response()->json(["error"=>"ERREUR: La génération des résultats des examens de type: ".$type_session->type_session."ne sont  pas pris en charge"], 422);
                }
            }
            else if($operation->date_cloture_verification_note == null){
                return response()->json(["error"=>"ERREUR: génération des résultats impossible car la vérification des notes n'est pas encore cloturée "], 422);

            }
            else if($operation->date_cloture_verification_en_tete == null){
                return response()->json(["error"=>"ERREUR: génération des résultats impossible car la vérification des en-têtes n'est pas encore cloturée "], 422);
            }
            else if($operation->date_resultats != null){
                return response()->json(["error"=>"ERREUR: génération des résultats impossible car elle a déjà été effectuée pour cet examen "], 422);
            }
        }


    }

    public function controle_resultats(){
        //recupération des examens
        try {
            $examens = AU::get_liste_examens();
            return view('notes/controle_resultats',['examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/controle_resultats',['error'=>$th->getMessage()]);
        }
    }

    // statistiques sur la vérification des entetes
    public function get_stats_verification_entete(Request $request){
        $request->validate([
            'id_ue_ec' => ['required','numeric', 'exists:ue_ec_parcours_niveau_au,id_ue_ec']
        ]);
        $id_ue_ec = $request->input('id_ue_ec');

        try {
            //récupérer le nombre des inscrits
            $inscrits = Operation_sur_examen::get_nbr_inscrits($id_ue_ec);

            // récupérer le nombre d'en-têtes déjà vérifiées
            $nbr_verifies = Operation_sur_examen::get_nbr_verifies_entete($id_ue_ec);

            //calculer le nombre de copies à encore enregistrer
            $nbr_restants = $inscrits - $nbr_verifies;

            return response()->json([
                "inscrits" => $inscrits,
                "nbr_enregistres" => $nbr_verifies,
                "nbr_restants" => $nbr_restants
            ], 200);

        } catch (\Exception $th) {
            return response()->json(["errors"=>["autres"=> $th->getMessage()]], 500);
        }



    }


    // statistiques sur la vérification des notes
    public function get_stats_verification_note(Request $request){
        $request->validate([
            'id_ue_ec' => ['required','numeric', 'exists:ue_ec_parcours_niveau_au,id_ue_ec']
        ]);
        $id_ue_ec = $request->input('id_ue_ec');

        try {
            //récupérer le nombre des inscrits
            $inscrits = Operation_sur_examen::get_nbr_inscrits($id_ue_ec);

            // récupérer le nombre des copies déjà enregistrées
            $nbr_enregistres = Operation_sur_examen::get_nbr_verifies_note($id_ue_ec);

            //calculer le nombre de copies à encore enregistrer
            $nbr_restants = $inscrits - $nbr_enregistres;

            return response()->json([
                "inscrits" => $inscrits,
                "nbr_enregistres" => $nbr_enregistres,
                "nbr_restants" => $nbr_restants
            ], 200);

        } catch (\Exception $th) {
            return response()->json(["errors"=>["autres"=> $th->getMessage()]], 500);
        }



    }


    // statistiques sur la saisie des en-têtes
    public function get_stats_saisie_entete(Request $request){
        $request->validate([
            'id_ue_ec' => ['required','numeric', 'exists:ue_ec_parcours_niveau_au,id_ue_ec']
        ]);
        $id_ue_ec = $request->input('id_ue_ec');

        try {
            //récupérer le nombre des inscrits
            $inscrits = Operation_sur_examen::get_nbr_inscrits($id_ue_ec);

            // récupérer le nombre des copies déjà enregistrées
            $nbr_enregistres = Operation_sur_examen::get_nbr_enregistres_entete($id_ue_ec);

            //calculer le nombre de copies à encore enregistrer
            $nbr_restants = $inscrits - $nbr_enregistres;

            return response()->json([
                "inscrits" => $inscrits,
                "nbr_enregistres" => $nbr_enregistres,
                "nbr_restants" => $nbr_restants
            ], 200);

        } catch (\Exception $th) {
            return response()->json(["errors"=>["autres"=> $th->getMessage()]], 500);
        }



    }


    //statistiques sur la saisie des notes
    public function get_stats_saisie_note(Request $request){
        $request->validate([
            'id_ue_ec' => ['required','numeric', 'exists:ue_ec_parcours_niveau_au,id_ue_ec']
        ]);
        $id_ue_ec = $request->input('id_ue_ec');

        try {
            //récupérer le nombre des inscrits
            $inscrits = Operation_sur_examen::get_nbr_inscrits($id_ue_ec);

            // récupérer le nombre des copies déjà enregistrées
            $nbr_enregistres = Operation_sur_examen::get_nbr_enregistres_note($id_ue_ec);

            //calculer le nombre de copies à encore enregistrer
            $nbr_restants = $inscrits - $nbr_enregistres;

            return response()->json([
                "inscrits" => $inscrits,
                "nbr_enregistres" => $nbr_enregistres,
                "nbr_restants" => $nbr_restants
            ], 200);

        } catch (\Exception $th) {
            return response()->json(["errors"=>["autres"=> $th->getMessage()]], 500);
        }



    }


    //vérification des en-têtes
    public function modifier_matricule(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
            'matricule_modifie' => ['bail','required','numeric', 'exists:etudiants,im']
        ]);
        $barcode = $request->input('barcode');
        $matricule = $request->input('matricule_modifie');

        $au_courant = AU::get_au_en_cours();

        //valider l'inscription de l'étudiant au parcours, niveau associé au code barre;
        $est_inscrit_a_ec = Inscription::verifier_inscription_ue_ec($matricule, $barcode, $au_courant->id_au);
        //return response()->json(["errors"=>["barcode"=>$barcode, "matricule"=>$matricule, "id_au"=>$au_courant->id_au]], 422);
        if($est_inscrit_a_ec == false)
            return response()->json(["errors"=>["barcode"=>'ERREUR: cet étudiant a reçu le code-barres d\'un parcours, d\'une mention ou d\'un niveau auquel il n\'est pas inscrit']], 422);

        $values = explode("-", $barcode);
        $id_ue_ec = $values[0];

        //d'après la règle de validation, c'est un ue_ec existant
        $ue_ecs = Unite_enseignement::get_ue_ec_by_id($id_ue_ec);
        $ue_ec = $ue_ecs[0];

        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($ue_ec->id_examen_par_au);


        //MODIFICATION SELON L'OUVERTURE DE LA VERIFICATION DES ENTETES, ET LES AUTORISATIONS
        //aucune operation n'a encore été enregistrée <-> donc pas d'ouverture de vérification et de modification
        if(empty($operations)){
            return response()->json(["errors"=>["autres"=>"Les operations relatifs à cet examen n'ont pas encore été enregistrés"]], 500);
        }
        else{
            $operation = $operations[0];
            //Pour que le code barres soit valide, cette operation doit
            //      --
            //      -- avoir la vérificataion des entetes ouverte et non verrouillée (tout le monde a partir de chef div a accès)
            //          OU
            //      -- avoir la vérification ouverte, verrouillée mais le resultat de l'examen ne doit pas être déjà généré(accès à partir de SP avec authentification contradictoire)

            $user = Auth::user();

            if($operation->date_ouverture_verification_en_tete != null){
                if($operation->date_cloture_verification_en_tete == null){
                    if($user-> role->rang_role >=0){
                        $response = rescue(
                            function() use($barcode, $matricule){
                                Operation_sur_examen::modifier_matricule($barcode, $matricule);
                                return [
                                    ['message'=>'Enregistrement effectué'],
                                    200
                                ];
                            },
                            function(Exception $ex){
                                return [
                                    ["errors"=>["acces"=>$ex->getMessage()]], 422
                                ];

                            },
                            false
                        );

                        return response()->json($response[0], $response[1]);
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                    }
                }
                else{
                    if($operation->date_resultats==null){
                        if($user-> role->rang_role >=40){
                            if(Session::has('user2') && $request->cookie('auth_cont') != null){
                                $user2 = Session::get('user2');
                                if($user2->role->rang_role >=40){
                                    $response = rescue(
                                        function() use($barcode, $matricule){
                                            Operation_sur_examen::modifier_matricule($barcode, $matricule);
                                            return [
                                                ['message'=>'Enregistrement effectué'],
                                                200
                                            ];
                                        },
                                        function(Exception $ex){
                                            return [
                                                ["errors"=>["acces"=>$ex->getMatricule]], 422
                                            ];
                                        },
                                        false
                                    );

                                    return response()->json($response[0], $response[1]);
                                }
                                else{
                                    Session::put('url.intended', route('interface_verification_matricule'));
                                    if($request->expectsJson())
                                        return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                    else return redirect(route('authentification_contradictoire.form'));
                                }
                            }
                            else{
                                Session::put('url.intended', route('interface_verification_matricule'));
                                if($request->expectsJson())
                                    return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                else return redirect(route('authentification_contradictoire.form'));
                            }
                        }
                        else{
                            return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                        }
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé: les résultats de l\'examen correspondant ont déjà été confirmés']], 422);
                    }
                }
            }
            else{
                return response()->json(["errors"=>["acces"=>'Vérification des en-têtes non encore ouverte pour l\'évaluation correspondante']], 422);
            }

        }

    }

    public function get_matricule(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
        ]);
        $barcode = $request->input('barcode');
        try {
            $matricule = Operation_sur_examen::get_matricule($barcode);
            return response()->json(['matricule'=>$matricule], 200);

        } catch (Exception $th) {
            return response()->json(['errors'=>['acces'=>$th->getMessage()]], 500);
        }
    }

    public function verrouiller_verification_entete(Request $request){
        $request->validate([
            'id_examen_par_au' => ['bail','required','numeric','exists:examen_par_au,id_examen_par_au'],
        ]);

        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::verrouiller_verification_entete($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de la vérification effectuée"], 200);
        } catch (Exception $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }

    public function ouvrir_verification_entete(Request $request){
        $request->validate([
            'id_examen_par_au' => ['bail','required','numeric','exists:examen_par_au,id_examen_par_au'],
        ]);

        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::ouvrir_verification_entete($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de la vérification effectuée"], 200);
        } catch (\Exception $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }

    public function controle_verification_entete(){
        //recupération des examens et des opérations
        try {
            $examens = AU::get_liste_examens();
            return view('notes/controle_verification_entete',['examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/controle_verification_entete',['error'=>$th->getMessage()]);
        }
    }

    //saisie des entetes

    public function enregistrer_entete(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
            'matricule' => ['bail','required','string', 'exists:etudiants,im']
        ]);

        $barcode = $request->input('barcode');
        $matricule = $request->input('matricule');

        $au_courant = AU::get_au_en_cours();

        //valider l'inscription de l'étudiant au parcours, niveau associé au code barre;
        $est_inscrit_a_ec = Inscription::verifier_inscription_ue_ec($matricule, $barcode, $au_courant->id_au);
        //return response()->json(["errors"=>["barcode"=>$barcode, "matricule"=>$matricule, "id_au"=>$au_courant->id_au]], 422);
        if($est_inscrit_a_ec == false)
            return response()->json(["errors"=>["barcode"=>'ERREUR: cet étudiant a reçu le code-barres d\'un parcours ou d\'une mention ou d\'un niveau auquel il n\'est pas inscrit']], 422);


        $values = explode("-", $barcode);
        $id_ue_ec = $values[0];

        //d'après la règle de validation, c'est un ue_ec existant
        $ue_ecs = Unite_enseignement::get_ue_ec_by_id($id_ue_ec);

        $ue_ec = $ue_ecs[0];
        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($ue_ec->id_examen_par_au);

        //aucune operation n'a encore été enregistrée <->saisie non encore ouverte
        if(empty($operations)){
            return response()->json(["errors"=>["barcode"=>'Saisie des en-têtes non encore ouverte pour l\'évaluation correspondante']], 422);
        }
        else{
            $operation = $operations[0];
            //Pour que le code barres soit valide, cette operation doit
            //      -- être ouverte et non verrouillée (tout le monde a partir de chef div a accès)
            //          OU
            //      -- etre ouverte, verrouillée mais le resultat de l'examen ne doit pas être déjà généré(accès à partir de SP avec authentification contradictoire)
            $user = Auth::user();

            if($operation->date_ouverture_saisie_en_tete != null){
                if($operation->date_cloture_saisie_en_tete == null){
                    if($user-> role->rang_role >=0){
                        $response = rescue(
                            function() use($barcode, $matricule){
                                Operation_sur_examen::enregistrer_entete($barcode, $matricule);
                                return [
                                    ['message'=>'Enregistrement effectué'],
                                    200
                                ];
                            },
                            function(Exception $ex){
                                if($ex instanceof UniqueConstraintViolationException){
                                    return [
                                        ["errors"=>["acces"=>"ERREUR: code-barres déjà enregistré ou matricule déjà enregistré pour cet élément constitutif"]],
                                        500
                                    ];
                                }
                                else{
                                    return [
                                        ["errors"=>["acces"=>$ex->getMessage()]],
                                        500
                                    ];
                                }
                            },
                            false
                        );

                        return response()->json($response[0], $response[1]);
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                    }
                }
                else{
                    if($operation->date_resultats==null){
                        if($user-> role->rang_role >=40){
                            if(Session::has('user2') && $request->cookie('auth_cont') != null){
                                $user2 = Session::get('user2');
                                if($user2->role->rang_role >=40){
                                    $response = rescue(
                                        function() use($barcode, $matricule){
                                            Operation_sur_examen::enregistrer_entete($barcode, $matricule);
                                            return [
                                                ['message'=>'Enregistrement effectué'],
                                                200
                                            ];
                                        },
                                        function(Exception $ex){
                                            if($ex instanceof UniqueConstraintViolationException){
                                                return [
                                                    ["errors"=>["acces"=>"ERREUR: code-barres déjà enregistré ou matricule déjà enregistré pour cet élément constitutif"]],
                                                    500
                                                ];
                                            }
                                            else{
                                                return [
                                                    ["errors"=>["acces"=>$ex->getMessage()]],
                                                    500
                                                ];
                                            }
                                        },
                                        false
                                    );

                                    return response()->json($response[0], $response[1]);
                                }
                                else{
                                    Session::put('url.intended', route('interface_saisie_entete'));
                                    if($request->expectsJson())
                                        return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                    else return redirect(route('authentification_contradictoire.form'));
                                }
                            }
                            else{
                                Session::put('url.intended', route('interface_saisie_entete'));
                                if($request->expectsJson())
                                    return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                else return redirect(route('authentification_contradictoire.form'));                            }
                        }
                        else{
                            return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                        }
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé: aucune modification ne peut plus être faite après la confirmation des résultats']], 422);
                    }
                }
            }
            else{
                return response()->json(["errors"=>["acces"=>'Saisie des en-têtes non_encore_ouverte pour l\'évaluation correspondante']], 422);
            }

        }
    }

    public function verrouiller_saisie_entete(Request $request){
        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::verrouiller_saisie_entete($id_examen_par_au, $id_user);
            return response()->json(['message'=>"verrouillage de saisie des en-têtes effectuée"], 200);
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }

    public function ouvrir_saisie_entete(Request $request){
        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::ouvrir_saisie_entete($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de la saisie des entetes effectuée"], 200);
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }


    public function controle_saisie_entete(){
        //recupération des examens et des opérations
        try {
            $examens = AU::get_liste_examens();
            return view('notes/controle_saisie_entete',['examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/controle_saisie_entete',['error'=>$th->getMessage()]);
        }
    }

    //vérification des notes

    public function modifier_note(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
            'note_modifiee' => ['bail','required','numeric', new IsNoteValide]
        ]);
        $barcode = $request->input('barcode');
        $note = $request->input('note_modifiee');

        $values = explode("-", $barcode);
        $id_ue_ec = $values[0];

        //d'après la règle de validation, c'est un ue_ec existant
        $ue_ecs = Unite_enseignement::get_ue_ec_by_id($id_ue_ec);
        $ue_ec = $ue_ecs[0];

        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($ue_ec->id_examen_par_au);

        //aucune operation n'a encore été enregistrée <-> donc pas d'ouverture de vérification et de modification
        //MODIFICATION SELON L'OUVERTURE DE LA VERIFICATION, ET LES AUTORISATIONS
        if(empty($operations)){
            return response()->json(["errors"=>["autres"=>"Les operations relatifs à cet examen n'ont pas encore été enregistrés"]], 500);
        }
        else{
            $operation = $operations[0];
            //Pour que le code barres soit valide, cette operation doit
            //      --
            //      -- avoir la vérificataion des notes ouverte et non verrouillée (tout le monde a partir de chef div a accès)
            //          OU
            //      -- avoir la vérification ouverte, verrouillée mais le resultat de l'examen ne doit pas être déjà généré(accès à partir de SP avec authentification contradictoire)

            $user = Auth::user();

            if($operation->date_ouverture_verification_note != null){
                if($operation->date_cloture_verification_note == null){
                    if($user-> role->rang_role >=0){
                        $response = rescue(
                            function() use($barcode, $note){
                                Operation_sur_examen::modifier_note($barcode, $note);
                                return [
                                    ['message'=>'Enregistrement effectué'],
                                    200
                                ];
                            },
                            function(Exception $ex){
                                return [
                                    ["errors"=>["acces"=>$ex]], 422
                                ];

                            },
                            false
                        );

                        return response()->json($response[0], $response[1]);
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                    }
                }
                else{
                    if($operation->date_resultats==null){
                        if($user-> role->rang_role >=40){
                            if(Session::has('user2') && $request->cookie('auth_cont') != null){
                                $user2 = Session::get('user2');
                                if($user2->role->rang_role >=40){
                                    $response = rescue(
                                        function() use($barcode, $note){
                                            Operation_sur_examen::modifier_note($barcode, $note);
                                            return [
                                                ['message'=>'Enregistrement effectué'],
                                                200
                                            ];
                                        },
                                        function(Exception $ex){
                                            return [
                                                ["errors"=>["acces"=>$ex]], 422
                                            ];
                                        },
                                        false
                                    );

                                    return response()->json($response[0], $response[1]);
                                }
                                else{
                                    Session::put('url.intended', route('interface_verification_notes'));
                                    if($request->expectsJson())
                                        return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                    else return redirect(route('authentification_contradictoire.form'));
                                }
                            }
                            else{
                                Session::put('url.intended', route('interface_verification_notes'));
                                if($request->expectsJson())
                                    return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                else return redirect(route('authentification_contradictoire.form'));
                            }
                        }
                        else{
                            return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                        }
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé: les résultats de l\'examen correspondant ont déjà été confirmés']], 422);
                    }
                }
            }
            else{
                return response()->json(["errors"=>["acces"=>'Vérification non_encore_ouverte pour l\'évaluation correspondante']], 422);
            }

        }

    }


    public function get_note(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
        ]);
        $barcode = $request->input('barcode');
        try {
            $note = Operation_sur_examen::get_note($barcode);
            return response()->json(['note'=>$note], 200);

        } catch (Exception $th) {
            return response()->json(['errors'=>['acces'=>$th->getMessage()]], 500);
        }
    }

    public function verrouiller_verification_note(Request $request){
        $request->validate([
            'id_examen_par_au' => ['bail','required','numeric','exists:examen_par_au,id_examen_par_au'],
        ]);

        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::verrouiller_verification_note($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de la vérification effectuée"], 200);
        } catch (Exception $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }

    public function ouvrir_verification_note(Request $request){
        $request->validate([
            'id_examen_par_au' => ['bail','required','numeric','exists:examen_par_au,id_examen_par_au'],
        ]);

        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::ouvrir_verification_note($id_examen_par_au, $id_user);
            return response()->json(['message'=>"ouverture de la vérification effectuée"], 200);
        } catch (\Exception $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }

    }

    public function controle_verification_note(){
        //recupération des examens et des opérations
        try {
            $examens = AU::get_liste_examens();
            return view('notes/controle_verification_notes',['examens'=>$examens]);
        } catch (\Exception $th) {
            return view('notes/controle_verification_notes',['error'=>$th->getMessage()]);
        }
    }

    //saisie des notes

    public function verrouiller_saisie_note(Request $request){
        $id_examen_par_au = $request->input('id_examen_par_au');
        $id_user = Auth::user()->id_user;
        try {
            Operation_sur_examen::verrouiller_saisie_note($id_examen_par_au, $id_user);
            return response()->json(['message'=>"verrouillage de saisie effectuée"], 200);
        } catch (\Throwable $th) {
            return response()->json(['message'=>$th->getMessage()], 500);
        }

    }

    public function enregistrer_note(Request $request){
        $request->validate([
            'barcode' => ['bail','required','string', new IsBarCodeValide],
            'note' => ['bail','required','numeric', new IsNoteValide]
        ]);
        $barcode = $request->input('barcode');
        $note = $request->input('note');

        $values = explode("-", $barcode);
        $id_ue_ec = $values[0];

        //d'après la règle de validation, c'est un ue_ec existant
        $ue_ecs = Unite_enseignement::get_ue_ec_by_id($id_ue_ec);

        $ue_ec = $ue_ecs[0];
        $operations = Operation_sur_examen::get_operation_by_id_examen_par_au($ue_ec->id_examen_par_au);

        //aucune operation n'a encore été enregistrée <->saisie non encore ouverte
        if(empty($operations)){
            return response()->json(["errors"=>["barcode"=>'Saisie non encore ouverte pour l\'évaluation correspondante']], 422);
        }
        else{
            $operation = $operations[0];
            //Pour que le code barres soit valide, cette operation doit
            //      -- être ouverte et non verrouillée (tout le monde a partir de chef div a accès)
            //          OU
            //      -- etre ouverte, verrouillée mais le resultat de l'examen ne doit pas être déjà généré(accès à partir de SP avec authentification contradictoire)
            $user = Auth::user();

            if($operation->date_ouverture_saisie_note != null){
                if($operation->date_cloture_saisie_note == null){
                    if($user-> role->rang_role >=0){
                        $response = rescue(
                            function() use($barcode, $note){
                                Operation_sur_examen::enregistrer_note($barcode, $note);
                                return [
                                    ['message'=>'Enregistrement effectué'],
                                    200
                                ];
                            },
                            function(Exception $ex){
                                if($ex instanceof UniqueConstraintViolationException){
                                    return [
                                        ['message'=> 'Ce code barre a déjà été enregistré'],
                                        500
                                    ];
                                }
                                else{
                                    return [
                                        ['message'=> $ex->getMessage()],
                                        500
                                    ];
                                }
                            },
                            false
                        );

                        return response()->json($response[0], $response[1]);
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                    }
                }
                else{
                    if($operation->date_resultats==null){
                        if($user-> role->rang_role >=40){
                            if(Session::has('user2') && $request->cookie('auth_cont') != null){
                                $user2 = Session::get('user2');
                                if($user2->role->rang_role >=40){
                                    $response = rescue(
                                        function() use($barcode, $note){
                                            Operation_sur_examen::enregistrer_note($barcode, $note);
                                            return [
                                                ['message'=>'Enregistrement effectué'],
                                                200
                                            ];
                                        },
                                        function(Exception $ex){
                                            if($ex instanceof UniqueConstraintViolationException){
                                                return [
                                                    ['message'=> 'Ce code barre a déjà été enregistré'],
                                                    500
                                                ];
                                            }
                                            else{
                                                return [
                                                    ['message'=> $ex->getMessage()],
                                                    500
                                                ];
                                            }
                                        },
                                        false
                                    );

                                    return response()->json($response[0], $response[1]);
                                }
                                else{
                                    Session::put('url.intended', route('interface_saisie_notes'));
                                    if($request->expectsJson())
                                        return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                    else return redirect(route('authentification_contradictoire.form'));
                                }
                            }
                            else{
                                Session::put('url.intended', route('interface_saisie_notes'));
                                if($request->expectsJson())
                                    return response()->json(['message'=>'authentification_contradictoire_necessaire'], 401);
                                else return redirect(route('authentification_contradictoire.form'));
                            }
                        }
                        else{
                            return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                        }
                    }
                    else{
                        return response()->json(["errors"=>["acces"=>'Accès refusé']], 422);
                    }
                }
            }
            else{
                return response()->json(["errors"=>["acces"=>'Saisie non_encore_ouverte pour l\'évaluation correspondante']], 422);
            }

        }
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
