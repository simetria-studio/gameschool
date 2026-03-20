<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * @param  string[]  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userRole = $user->access_role ?? 'professor';

        if (!in_array($userRole, $roles, true)) {
            abort(403);
        }

        // Direção/Professor só podem operar se a unidade estiver definida.
        if ($userRole !== 'master' && empty($user->unidade_id)) {
            abort(403, 'Usuário sem unidade definida.');
        }

        return $next($request);
    }
}

