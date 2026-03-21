<?php

namespace App\Http\Controllers;

use App\Models\Missao;
use App\Models\Turma;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MissaoController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $professorTurmaIds = $isProfessor
            ? $user->turmas()->pluck('id')->all()
            : [];

        $query = Missao::with(['unidade', 'turmas'])
            ->when($isProfessor, function ($q) use ($professorTurmaIds) {
                if ($professorTurmaIds === []) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereHas('turmas', fn ($qt) => $qt->whereIn('turmas.id', $professorTurmaIds));
                }
            })
            ->when(!$isMaster && !$isProfessor, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('titulo', 'like', '%' . $search . '%')
                        ->orWhere('descricao', 'like', '%' . $search . '%')
                        ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'))
                        ->orWhereHas('turmas', fn ($t) => $t->where('nome', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy('created_at', 'desc');

        $missoes = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        $turmasQuery = Turma::query()->orderBy('nome');
        if (!$isMaster) {
            $turmasQuery->where('unidade_id', $user->unidade_id);
        }
        $turmas = $turmasQuery->get(['id', 'nome', 'unidade_id']);

        return view('missoes.index', [
            'missoes' => $missoes,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'turmasPorUnidadeJson' => $turmas->groupBy('unidade_id')->map(function ($g) {
                return $g->map(fn (Turma $t) => ['id' => $t->id, 'nome' => $t->nome])->values();
            }),
            'perPage' => $perPage,
            'search' => $search,
            'canManageAllUnits' => $isMaster,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        if (!$isMaster) {
            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_ids' => ['required', 'array', 'min:1'],
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'xp' => ['required', 'integer'],
            'coins' => ['required', 'integer'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'turma_ids' => 'turmas',
            'descricao' => 'descrição',
            'data_encerramento' => 'data de encerramento',
        ]);

        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);

        if ($isProfessor) {
            $this->assertProfessorOwnsTurmas($user, $validated['turma_ids']);
            if ((int) $validated['unidade_id'] !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $missao = Missao::create($validated);
        $missao->turmas()->sync($turmaIds);

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão adicionada com sucesso.');
    }

    public function update(Request $request, Missao $missao): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        if (!$isMaster) {
            if ((int) $missao->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }

            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_ids' => ['required', 'array', 'min:1'],
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'xp' => ['required', 'integer'],
            'coins' => ['required', 'integer'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'turma_ids' => 'turmas',
            'descricao' => 'descrição',
            'data_encerramento' => 'data de encerramento',
        ]);

        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);

        if ($isProfessor) {
            $this->assertProfessorOwnsTurmas($user, $validated['turma_ids']);
            if ((int) $validated['unidade_id'] !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $missao->update($validated);
        $missao->turmas()->sync($turmaIds);

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão atualizada com sucesso.');
    }

    public function destroy(Request $request, Missao $missao): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $missao->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $missao->turmas()->detach();
        $missao->delete();

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão excluída com sucesso.');
    }

    private function assertTurmasBelongToUnidade(int $unidadeId, array $turmaIds): void
    {
        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        $ok = Turma::query()
            ->where('unidade_id', $unidadeId)
            ->whereIn('id', $turmaIds)
            ->count();

        if ($ok !== count($turmaIds)) {
            throw ValidationException::withMessages([
                'turma_ids' => 'Selecione apenas turmas da escola escolhida.',
            ]);
        }
    }

    private function assertProfessorOwnsTurmas($user, array $turmaIds): void
    {
        $allowed = $user->turmas()->pluck('id')->all();
        foreach (array_unique(array_map('intval', $turmaIds)) as $tid) {
            if (! in_array($tid, $allowed, true)) {
                abort(403);
            }
        }
    }
}
