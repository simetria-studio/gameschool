<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name');

        $usuarios = $query->paginate($perPage)->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'password' => 'senha',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('usuarios.index', $request->only(['per_page', 'search']))
            ->with('success', 'Usuário adicionado com sucesso.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($usuario->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'password' => 'senha',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $usuario->update($data);

        return redirect()
            ->route('usuarios.index', $request->only(['per_page', 'search']))
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if (Auth::check() && Auth::id() === $usuario->getKey()) {
            return redirect()
                ->route('usuarios.index', $request->only(['per_page', 'search']))
                ->with('success', 'Você não pode excluir o próprio usuário autenticado.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index', $request->only(['per_page', 'search']))
            ->with('success', 'Usuário excluído com sucesso.');
    }
}

