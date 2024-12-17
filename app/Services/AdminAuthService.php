<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public function authenticate($credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Données d\'authentification erronées');
        }

        return $user;
    }

    public function isAdmin(User $user)
    {
        return $user->role->rang_role === 70;
    }
}
