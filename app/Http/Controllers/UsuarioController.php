<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unidade;
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

        $query = User::with('unidade')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name');

        $usuarios = $query->paginate($perPage)->withQueryString();

        $unidades = Unidade::orderBy('titulo')->get();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'perPage' => $perPage,
            'search' => $search,
            'unidades' => $unidades,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'access_role' => ['required', 'in:master,direcao,professor'],
            'unidade_id' => ['required_unless:access_role,master', 'nullable', 'exists:unidades,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'access_role' => 'tipo de acesso',
            'unidade_id' => 'unidade',
            'password' => 'senha',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'access_role' => $validated['access_role'],
            'unidade_id' => $validated['unidade_id'] ?? null,
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
            'access_role' => ['required', 'in:master,direcao,professor'],
            'unidade_id' => ['required_unless:access_role,master', 'nullable', 'exists:unidades,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'access_role' => 'tipo de acesso',
            'unidade_id' => 'unidade',
            'password' => 'senha',
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'access_role' => $validated['access_role'],
            'unidade_id' => $validated['unidade_id'] ?? null,
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

