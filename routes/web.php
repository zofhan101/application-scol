<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\AU\AUcontroller;
use App\Http\Controllers\inscriptions\Inscription_import_controller;
use App\Http\Controllers\inscriptions\Inscription_controller;
use App\Http\Controllers\inscriptions\EtudiantController;
use App\Http\Controllers\inscriptions\TransfertController;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureIsChefDiv;
use App\Http\Middleware\EnsureIsSP;
use App\Http\Middleware\EnsureIsChefDivScol;
use App\Http\Middleware\AU\CheckOpenedAU;
use App\Http\Middleware\notes\CheckOuvertureSaisieNote;
use App\Http\Middleware\notes\CheckOuvertureSaisieEntete;
use App\Http\Controllers\UE\UEController;
use App\Http\Controllers\notes\NoteController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//routes nécessitant authentification
Route::middleware('auth')->group(function(){
        Route::get('acces_refuse', function(){ return view('acces_refuse');})->name('acces_refuse');

        //PROFILE
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


        Route::get('au_fermee',function(){
            return view('AU/au_fermee');
        })->name('au_fermee');

        Route::get('accueil',function(){
            return view('app/welcome');
        })->name('accueil');

        //authentification contradictoire

        Route::post('auth/authentification_contradictoire.controller',[AdminAuthController::class,'authentification_contradictoire'])->name('authentification_contradictoire.controller');
        Route::get('auth/authentification_contradictoire.form',function(){ return view('auth/authentification_contradictoire/login2'); })->name('authentification_contradictoire.form');

        //accèes à l'interface saisie des en-tetes
        Route::middleware(CheckOpenedAU::class, CheckOuvertureSaisieEntete::class)->group(function () {
            Route::get('notes/interface_saisie_entete',function(){ return view('notes/interface_saisie_entete');})->name('interface_saisie_entete');

        });

        //accèes à l'interface saisie des notes d'examen
        Route::middleware(CheckOpenedAU::class, CheckOuvertureSaisieNote::class)->group(function () {
            Route::get('notes/interface_saisie_notes',function(){ return view('notes/interface_saisie_notes');})->name('interface_saisie_notes');

        });



        //ACCES A PARTIR DE CHEF DE DIVISION
        Route::middleware(EnsureIsChefDiv::class)->group(function () {


            //Mise à jour des données étudiant
            Route::post('etudiant/form_parents',[EtudiantController::class,'form_parents'])->name('form_parents_modif');
            Route::post('etudiant/form_bacc',[EtudiantController::class,'form_bacc'])->name('form_bacc_modif');
            Route::post('etudiant/form_identite',[EtudiantController::class,'form_identite'])->name('form_identite_modif');
            Route::post('etudiant/form_etudiant',[EtudiantController::class,'form_etudiant'])->name('form_etudiant_modif');
            Route::post('etudiant/search_matricule',[EtudiantController::class,'search_etudiant'])->name('maj_etu_search');
            Route::get('etudiant/search_matricule',function(){ return view('etudiants/check_etudiant');})->name('maj_etu_search_form');

            // NECESSITANT AUTHENTIFICATION ET A.U. OUVERTE
            Route::middleware(CheckOpenedAU::class)->group(function(){
                //modification matricule
                Route::post('notes/modifier_matricule',[NoteController::class,'modifier_matricule'])->name('modifier_matricule');

                //vérification matricule
                Route::post('notes/get_matricule',[NoteController::class,'get_matricule'])->name('get_matricule');


                //saisie des entetes
                Route::post('entetes/enregistrer_entete',[NoteController::class,'enregistrer_entete'])->name('enregistrer_entete');

                //vérification des entetes
                Route::get('entetes/interface_verification_entete',function(){ return view('notes/interface_verification_entete'); })->name('interface_verification_entete');

                //modification note
                Route::post('notes/modifier_note',[NoteController::class,'modifier_note'])->name('modifier_note');


                //saisie des notes
                Route::post('notes/enregistrer_note',[NoteController::class,'enregistrer_note'])->name('enregistrer_note');


                //vérification des notes
                Route::post('notes/get_note',[NoteController::class,'get_note'])->name('get_note');
                Route::get('notes/interface_verification_notes',function(){ return view('notes/interface_verification_notes'); })->name('interface_verification_notes');

                //transfert d'étudiant
                Route::post('transfert/autres_inscriptions',[TransfertController::class,'inscription'])->name('autres_inscriptions_transfert');
                Route::get('transfert/autres_inscriptions',function(){ return view('transfert/form_autres_inscriptions'); })->name('autres_inscriptions_f');
                Route::post('transfert/form_parents',[TransfertController::class,'form_parents'])->name('form_parents_transfert');
                Route::get('transfert/form_parents',function(){ return view('transfert/form_parents'); })->name('form_parents_f');
                Route::post('transfert/form_parents',[TransfertController::class,'form_parents'])->name('form_parents_transfert');
                Route::post('transfert/form_bacc',[TransfertController::class,'form_bacc'])->name('form_bacc_transfert');
                Route::get('transfert/form_bacc',[TransfertController::class,'form_bacc_f'])->name('form_bacc_f');
                Route::post('transfert/form_identite',[TransfertController::class,'form_identite'])->name('form_identite_transfert');
                Route::get('transfert/form_identite',[TransfertController::class,'form_identite_f'])->name('form_identite_f');
                Route::post('transfert/form_etudiant',[TransfertController::class,'form_etudiant'])->name('form_etudiant_transfert');
                Route::get('transfert/form_etudiant',function(){ return view('transfert/form_etudiant'); })->name('form_etudiant_f');
                Route::post('transfert/transfert_form',[TransfertController::class,'transfert_etu'])->name('transfert_etu');
                Route::get('transfert/transfert_form',[TransfertController::class,'transfert_etu_form'])->name('transfert_etu_form');



                // Inscriptions
                Route::post('inscription/finaliser',[Inscription_controller::class,'inscription']);
                Route::get('inscription/form_autres_inscriptions',function(){ return view('inscriptions/form_autres_inscriptions'); });
                Route::post('inscription/form_parents',[Inscription_controller::class,'form_parents']);
                Route::get('inscription/form_parents',function(){ return view('inscriptions/form_parents'); });
                Route::post('inscription/form_bacc',[Inscription_controller::class,'form_bacc']);
                Route::get('inscription/form_bacc',[Inscription_controller::class,'form_bacc_page']);
                Route::post('inscription/form_identite',[Inscription_controller::class,'form_identite']);
                Route::get('inscription/form_identite',[Inscription_controller::class,'form_identite_page']);
                Route::get('inscription/form_etudiant',function(){ return view('inscriptions/form_etudiant'); });
                Route::post('inscription/form_etudiant',[Inscription_controller::class,'form_etudiant']);
                Route::post('inscription/parcours',[Inscription_controller::class,'choix_parcours']);
                Route::get('verifier_admission',[Inscription_controller::class,'verifier_admission'])->name('verifier_admission');
                Route::get('check_admission',function(){ return view('inscriptions/check_admission'); })->name('check_admission');

            });

        });

        //ACCES A PARTIR DE CHEF DE DIVISION SCOLARITE
        Route::middleware(EnsureIsChefDivScol::class)->group(function () {

            //liste des inscrits
            Route::post('inscription/liste_inscrits',[Inscription_controller::class,'get_liste_inscrits'])->name('liste_inscrits');
            Route::get('inscription/liste_inscrits',[Inscription_controller::class,'form_au_niveau_parcours'])->name('liste_inscrits_form');


            //attestation d'inscription
            Route::post('inscription/check_inscription_attestation',[Inscription_controller::class,'check_inscription_attestation'])->name('check_inscription_attestation');
            Route::get('inscription/check_inscription_attestation',[Inscription_controller::class,'get_au_fermees'])->name('check_inscription_form_attestation');

            //modèle excel pour l'import des sélectionnés
            Route::get('down_modele_selectionnes',[Inscription_import_controller::class,'modele_selectionnes']);

            // NECESSITANT AUTHENTIFICATION ET A.U. OUVERTE
            Route::middleware(CheckOpenedAU::class)->group(function(){
                //annulation d'inscription
                Route::post('inscription/annuler_inscription',[Inscription_controller::class,'annuler_inscription'])->name('annuler_inscription');
                Route::get('inscription/annuler_inscription',function(){ return view('inscriptions/annuler_inscription'); })->name('annuler_inscription_form');

                // certificat de scolarité
                Route::post('inscription/plutot_attestation_inscription',[Inscription_controller::class,'plutot_attestation_inscription'])->name('plutot_attestation_inscription');
                Route::post('inscription/check_inscription',[Inscription_controller::class,'check_inscription'])->name('check_inscription');
                Route::get('inscription/check_inscription',function(){ return view('inscriptions/check_inscription'); })->name('check_inscription_form');

                // import
                Route::post('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes']);
                Route::get('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes_page'])->name('import_selectionnes_page');

            });


        });

        //ACCES A PARTIR DE SECRETAIRE PRINCIPAL
        Route::middleware(EnsureIsSP::class)->group(function () {

            Route::middleware(CheckOpenedAU::class)->group(function(){

                //ouverture et cloture des vérification des en-têtes
                Route::post('entetes/verrouiller_verification_entete',[NoteController::class,'verrouiller_verification_entete'])->name('verrouiller_verification_entete');
                Route::post('entetes/ouvrir_verification_entete',[NoteController::class,'ouvrir_verification_entete'])->name('ouvrir_verification_entete');
                Route::get('entetes/controle_verification_entete',[NoteController::class,'controle_verification_entete'])->name('controle_verification_entete');

                // ouverture et cloture de saisie d' en-tetes des feuilles de copie
                Route::post('entetes/verrouiller_saisie_entete',[NoteController::class,'verrouiller_saisie_entete'])->name('verrouiller_saisie_entete');
                Route::post('entetes/ouvrir_saisie_entete',[NoteController::class,'ouvrir_saisie_entete'])->name('ouvrir_saisie_entete');
                Route::get('entetes/controle_saisie_entete',[NoteController::class,'controle_saisie_entete'])->name('controle_saisie_entete');


                //ouverture et cloture des vérification des notes saisies
                Route::post('notes/verrouiller_verification_note',[NoteController::class,'verrouiller_verification_note'])->name('verrouiller_verification_note');
                Route::post('notes/ouvrir_verification_note',[NoteController::class,'ouvrir_verification_note'])->name('ouvrir_verification_note');
                Route::get('notes/controle_verification_note',[NoteController::class,'controle_verification_note'])->name('controle_verification_note');

                //ouverture et cloture des saisie des notes d'examen
                Route::post('notes/verrouiller_saisie_note',[NoteController::class,'verrouiller_saisie_note'])->name('verrouiller_saisie_note');
                Route::post('notes/ouvrir_saisie_note',[NoteController::class,'ouvrir_saisie_note'])->name('ouvrir_saisie_note');
                Route::get('notes/controle_saisie_note',[NoteController::class,'controle_saisie_note'])->name('controle_saisie_note');
                Route::post('notes/get_operation_par_examen',[NoteController::class,'get_operation_par_examen'])->name('get_operation_par_examen');
            });
        });

        // ACCES ADMIN AUTHENTIFICATION ET ADMIN
        Route::middleware(EnsureIsAdmin::class)->group(function () {
            // Nécessitant AU ouverte
            Route::middleware(CheckOpenedAU::class)->group(function () {
                //codes barres des feuilles de copie
                Route::post('ue/down_barcode',[UEController::class,'down_barcode'])->name('down_barcode');
                Route::get('ue/codes_barres',[UEController::class,'get_liste_ue_ec_code_barre'])->name('codes_barres');

                //évaluations
                Route::post('au/create_exam',[AUcontroller::class,'create_exam'])->name('create_exam');
                Route::get('au/create_exam',[AUcontroller::class,'create_exam_form'])->name('create_exam_form');

                //unites d'enseignements
                Route::post('ue/supprimer_ue_ec',[UEController::class,'supprimer_ue_ec'])->name('supprimer_ue_ec');
                Route::post('ue/ajouter_ue_ec',[UEController::class,'ajouter_ue_ec'])->name('ajouter_ue_ec');
                Route::post('ue/liste_ue_ec',[UEController::class,'liste_ue_ec'])->name('liste_ue_ec');
                Route::post('ue/liste_ec',[UEController::class,'liste_ec'])->name('liste_ec');
                Route::post('ue/create_ec',[UEController::class,'create_ec'])->name('create_ec');
                Route::post('ue/liste_ue',[UEController::class,'liste_ue'])->name('liste_ue');
                Route::post('ue/create_ue',[UEController::class,'create_ue'])->name('create_ue');
                Route::get('ue/crud_ue', [UEController::class, 'ue_form'])->name('crud_ue');
            });

            // années universitaires
            Route::get('ouvrir_au',[AUcontroller::class,'auForm'])->name('au_form');
            Route::post('ouvrir_au',[AUcontroller::class,'ouvrir_au'])->name('ouvrir_au');
            Route::get('au_en_cours',[AUcontroller::class,'au_en_cours'])->name('au_en_cours');
            Route::post('cloture_au',[AUcontroller::class,'cloture_au'])->name('cloture_au');

            //utilisateurs
            Route::post('delUser',[UserController::class,'delUser'])->name('delUser');
            Route::get('listeUsers', [UserController::class, 'getAllUsers'])->name('listeUsers');
        });

});




Route::post('niveaux_par_parcours',[Inscription_controller::class,'get_niveaux_parcours'])->name('get_niveaux_parcours');

Route::post('authAdmin',[AdminAuthController::class,'login'])->name('authAdmin');

Route::get('/', function () {
    return view('auth/login2');
});

require __DIR__.'/auth.php';
