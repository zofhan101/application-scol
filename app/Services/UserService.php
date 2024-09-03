<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Users_valide;
use App\Models\User;


class UserService
{
    public function delUser($idUser):void{
        $user = User::find($idUser);
        if($user){
            $user->date_suppr = now();
            $user->save();
        }
        else{
            throw(new Exception('id_user inexistant'));
        }
    }

    public function getAllUsersValides(){
        return Users_valide::all();
    }
}
