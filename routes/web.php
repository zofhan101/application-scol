<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\AU\AUcontroller;
use App\Http\Controllers\inscriptions\Inscription_import_controller;
use App\Http\Middleware\EnsureIsAdmin;
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
    Route::post('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes']);
    Route::get('down_modele_selectionnes',[Inscription_import_controller::class,'modele_selectionnes']);
    Route::get('import_selectionnes',[Inscription_import_controller::class,'import_selectionnes_page'])->name('import_selectionnes');
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
