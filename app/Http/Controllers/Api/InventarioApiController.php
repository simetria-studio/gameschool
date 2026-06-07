<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\AlunoItem;
use App\Models\AlunoPresente;
use App\Models\User;
use App\Support\InventarioAluno;
use App\Support\PresenteAlunoProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $aluno = $this->resolveAluno($user, $request);

        $query = AlunoItem::query()
            ->with('item')
            ->where('aluno_id', $aluno->id)
            ->where('quantidade', '>', 0)
            ->orderByDesc('updated_at');

        if ($request->filled('tipo')) {
            $tipo = (string) $request->string('tipo');
            $query->whereHas('item', fn ($q) => $q->where('tipo', $tipo));
        }

        $registros = $query->get();
        $itens = $registros->map(fn (AlunoItem $r) => InventarioAluno::formatarItem($r));

        return response()->json([
            'data' => [
                'aluno' => $this->formatarAluno($aluno),
                'resumo' => InventarioAluno::montarResumo($registros),
                'categorias' => InventarioAluno::montarCategorias($registros),
                'itens' => $itens,
            ],
        ]);
    }

    public function show(Request $request, AlunoItem $alunoItem): JsonResponse
    {
        $user = $this->user($request);
        $aluno = $this->resolveAluno($user, $request);

        if ((int) $alunoItem->aluno_id !== (int) $aluno->id) {
            abort(404);
        }

        $alunoItem->load('item');

        return response()->json([
            'data' => InventarioAluno::formatarItem($alunoItem, detalhado: true),
        ]);
    }

    public function presentes(Request $request): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403);
        }

        $tipo = (string) $request->string('tipo', 'recebidos');

        $query = AlunoPresente::query()
            ->with(['item', 'remetente:id,nome', 'destinatario:id,nome'])
            ->orderByDesc('created_at');

        if ($tipo === 'enviados') {
            $query->where('remetente_id', $user->aluno->id);
        } else {
            $query->where('destinatario_id', $user->aluno->id);
        }

        $paginated = $query->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => $paginated->getCollection()->map(fn (AlunoPresente $p) => [
                'id' => $p->id,
                'quantidade' => (int) $p->quantidade,
                'mensagem' => $p->mensagem,
                'lido' => (bool) $p->lido,
                'item' => InventarioAluno::formatarRoletaItem($p->item),
                'remetente' => $p->remetente ? ['id' => $p->remetente->id, 'nome' => $p->remetente->nome] : null,
                'destinatario' => $p->destinatario ? ['id' => $p->destinatario->id, 'nome' => $p->destinatario->nome] : null,
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function buscarDestinatarios(Request $request): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403);
        }

        $search = trim((string) $request->string('search', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'data' => [],
                'message' => 'Digite pelo menos 2 caracteres para buscar.',
            ]);
        }

        $alunos = Aluno::query()
            ->with('turma:id,nome')
            ->where('unidade_id', $user->aluno->unidade_id)
            ->where('id', '!=', $user->aluno->id)
            ->where('nome', 'like', '%' . $search . '%')
            ->orderBy('nome')
            ->limit(15)
            ->get(['id', 'nome', 'turma_id']);

        return response()->json([
            'data' => $alunos->map(fn (Aluno $a) => [
                'id' => $a->id,
                'nome' => $a->nome,
                'turma' => $a->turma ? ['id' => $a->turma->id, 'nome' => $a->turma->nome] : null,
            ]),
        ]);
    }

    public function enviarPresente(Request $request): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403, 'Somente alunos podem enviar presentes.');
        }

        $validated = $request->validate([
            'nome_destino' => ['required', 'string', 'min:2', 'max:255'],
            'aluno_item_id' => ['required', 'integer', 'exists:aluno_itens,id'],
            'quantidade' => ['sometimes', 'integer', 'min:1'],
            'mensagem' => ['nullable', 'string', 'max:500'],
        ], [], [
            'nome_destino' => 'nome do destino',
            'aluno_item_id' => 'item',
            'quantidade' => 'quantidade',
            'mensagem' => 'mensagem',
        ]);

        $destinatario = PresenteAlunoProcessor::resolverDestinatarioPorNome(
            $user->aluno,
            $validated['nome_destino']
        );
        $alunoItem = AlunoItem::findOrFail((int) $validated['aluno_item_id']);
        $qtd = (int) ($validated['quantidade'] ?? 1);

        $presente = PresenteAlunoProcessor::enviar(
            $user->aluno,
            $destinatario,
            $alunoItem,
            $qtd,
            $validated['mensagem'] ?? null
        );

        return response()->json([
            'message' => 'Presente enviado com sucesso!',
            'data' => [
                'id' => $presente->id,
                'quantidade' => (int) $presente->quantidade,
                'destinatario' => [
                    'id' => $destinatario->id,
                    'nome' => $destinatario->nome,
                ],
            ],
        ], 201);
    }

    private function resolveAluno(User $user, Request $request): Aluno
    {
        if ($request->filled('id_aluno')) {
            if (! $this->isStaff($user)) {
                abort(403, 'Sem permissão para consultar inventário de outro aluno.');
            }

            $aluno = Aluno::findOrFail((int) $request->integer('id_aluno'));

            if (! $this->canAccessAluno($user, $aluno)) {
                abort(403, 'Sem permissão para consultar este aluno.');
            }

            return $aluno;
        }

        if ($this->isAluno($user) && $user->aluno) {
            return $user->aluno;
        }

        abort(403, 'Somente alunos possuem inventário.');
    }

    private function canAccessAluno(User $user, Aluno $aluno): bool
    {
        $role = $user->access_role ?? 'professor';

        if ($role === 'master') {
            return true;
        }

        return (int) $user->unidade_id === (int) $aluno->unidade_id;
    }

    private function formatarAluno(Aluno $aluno): array
    {
        return [
            'id' => $aluno->id,
            'nome' => $aluno->nome,
            'coins' => (int) $aluno->coins,
            'xp' => (int) $aluno->xp,
        ];
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        $user->loadMissing('aluno');

        return $user;
    }

    private function isAluno(User $user): bool
    {
        return ($user->access_role ?? 'professor') === 'aluno';
    }

    private function isStaff(User $user): bool
    {
        return in_array($user->access_role ?? 'professor', ['master', 'direcao', 'professor'], true);
    }
}
