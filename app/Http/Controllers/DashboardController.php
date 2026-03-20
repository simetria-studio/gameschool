<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $role = Auth::user()->access_role ?? 'professor';

        if ($role === 'master' || $role === 'direcao') {
            return redirect()->route('pedidos.index');
        }

        return redirect()->route('missoes.index');
    }
}
