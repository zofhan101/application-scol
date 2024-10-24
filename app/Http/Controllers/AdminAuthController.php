<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;


class AdminAuthController extends Controller
{
    protected $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function authentification_contradictoire(Request $request){
        $request->validate( [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user2 = $this->authService->authenticate($request->only('email', 'password'));
            $user = Auth::user();
            if($user->id == $user2->id){
                return redirect()->back()->withInput()->withErrors(['connexion'=>'L\'authentification contradictoire nécessite la connexion d\'un autre utilisateur que vous.']);
            }
            else{
                Session::put('user2', $user2);
                Cookie::queue(Cookie::make('auth_cont', true, env('DUREE_AUTH_CONT', 60)));
                return redirect()->intended(route('accueil'));
            }

        } catch (\Exception $th) {
            return redirect()->back()->withInput()->withErrors(['connexion'=>$th->getMessage()]);
        }

    }

    public function login(Request $request)
    {
        /*$request->validate( [
            'email' => 'required|email',
            'password' => 'required',
        ]);*/

        /*if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }*/

        try {
            $user = $this->authService->authenticate($request->only('email', 'password'));

            if ($this->authService->isAdmin($user)) {
                // cas où l'utilisateur est un administrateur
                return response()->json(['estAuthentifie'=>true]);
            } else {
                // L'utilisateur n'est pas un administrateur
                return response()->json(['estAuthentifie'=>false]);
            }
        } catch (\Exception $e) {
            return response()->json(['estAuthentifie'=>$e->getMessage()]);

        }
    }
}
