<?php

namespace App\Http\Controllers\notes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\notes\operation_sur_resultats;
use App\Models\AU\AU;
use App\Models\notes\Operation_sur_examen;
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




class NoteController extends Controller
{
    //génération des résultats

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
                        $resultats[] = operation_sur_examen::get_resultats_eval($id_au, $parcour->id_parcours, $niveau->id_niveau, $id_examen_par_au);

                        $sous_titre = [];
                        $sous_titre[] = "Résusltats ".$eval->nom_session_examen;
                        $sous_titre[] = $niveau->nom_niveau." - ".$parcour->nom_parcours;
                        $sous_titre[] = "Année Universitaire ".$au->intitule;
                        $sous_titres[] = $sous_titre;

                    }
                }

                return Excel::download(new AllResultsExport($resultats, $sous_titres), $titre.'.xlsx');
            }
            else if($operation->date_cloture_verification_note == null){
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
                        $id_user = Auth::user()->id_user;
                        Operation_sur_examen::remplir_note_eval($id_examen_par_au);
                        Operation_sur_examen::verrouiller_resultats($id_examen_par_au, $id_user);
                        return response()->json(["message"=>"Génération des résultats effectuée"], 200);

                    }
                }
                else if($session->type_session == "repe"){

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
