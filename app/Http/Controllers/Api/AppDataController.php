<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Atitude;
use App\Models\LojaItem;
use App\Models\Missao;
use App\Models\Pedido;
use App\Models\Turma;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppDataController extends Controller
{
    public function unidades(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Unidade::query()->orderBy('titulo');

        if (! $this->isMaster($user)) {
            $query->where('id', $user->unidade_id);
        }

        return response()->json(['data' => $query->get(['id', 'titulo'])]);
    }

    public function turmas(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Turma::query()->orderBy('nome');

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', (int) $request->integer('unidade_id'));
        }

        if (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        if ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            $query->whereIn('id', $ids ?: [0]);
        }

        return response()->json(['data' => $query->get(['id', 'unidade_id', 'nome', 'periodo', 'ativo'])]);
    }

    public function alunos(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Aluno::query()->with(['unidade:id,titulo', 'turma:id,nome'])->orderBy('nome');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('nome', 'like', '%' . $search . '%');
        }

        if ($request->filled('turma_id')) {
            $query->where('turma_id', (int) $request->integer('turma_id'));
        }

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('id', $user->aluno->id);
        } elseif (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);

            if ($this->isProfessor($user)) {
                $ids = $user->turmas()->pluck('turmas.id')->all();
                $query->whereIn('turma_id', $ids ?: [0]);
            }
        }

        return response()->json(['data' => $query->paginate((int) $request->integer('per_page', 20))]);
    }

    public function missoes(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Missao::query()->with(['unidade:id,titulo', 'turmas:id,nome'])->orderByDesc('created_at');

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('unidade_id', $user->aluno->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->where('turmas.id', $user->aluno->turma_id));
        } elseif ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            $query->where('unidade_id', $user->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->whereIn('turmas.id', $ids ?: [0]));
        } elseif (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        return response()->json(['data' => $query->paginate((int) $request->integer('per_page', 20))]);
    }

    public function atitudes(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Atitude::query()->with('unidade:id,titulo')->orderBy('titulo');

        if (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        return response()->json(['data' => $query->paginate((int) $request->integer('per_page', 20))]);
    }

    public function loja(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = LojaItem::query()->orderBy('titulo');

        if ($request->boolean('apenas_ativos', true)) {
            $query->where('status', 'ativo')->where('quantidade', '>', 0);
        }

        if (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        return response()->json(['data' => $query->paginate((int) $request->integer('per_page', 20))]);
    }

    public function pedidos(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Pedido::query()
            ->with(['aluno:id,nome,user_id,unidade_id,turma_id', 'produto:id,titulo,unidade_id'])
            ->orderByDesc('created_at');

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('aluno_id', $user->aluno->id);
        } elseif (! $this->isMaster($user)) {
            $query->whereHas('aluno', fn (Builder $q) => $q->where('unidade_id', $user->unidade_id));
        }

        return response()->json(['data' => $query->paginate((int) $request->integer('per_page', 20))]);
    }

    public function ranking(Request $request): JsonResponse
    {
        $user = $this->user($request);

        $validated = $request->validate([
            'por' => ['sometimes', 'in:coins,xp'],
            'unidade_id' => ['sometimes', 'nullable', 'exists:unidades,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ], [], [
            'por' => 'critério',
            'unidade_id' => 'unidade',
        ]);

        $por = $validated['por'] ?? 'coins';
        $column = $por === 'xp' ? 'xp' : 'coins';

        $query = Aluno::query()->with(['unidade:id,titulo', 'turma:id,nome']);

        if ($this->isMaster($user)) {
            if (! empty($validated['unidade_id'])) {
                $query->where('unidade_id', (int) $validated['unidade_id']);
            }
        } else {
            $query->where('unidade_id', $user->unidade_id);
        }

        $query->orderByDesc($column)->orderBy('nome');

        $perPage = min(100, max(1, (int) ($validated['per_page'] ?? 50)));
        $paginated = $query->paginate($perPage)->withQueryString();

        $base = ($paginated->currentPage() - 1) * $paginated->perPage();

        $items = $paginated->getCollection()->values()->map(function (Aluno $aluno, int $i) use ($base, $column) {
            return [
                'posicao' => $base + $i + 1,
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'coins' => (int) $aluno->coins,
                'xp' => (int) $aluno->xp,
                'ordenado_por' => $column,
                'turma' => $aluno->turma ? ['id' => $aluno->turma->id, 'nome' => $aluno->turma->nome] : null,
                'unidade' => $aluno->unidade ? ['id' => $aluno->unidade->id, 'titulo' => $aluno->unidade->titulo] : null,
            ];
        });

        return response()->json([
            'por' => $column,
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('aluno', 'turmas');
        return $user;
    }

    private function isMaster(User $user): bool
    {
        return ($user->access_role ?? 'professor') === 'master';
    }

    private function isProfessor(User $user): bool
    {
        return ($user->access_role ?? 'professor') === 'professor';
    }

    private function isAluno(User $user): bool
    {
        return ($user->access_role ?? 'professor') === 'aluno';
    }
}

