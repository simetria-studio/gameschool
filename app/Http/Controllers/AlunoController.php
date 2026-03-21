<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlunoController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = Aluno::with(['unidade', 'turma'])
            ->when(!$isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('nome', 'like', '%' . $search . '%')
                    ->orWhere('genero', 'like', '%' . $search . '%')
                    ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'))
                    ->orWhereHas('turma', fn ($t) => $t->where('nome', 'like', '%' . $search . '%'));
            })
            ->orderBy('nome');

        $alunos = $query->paginate($perPage)->withQueryString();

        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        $turmas = $isMaster
            ? Turma::orderBy('nome')->get(['id', 'nome', 'unidade_id'])
            : $this->turmasForUser($user)->get(['id', 'nome', 'unidade_id']);

        $turmasPorUnidade = Turma::orderBy('nome')
            ->get(['id', 'nome', 'unidade_id'])
            ->groupBy('unidade_id')
            ->map(fn ($g) => $g->map(fn (Turma $t) => ['id' => $t->id, 'nome' => $t->nome])->values());

        return view('alunos.index', [
            'alunos' => $alunos,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'turmasPorUnidadeJson' => $turmasPorUnidade,
            'perPage' => $perPage,
            'search' => $search,
            'canManageAllUnits' => $isMaster,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        // Direção só pode cadastrar alunos na própria unidade.
        if (!$isMaster) {
            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'genero' => ['required', 'in:masculino,feminino,outro'],
            'nome' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'coins' => ['required', 'integer', 'min:0'],
            'xp' => ['required', 'integer', 'min:0'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_id' => [
                'required',
                Rule::exists('turmas', 'id')->where('unidade_id', (int) $request->input('unidade_id')),
            ],
        ], [], [
            'genero' => 'gênero',
            'nome' => 'nome',
            'data_nascimento' => 'data de nascimento',
            'unidade_id' => 'unidade',
            'turma_id' => 'turma',
        ]);

        if (($user->access_role ?? '') === 'professor') {
            $allowed = $user->turmas()->pluck('id')->all();
            if (! in_array((int) $validated['turma_id'], $allowed, true)) {
                abort(403);
            }
        }

        $aluno = Aluno::create($validated);

        $user = User::create([
            'name' => $aluno->nome,
            'username' => 'aluno' . $aluno->id,
            'email' => 'aluno' . $aluno->id . '@alunos.' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'local'),
            'password' => Str::random(16),
            'access_role' => 'aluno',
            'unidade_id' => $aluno->unidade_id,
        ]);
        $user->generateQrLoginToken();

        $aluno->update(['user_id' => $user->id]);

        return redirect()
            ->route('alunos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Aluno adicionado com sucesso.');
    }

    public function update(Request $request, Aluno $aluno): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $aluno->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }

            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'genero' => ['required', 'in:masculino,feminino,outro'],
            'nome' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'coins' => ['required', 'integer', 'min:0'],
            'xp' => ['required', 'integer', 'min:0'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_id' => [
                'required',
                Rule::exists('turmas', 'id')->where('unidade_id', (int) $request->input('unidade_id')),
            ],
        ], [], [
            'genero' => 'gênero',
            'nome' => 'nome',
            'data_nascimento' => 'data de nascimento',
            'unidade_id' => 'unidade',
            'turma_id' => 'turma',
        ]);

        if (($user->access_role ?? '') === 'professor') {
            $allowed = $user->turmas()->pluck('id')->all();
            if (! in_array((int) $validated['turma_id'], $allowed, true)) {
                abort(403);
            }
        }

        $aluno->update($validated);

        if ($aluno->user) {
            $aluno->user->update(['name' => $aluno->nome]);
        }

        return redirect()
            ->route('alunos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Aluno atualizado com sucesso.');
    }

    public function destroy(Request $request, Aluno $aluno): RedirectResponse
    {
        $userAuth = Auth::user();
        $isMaster = ($userAuth->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $aluno->unidade_id !== (int) $userAuth->unidade_id) {
                abort(403);
            }
        }

        $user = $aluno->user;
        $aluno->delete();
        $user?->delete();

        return redirect()
            ->route('alunos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Aluno excluído com sucesso.');
    }

    public function cracha(Aluno $aluno): View
    {
        $aluno->load(['unidade', 'turma', 'user']);

        if (! $aluno->user) {
            $this->ensureAlunoUserAndToken($aluno);
        }

        return view('alunos.cracha', ['aluno' => $aluno]);
    }

    public function crachasLote(Request $request): View
    {
        $userAuth = Auth::user();
        $isMaster = ($userAuth->access_role ?? 'professor') === 'master';

        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $userAuth->unidade_id)->orderBy('titulo')->get();
        $turmas = $this->turmasForUser($userAuth)->get();

        $selectedUnidade = $request->input('unidade_id');
        $selectedTurma = $request->input('turma_id');

        if (!$isMaster) {
            $selectedUnidade = $userAuth->unidade_id;
        }

        $query = Aluno::with(['unidade', 'turma', 'user'])
            ->when($selectedUnidade, fn ($q) => $q->where('unidade_id', $selectedUnidade))
            ->when($selectedTurma, fn ($q) => $q->where('turma_id', $selectedTurma))
            ->orderBy('nome');

        $alunos = $query->get();

        foreach ($alunos as $aluno) {
            if (! $aluno->user) {
                $this->ensureAlunoUserAndToken($aluno);
                $aluno->load('user');
            } elseif (! $aluno->user->qr_login_token) {
                $aluno->user->generateQrLoginToken();
            }
        }

        return view('alunos.crachas-lote', [
            'alunos' => $alunos,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'selectedUnidade' => $selectedUnidade,
            'selectedTurma' => $selectedTurma,
        ]);
    }

    private function turmasForUser($user)
    {
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $q = Turma::query()->orderBy('nome');

        if ($isProfessor) {
            $ids = $user->turmas()->pluck('id')->all();
            if ($ids === []) {
                return $q->whereRaw('1 = 0');
            }

            return $q->whereIn('id', $ids);
        }

        if (!$isMaster) {
            return $q->where('unidade_id', $user->unidade_id);
        }

        return $q;
    }

    private function ensureAlunoUserAndToken(Aluno $aluno): void
    {
        $user = User::create([
            'name' => $aluno->nome,
            'username' => 'aluno' . $aluno->id,
            'email' => 'aluno' . $aluno->id . '@alunos.' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'local'),
            'password' => Str::random(16),
            'access_role' => 'aluno',
            'unidade_id' => $aluno->unidade_id,
        ]);

        $user->generateQrLoginToken();
        $aluno->update(['user_id' => $user->id]);
    }
}
