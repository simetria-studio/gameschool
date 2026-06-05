<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Roleta;
use App\Models\RoletaGiro;
use App\Models\User;
use App\Support\RoletaGiroProcessor;
use App\Support\RoletaImagemStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoletaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Roleta::query()
            ->with(['unidade:id,titulo', 'turmas:id,nome'])
            ->withCount('segmentos')
            ->orderByDesc('created_at');

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('status', 'ativa')
                ->where(function (Builder $q) {
                    $q->whereNull('data_encerramento')
                        ->orWhereDate('data_encerramento', '>=', now()->toDateString());
                })
                ->where('unidade_id', $user->aluno->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->where('turmas.id', $user->aluno->turma_id))
                ->whereHas('segmentos', fn (Builder $q) => $q->where('peso', '>', 0));
        } elseif ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            $query->where('unidade_id', $user->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->whereIn('turmas.id', $ids ?: [0]));
        } elseif (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        $paginated = $query->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => $paginated->getCollection()->map(fn (Roleta $r) => $this->formatResumo($r, $user)),
            'meta' => $this->meta($paginated),
        ]);
    }

    public function show(Request $request, Roleta $roleta): JsonResponse
    {
        $user = $this->user($request);
        $this->authorizeAccess($user, $roleta);

        $roleta->load(['unidade:id,titulo', 'turmas:id,nome', 'segmentos.item']);

        return response()->json([
            'data' => $this->formatDetalhe($roleta, $user),
        ]);
    }

    public function status(Request $request, Roleta $roleta): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403);
        }

        $this->authorizeAccess($user, $roleta);

        $gratis = RoletaGiroProcessor::statusGiroGratis($roleta, $user->aluno);

        return response()->json([
            'data' => [
                'giro_gratis' => $gratis,
                'somente_gratis' => (bool) $roleta->somente_gratis,
                'giros_gratis_por_semana' => (int) $roleta->giros_gratis_por_semana,
                'custo_coins' => (int) $roleta->custo_coins,
                'coins_aluno' => (int) $user->aluno->coins,
            ],
        ]);
    }

    public function storeGiro(Request $request, Roleta $roleta): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403, 'Somente alunos podem girar a roleta.');
        }

        $this->authorizeAccess($user, $roleta);

        if ($roleta->somente_gratis) {
            $tipo = 'gratis';
        } else {
            $validated = $request->validate([
                'tipo' => ['required', 'in:gratis,pago'],
            ], [], ['tipo' => 'tipo de giro']);
            $tipo = $validated['tipo'];
        }

        $giro = RoletaGiroProcessor::girar($roleta, $user->aluno, $tipo);
        $user->aluno->refresh();

        return response()->json([
            'message' => 'Roleta girada com sucesso!',
            'data' => [
                'giro' => $this->formatGiro($giro),
                'coins_aluno' => (int) $user->aluno->coins,
                'xp_aluno' => (int) $user->aluno->xp,
            ],
        ], 201);
    }

    public function giros(Request $request, Roleta $roleta): JsonResponse
    {
        $user = $this->user($request);
        $this->authorizeAccess($user, $roleta);

        $query = RoletaGiro::query()
            ->where('roleta_id', $roleta->id)
            ->with(['aluno:id,nome', 'segmento:id,titulo,tipo'])
            ->orderByDesc('created_at');

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('aluno_id', $user->aluno->id);
        } elseif (! $this->isMaster($user)) {
            $query->whereHas('aluno', fn (Builder $q) => $q->where('unidade_id', $user->unidade_id));
            if ($this->isProfessor($user)) {
                $ids = $user->turmas()->pluck('turmas.id')->all();
                $query->whereHas('aluno', fn (Builder $q) => $q->whereIn('turma_id', $ids ?: [0]));
            }
        }

        $paginated = $query->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'data' => $paginated->getCollection()->map(fn (RoletaGiro $g) => $this->formatGiro($g)),
            'meta' => $this->meta($paginated),
        ]);
    }

    private function formatResumo(Roleta $roleta, User $user): array
    {
        $data = [
            'id' => $roleta->id,
            'titulo' => $roleta->titulo,
            'descricao' => $roleta->descricao,
            'custo_coins' => (int) $roleta->custo_coins,
            'giros_gratis_por_semana' => (int) $roleta->giros_gratis_por_semana,
            'somente_gratis' => (bool) $roleta->somente_gratis,
            'status' => $roleta->status,
            'total_segmentos' => (int) ($roleta->segmentos_count ?? 0),
            'unidade' => $roleta->unidade ? ['id' => $roleta->unidade->id, 'titulo' => $roleta->unidade->titulo] : null,
            'turmas' => $roleta->turmas->map(fn ($t) => ['id' => $t->id, 'nome' => $t->nome])->values(),
        ];

        if ($this->isAluno($user) && $user->aluno) {
            $data['giro_gratis'] = RoletaGiroProcessor::statusGiroGratis($roleta, $user->aluno);
        }

        return $data;
    }

    private function formatDetalhe(Roleta $roleta, User $user): array
    {
        $resumo = $this->formatResumo($roleta, $user);

        $resumo['segmentos'] = $roleta->segmentos->map(function ($s) {
            $segmento = [
                'id' => $s->id,
                'titulo' => $s->titulo,
                'tipo' => $s->tipo,
                'cor' => $s->cor,
                'ordem' => (int) $s->ordem,
            ];

            return match ($s->tipo) {
                'item' => array_merge($segmento, [
                    'item' => $s->item ? [
                        'id' => $s->item->id,
                        'titulo' => $s->item->titulo,
                        'tipo' => $s->item->tipo,
                        'emoji' => $s->item->emoji,
                        'imagem' => $s->item->imagem,
                        'imagem_url' => RoletaImagemStorage::urlPublica($s->item->imagem),
                        'raridade' => $s->item->raridade,
                    ] : null,
                ]),
                'coins' => array_merge($segmento, [
                    'coins' => (int) $s->coins,
                    'emoji' => '🪙',
                ]),
                'xp' => array_merge($segmento, [
                    'xp' => (int) $s->xp,
                    'emoji' => '⭐',
                ]),
                'bau' => array_merge($segmento, [
                    'emoji' => '🎁',
                ]),
                default => $segmento,
            };
        })->values();

        return $resumo;
    }

    private function formatGiro(RoletaGiro $giro): array
    {
        $premios = collect($giro->premios_json ?? [])->map(function ($p) {
            if (! is_array($p)) {
                return $p;
            }

            if (! empty($p['imagem'])) {
                $p['imagem_url'] = RoletaImagemStorage::urlPublica($p['imagem']);
            }

            return $p;
        })->values()->all();

        return [
            'id' => $giro->id,
            'tipo' => $giro->tipo,
            'custo_coins' => (int) $giro->custo_coins,
            'coins_ganho' => (int) $giro->coins_ganho,
            'xp_ganho' => (int) $giro->xp_ganho,
            'premios' => $premios,
            'segmento' => $giro->segmento ? [
                'id' => $giro->segmento->id,
                'titulo' => $giro->segmento->titulo,
                'tipo' => $giro->segmento->tipo,
            ] : null,
            'aluno' => $giro->aluno ? ['id' => $giro->aluno->id, 'nome' => $giro->aluno->nome] : null,
            'created_at' => $giro->created_at?->toIso8601String(),
        ];
    }

    private function authorizeAccess(User $user, Roleta $roleta): void
    {
        if ($this->isMaster($user)) {
            return;
        }

        if ($this->isAluno($user) && $user->aluno) {
            if ((int) $roleta->unidade_id !== (int) $user->aluno->unidade_id) {
                abort(403);
            }
            if (! $roleta->turmas()->where('turmas.id', $user->aluno->turma_id)->exists()) {
                abort(403);
            }
            if (! $roleta->isAtiva()) {
                abort(403, 'Roleta indisponível.');
            }

            return;
        }

        if ((int) $roleta->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            if (! $roleta->turmas()->whereIn('turmas.id', $ids ?: [0])->exists()) {
                abort(403);
            }
        }
    }

    private function meta($paginated): array
    {
        return [
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ];
    }

    private function user(Request $request): User
    {
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
