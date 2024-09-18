<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\AU\AUcontroller;
use App\Http\Controllers\inscriptions\Inscription_import_controller;
use App\Http\Controllers\inscriptions\Inscription_controller;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\AU\CheckOpenedAU;
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
// route nécessitant authentification et au ouverte
Route::middleware('auth',CheckOpenedAU::class)->group(function(){

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
    Route::get('check_admission',function(){ return view('inscriptions/check_admission'); });

    // import
    Route::post('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes']);
    Route::get('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes_page'])->name('import_selectionnes');
});


//routes nécessitant authentification
Route::middleware('auth')->group(function(){
    Route::get('au_fermee',function(){
        return view('AU/au_fermee');
    });
    Route::get('down_modele_selectionnes',[Inscription_import_controller::class,'modele_selectionnes']);
    Route::get('accueil',function(){
        if(Auth::user()->role->nom_role === 'admin')
            return view ('app/admin_welcome');
        else
            return view('app/welcome');
    })->name('accueil');
});

// routes nécessitant connexion et admin
Route::middleware('auth', EnsureIsAdmin::class)->group(function () {
    // années universitaires
    Route::get('ouvrir_au',[AUcontroller::class,'auForm'])->name('au_form');
    Route::post('ouvrir_au',[AUcontroller::class,'ouvrir_au'])->name('ouvrir_au');
    Route::get('au_en_cours',[AUcontroller::class,'au_en_cours'])->name('au_en_cours');
    Route::post('cloture_au',[AUcontroller::class,'cloture_au'])->name('cloture_au');

    //utilisateurs
    Route::post('delUser',[UserController::class,'delUser'])->name('delUser');
    Route::get('listeUsers', [UserController::class, 'getAllUsers'])->name('listeUsers');
});


Route::post('authAdmin',[AdminAuthController::class,'login'])->name('authAdmin');

Route::get('/', function () {
    return view('auth/login2');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
