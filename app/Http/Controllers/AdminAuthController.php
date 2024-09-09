<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use Illuminate\Validation\Validator;


class AdminAuthController extends Controller
{
    protected $authService;

    public function __construct(AdminAuthService $authService)
    {
        $this->authService = $authService;
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
