<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\User;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = User::with(['unidade', 'turmas'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy('name');

        $usuarios = $query->paginate($perPage)->withQueryString();

        $unidades = Unidade::orderBy('titulo')->get();
        $turmas = Turma::with('unidade')->orderBy('nome')->get(['id', 'nome', 'unidade_id']);

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'perPage' => $perPage,
            'search' => $search,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'turmasPorUnidadeJson' => $turmas->groupBy('unidade_id')->map(function ($g) {
                return $g->map(fn (Turma $t) => ['id' => $t->id, 'nome' => $t->nome])->values();
            }),
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
            'turma_ids' => Rule::when(
                $request->input('access_role') === 'professor',
                ['required', 'array', 'min:1'],
                ['nullable', 'array'],
            ),
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'access_role' => 'tipo de acesso',
            'unidade_id' => 'unidade',
            'turma_ids' => 'turmas',
            'password' => 'senha',
        ]);

        if (($validated['access_role'] ?? '') === 'professor') {
            $this->assertTurmasBelongToUnidade((int) $validated['unidade_id'], $validated['turma_ids'] ?? []);
        }

        $turmaIds = ($validated['access_role'] ?? '') === 'professor'
            ? array_values(array_unique(array_map('intval', $validated['turma_ids'] ?? [])))
            : [];

        $newUser = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'access_role' => $validated['access_role'],
            'unidade_id' => $validated['unidade_id'] ?? null,
        ]);

        $newUser->turmas()->sync($turmaIds);

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
            'turma_ids' => Rule::when(
                $request->input('access_role') === 'professor',
                ['required', 'array', 'min:1'],
                ['nullable', 'array'],
            ),
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [], [
            'name' => 'nome',
            'username' => 'usuário',
            'email' => 'e-mail',
            'access_role' => 'tipo de acesso',
            'unidade_id' => 'unidade',
            'turma_ids' => 'turmas',
            'password' => 'senha',
        ]);

        if (($validated['access_role'] ?? '') === 'professor') {
            $this->assertTurmasBelongToUnidade((int) $validated['unidade_id'], $validated['turma_ids'] ?? []);
        }

        $turmaIds = ($validated['access_role'] ?? '') === 'professor'
            ? array_values(array_unique(array_map('intval', $validated['turma_ids'] ?? [])))
            : [];

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
        $usuario->turmas()->sync($turmaIds);

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

        $usuario->turmas()->detach();
        $usuario->delete();

        return redirect()
            ->route('usuarios.index', $request->only(['per_page', 'search']))
            ->with('success', 'Usuário excluído com sucesso.');
    }

    private function assertTurmasBelongToUnidade(int $unidadeId, array $turmaIds): void
    {
        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        if ($turmaIds === []) {
            throw ValidationException::withMessages([
                'turma_ids' => 'Selecione ao menos uma turma para o professor.',
            ]);
        }

        $ok = Turma::query()
            ->where('unidade_id', $unidadeId)
            ->whereIn('id', $turmaIds)
            ->count();

        if ($ok !== count($turmaIds)) {
            throw ValidationException::withMessages([
                'turma_ids' => 'As turmas devem pertencer à unidade selecionada.',
            ]);
        }
    }
}

