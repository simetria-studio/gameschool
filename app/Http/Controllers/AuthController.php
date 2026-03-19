<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Processa o login (usuário + senha).
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
        ];

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['username' => __('Credenciais inválidas. Verifique o usuário e a senha.')]);
    }

    /**
     * Login via token do QR code (crachá do aluno).
     */
    public function loginWithToken(string $token, Request $request): RedirectResponse
    {
        $user = User::where('qr_login_token', $token)->first();

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors(['username' => 'QR code inválido ou expirado.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Encerra a sessão do usuário.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
