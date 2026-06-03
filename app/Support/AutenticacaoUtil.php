<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AutenticacaoUtil
{
    public static function tentarLogin(string $login, string $password, bool $remember = false): bool
    {
        $login = trim($login);

        $user = User::query()
            ->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        Auth::login($user, $remember);

        return true;
    }
}
