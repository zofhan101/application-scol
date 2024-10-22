<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function delUser(Request $request){
        try{
            $iduser = $request->input('id_user');
            $this->userService->delUser($iduser);
            return redirect()->back();
        }
        catch(\Exception $e){
            return redirect()->back()->withErrors(['iderror'=>$e->getMessage()]);
        }

    }

    public function getAllUsers(){
        $allUsers = $this->userService->getAllUsersValides();
        return view('listeUsers',[
            'users' =>$allUsers
        ]);
    }
}
