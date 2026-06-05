<?php

namespace App\Http\Controllers;

use App\Models\Roleta;
use App\Models\RoletaBauItem;
use App\Models\RoletaItem;
use App\Models\RoletaSegmento;
use App\Models\Turma;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoletaController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';
        $professorTurmaIds = $isProfessor ? $user->turmas()->pluck('id')->all() : [];

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $query = Roleta::with(['unidade', 'turmas'])
            ->withCount('segmentos')
            ->when($isProfessor, function ($q) use ($professorTurmaIds) {
                if ($professorTurmaIds === []) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereHas('turmas', fn ($qt) => $qt->whereIn('turmas.id', $professorTurmaIds));
                }
            })
            ->when(! $isMaster && ! $isProfessor, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->orderByDesc('created_at');

        $roletas = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster ? Unidade::orderBy('titulo')->get() : Unidade::where('id', $user->unidade_id)->get();
        $turmasQuery = Turma::query()->orderBy('nome');
        if (! $isMaster) {
            $turmasQuery->where('unidade_id', $user->unidade_id);
        }
        $turmas = $turmasQuery->get(['id', 'nome', 'unidade_id']);

        return view('roletas.index', compact('roletas', 'unidades', 'turmas', 'perPage') + [
            'turmasPorUnidadeJson' => $turmas->groupBy('unidade_id')->map(fn ($g) => $g->map(fn (Turma $t) => ['id' => $t->id, 'nome' => $t->nome])->values()),
            'canManageAllUnits' => $isMaster,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateRoleta($request);
        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);
        if ($isProfessor) {
            $this->assertProfessorOwnsTurmas($user, $validated['turma_ids']);
        }

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $roleta = Roleta::create($validated);
        $roleta->turmas()->sync($turmaIds);

        return redirect()->route('roletas.index')->with('success', 'Roleta criada com sucesso.');
    }

    public function update(Request $request, Roleta $roleta): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManage($user, $roleta, $isMaster, $isProfessor);

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateRoleta($request);
        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $roleta->update($validated);
        $roleta->turmas()->sync($turmaIds);

        return redirect()->route('roletas.index')->with('success', 'Roleta atualizada com sucesso.');
    }

    public function destroy(Roleta $roleta): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManage($user, $roleta, $isMaster, $isProfessor);

        $roleta->turmas()->detach();
        $roleta->delete();

        return redirect()->route('roletas.index')->with('success', 'Roleta excluída com sucesso.');
    }

    public function segmentos(Roleta $roleta): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManage($user, $roleta, $isMaster, $isProfessor);

        $roleta->load(['unidade', 'segmentos.item', 'segmentos.bauItens.item']);
        $itens = RoletaItem::query()
            ->where('unidade_id', $roleta->unidade_id)
            ->where('status', 'ativo')
            ->orderBy('tipo')
            ->orderBy('titulo')
            ->get();

        return view('roletas.segmentos', compact('roleta', 'itens'));
    }

    public function storeSegmento(Request $request, Roleta $roleta): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManage($user, $roleta, $isMaster, $isProfessor);

        $validated = $this->validateSegmento($request, $roleta);
        $bauItens = $validated['bau_itens'] ?? [];
        unset($validated['bau_itens']);

        DB::transaction(function () use ($roleta, $validated, $bauItens) {
            $ordem = ((int) $roleta->segmentos()->max('ordem')) + 1;
            $segmento = $roleta->segmentos()->create($validated + ['ordem' => $ordem]);

            if ($segmento->tipo === 'bau') {
                foreach ($bauItens as $bi) {
                    $segmento->bauItens()->create([
                        'roleta_item_id' => $bi['roleta_item_id'],
                        'peso' => $bi['peso'],
                    ]);
                }
            }
        });

        return redirect()->route('roletas.segmentos', $roleta)->with('success', 'Segmento adicionado.');
    }

    public function destroySegmento(Roleta $roleta, RoletaSegmento $segmento): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManage($user, $roleta, $isMaster, $isProfessor);

        if ((int) $segmento->roleta_id !== (int) $roleta->id) {
            abort(404);
        }

        $segmento->delete();

        return redirect()->route('roletas.segmentos', $roleta)->with('success', 'Segmento removido.');
    }

    private function validateRoleta(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_ids' => ['required', 'array', 'min:1'],
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'custo_coins' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'custo_coins' => 'custo em coins',
        ]);
    }

    private function validateSegmento(Request $request, Roleta $roleta): array
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:item,coins,xp,bau'],
            'roleta_item_id' => ['nullable', 'integer', 'exists:roleta_itens,id'],
            'coins' => ['nullable', 'integer', 'min:0'],
            'xp' => ['nullable', 'integer', 'min:0'],
            'peso' => ['required', 'integer', 'min:1', 'max:1000'],
            'cor' => ['nullable', 'string', 'max:7'],
            'bau_itens' => ['nullable', 'array'],
            'bau_itens.*.roleta_item_id' => ['required_with:bau_itens', 'integer', 'exists:roleta_itens,id'],
            'bau_itens.*.peso' => ['required_with:bau_itens', 'integer', 'min:1'],
        ], [], [
            'titulo' => 'título',
            'tipo' => 'tipo',
            'peso' => 'peso',
        ]);

        if ($validated['tipo'] === 'item' && empty($validated['roleta_item_id'])) {
            throw ValidationException::withMessages(['roleta_item_id' => 'Selecione o item do prêmio.']);
        }

        if ($validated['tipo'] === 'bau') {
            $bauItens = collect($validated['bau_itens'] ?? [])->filter(fn ($b) => ! empty($b['roleta_item_id']))->values();
            if ($bauItens->count() < 2) {
                throw ValidationException::withMessages(['bau_itens' => 'O baú precisa de pelo menos 2 itens no pool.']);
            }
            $validated['bau_itens'] = $bauItens->all();
            $validated['roleta_item_id'] = null;
            $validated['coins'] = 0;
            $validated['xp'] = 0;
        } elseif ($validated['tipo'] === 'coins') {
            $validated['roleta_item_id'] = null;
            $validated['xp'] = 0;
            $validated['bau_itens'] = [];
        } elseif ($validated['tipo'] === 'xp') {
            $validated['roleta_item_id'] = null;
            $validated['coins'] = 0;
            $validated['bau_itens'] = [];
        } else {
            $validated['coins'] = 0;
            $validated['xp'] = 0;
            $validated['bau_itens'] = [];
        }

        if (! empty($validated['roleta_item_id'])) {
            $ok = RoletaItem::query()
                ->where('id', $validated['roleta_item_id'])
                ->where('unidade_id', $roleta->unidade_id)
                ->exists();
            if (! $ok) {
                throw ValidationException::withMessages(['roleta_item_id' => 'Item inválido para esta escola.']);
            }
        }

        return $validated;
    }

    private function assertCanManage($user, Roleta $roleta, bool $isMaster, bool $isProfessor): void
    {
        if ($isMaster) {
            return;
        }
        if ((int) $roleta->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }
        if ($isProfessor) {
            $allowed = $user->turmas()->pluck('id')->all();
            $ids = $roleta->turmas()->pluck('turmas.id')->all();
            if ($ids === [] || count(array_intersect($ids, $allowed)) === 0) {
                abort(403);
            }
        }
    }

    private function assertTurmasBelongToUnidade(int $unidadeId, array $turmaIds): void
    {
        $turmaIds = array_values(array_unique(array_map('intval', $turmaIds)));
        $ok = Turma::query()->where('unidade_id', $unidadeId)->whereIn('id', $turmaIds)->count();
        if ($ok !== count($turmaIds)) {
            throw ValidationException::withMessages(['turma_ids' => 'Selecione apenas turmas da escola escolhida.']);
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
