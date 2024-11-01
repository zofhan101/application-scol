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



class NoteController extends Controller
{
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
