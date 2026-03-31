<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Atitude;
use App\Models\Turma;
use App\Models\Unidade;
use App\Support\NotificarRecompensaAluno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AtitudeController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = Atitude::with('unidade')
            ->when(!$isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('titulo', 'like', '%' . $search . '%')
                        ->orWhere('descricao', 'like', '%' . $search . '%')
                        ->orWhere('tipo', 'like', '%' . $search . '%')
                        ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy('titulo');

        $atitudes = $query->paginate($perPage)->withQueryString();

        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        return view('atitudes.index', [
            'atitudes' => $atitudes,
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
            'canManageAllUnits' => $isMaster,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $request->validate([
            'unidade_id' => ['required', 'exists:unidades,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:positiva,negativa'],
            'coins' => ['required', 'integer'],
            'xp' => ['required', 'integer'],
        ], [], [
            'unidade_id' => 'escola / unidade',
            'titulo' => 'título',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
        ]);

        if (!$isMaster && (int) $validated['unidade_id'] !== (int) $user->unidade_id) {
            abort(403);
        }

        Atitude::create($validated);

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude adicionada com sucesso.');
    }

    public function update(Request $request, Atitude $atitude): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $atitude->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $request->validate([
            'unidade_id' => ['required', 'exists:unidades,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:positiva,negativa'],
            'coins' => ['required', 'integer'],
            'xp' => ['required', 'integer'],
        ], [], [
            'unidade_id' => 'escola / unidade',
            'titulo' => 'título',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
        ]);

        if (!$isMaster && (int) $validated['unidade_id'] !== (int) $user->unidade_id) {
            abort(403);
        }

        $atitude->update($validated);

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude atualizada com sucesso.');
    }

    public function destroy(Request $request, Atitude $atitude): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster && (int) $atitude->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        $atitude->delete();

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude excluída com sucesso.');
    }

    public function showRecompensar(Atitude $atitude): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        if (!$isMaster && (int) $atitude->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        $turmasQuery = Turma::query()->orderBy('nome');
        if ($isProfessor) {
            $turmaIds = $user->turmas()->pluck('id')->all();
            $turmasQuery->whereIn('id', $turmaIds);
        } elseif (!$isMaster) {
            $turmasQuery->where('unidade_id', $user->unidade_id);
        }
        $turmas = $turmasQuery->get();

        $alunosQuery = Aluno::with(['unidade', 'turma'])->orderBy('nome');
        if ($isProfessor) {
            $turmaIds = $user->turmas()->pluck('id')->all();
            if ($turmaIds === []) {
                $alunosQuery->whereRaw('1 = 0');
            } else {
                $alunosQuery->whereIn('turma_id', $turmaIds);
            }
            $alunosQuery->where('unidade_id', $user->unidade_id);
        } elseif (!$isMaster) {
            $alunosQuery->where('unidade_id', $user->unidade_id);
        }

        $alunos = $alunosQuery->get();

        return view('atitudes.recompensar', [
            'atitude' => $atitude,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'alunos' => $alunos,
        ]);
    }

    public function recompensar(Request $request, Atitude $atitude): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        if (!$isMaster && (int) $atitude->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        $validated = $request->validate([
            'aluno_ids' => ['required', 'array', 'min:1'],
            'aluno_ids.*' => ['integer', 'exists:alunos,id'],
        ], [], ['aluno_ids' => 'alunos']);

        $ids = array_values(array_unique(array_map('intval', $validated['aluno_ids'])));

        $allowedTurmas = $isProfessor ? $user->turmas()->pluck('id')->all() : null;

        $nomes = [];

        DB::transaction(function () use ($ids, $isMaster, $isProfessor, $user, $atitude, $allowedTurmas, &$nomes): void {
            foreach ($ids as $id) {
                $aluno = Aluno::query()->whereKey($id)->lockForUpdate()->firstOrFail();

                if (! $isMaster) {
                    if ((int) $aluno->unidade_id !== (int) $user->unidade_id) {
                        abort(403);
                    }
                }

                if ($isProfessor) {
                    if (! in_array((int) $aluno->turma_id, $allowedTurmas ?: [], true)) {
                        abort(403);
                    }
                }

                $aluno->increment('coins', $atitude->coins);
                $aluno->increment('xp', $atitude->xp);

                NotificarRecompensaAluno::porAtitude($aluno, $atitude);

                $nomes[] = $aluno->nome;
            }
        });

        $qtd = count($ids);
        $lista = $qtd <= 3
            ? implode(', ', $nomes)
            : implode(', ', array_slice($nomes, 0, 3)).' e mais '.($qtd - 3).' aluno(s)';

        return redirect()
            ->route('atitudes.index')
            ->with('success', "Atitude \"{$atitude->titulo}\" aplicada a {$qtd} aluno(s) ({$lista}). ".(int) $atitude->coins.' coins e '.(int) $atitude->xp.' XP por aluno.');
    }
}
