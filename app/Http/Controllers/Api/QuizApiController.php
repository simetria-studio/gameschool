<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizTentativa;
use App\Models\User;
use App\Support\QuizTentativaProcessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $query = Quiz::query()
            ->with(['unidade:id,titulo', 'turmas:id,nome'])
            ->withCount('perguntas')
            ->orderByDesc('created_at');

        if ($this->isAluno($user) && $user->aluno) {
            $query->where('status', 'ativa')
                ->where(function (Builder $q) {
                    $q->whereNull('data_encerramento')
                        ->orWhereDate('data_encerramento', '>=', now()->toDateString());
                })
                ->where('unidade_id', $user->aluno->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->where('turmas.id', $user->aluno->turma_id));
        } elseif ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            $query->where('unidade_id', $user->unidade_id)
                ->whereHas('turmas', fn (Builder $q) => $q->whereIn('turmas.id', $ids ?: [0]));
        } elseif (! $this->isMaster($user)) {
            $query->where('unidade_id', $user->unidade_id);
        }

        $paginated = $query->paginate((int) $request->integer('per_page', 20));

        $items = $paginated->getCollection()->map(function (Quiz $quiz) use ($user) {
            return $this->formatQuizResumo($quiz, $user);
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function show(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $this->user($request);
        $this->authorizeQuizAccess($user, $quiz);

        $quiz->load(['unidade:id,titulo', 'turmas:id,nome', 'perguntas.opcoes']);

        $revelarRespostas = $request->boolean('revelar_respostas', false);

        return response()->json([
            'data' => $this->formatQuizDetalhe($quiz, $user, $revelarRespostas),
        ]);
    }

    public function storeTentativa(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $this->user($request);

        if (! $this->isAluno($user) || ! $user->aluno) {
            abort(403, 'Somente alunos podem responder quizzes.');
        }

        $this->authorizeQuizAccess($user, $quiz);

        $validated = $request->validate([
            'respostas' => ['required', 'array', 'min:1'],
            'respostas.*.pergunta_id' => ['required', 'integer'],
            'respostas.*.opcao_id' => ['required', 'integer'],
        ], [], [
            'respostas' => 'respostas',
            'respostas.*.pergunta_id' => 'pergunta',
            'respostas.*.opcao_id' => 'opção',
        ]);

        $tentativa = QuizTentativaProcessor::processar($quiz, $user->aluno, $validated['respostas']);
        $quiz->load(['unidade:id,titulo', 'turmas:id,nome', 'perguntas.opcoes']);

        return response()->json([
            'message' => $tentativa->aprovado
                ? 'Quiz concluído com sucesso!'
                : 'Quiz concluído. Você não atingiu a nota mínima.',
            'data' => [
                'tentativa' => $this->formatTentativa($tentativa, true),
                'quiz' => $this->formatQuizDetalhe($quiz, $user, true),
            ],
        ], 201);
    }

    public function showTentativa(Request $request, Quiz $quiz, QuizTentativa $tentativa): JsonResponse
    {
        $user = $this->user($request);
        $this->authorizeQuizAccess($user, $quiz);

        if ((int) $tentativa->quiz_id !== (int) $quiz->id) {
            abort(404);
        }

        $this->authorizeTentativaAccess($user, $tentativa);

        $tentativa->load([
            'aluno:id,nome,turma_id',
            'respostas.pergunta:id,enunciado,ordem',
            'respostas.opcao:id,texto,correta',
        ]);

        return response()->json([
            'data' => $this->formatTentativa($tentativa, true),
        ]);
    }

    public function tentativas(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $this->user($request);
        $this->authorizeQuizAccess($user, $quiz);

        $query = QuizTentativa::query()
            ->where('quiz_id', $quiz->id)
            ->with('aluno:id,nome,turma_id')
            ->withCount('respostas')
            ->orderByDesc('completed_at');

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
        $comRespostas = $request->boolean('com_respostas', false);

        if ($comRespostas) {
            $paginated->getCollection()->load([
                'respostas.pergunta:id,enunciado,ordem',
                'respostas.opcao:id,texto,correta',
            ]);
        }

        return response()->json([
            'data' => $paginated->getCollection()->map(fn (QuizTentativa $t) => $this->formatTentativa($t, $comRespostas)),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    private function authorizeQuizAccess(User $user, Quiz $quiz): void
    {
        if ($this->isMaster($user)) {
            return;
        }

        if ($this->isAluno($user) && $user->aluno) {
            if ((int) $quiz->unidade_id !== (int) $user->aluno->unidade_id) {
                abort(403);
            }

            $turmaOk = $quiz->turmas()->where('turmas.id', $user->aluno->turma_id)->exists();
            if (! $turmaOk) {
                abort(403);
            }

            if (! $quiz->isAtivo()) {
                abort(403, 'Este quiz não está disponível.');
            }

            return;
        }

        if ((int) $quiz->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            $ok = $quiz->turmas()->whereIn('turmas.id', $ids ?: [0])->exists();
            if (! $ok) {
                abort(403);
            }
        }
    }

    private function formatQuizResumo(Quiz $quiz, User $user): array
    {
        $data = [
            'id' => $quiz->id,
            'titulo' => $quiz->titulo,
            'descricao' => $quiz->descricao,
            'xp' => (int) $quiz->xp,
            'coins' => (int) $quiz->coins,
            'nota_minima' => (int) $quiz->nota_minima,
            'tentativas_max' => (int) $quiz->tentativas_max,
            'status' => $quiz->status,
            'data_encerramento' => $quiz->data_encerramento?->format('Y-m-d'),
            'total_perguntas' => (int) ($quiz->perguntas_count ?? $quiz->perguntas()->count()),
            'unidade' => $quiz->unidade ? ['id' => $quiz->unidade->id, 'titulo' => $quiz->unidade->titulo] : null,
            'turmas' => $quiz->turmas->map(fn ($t) => ['id' => $t->id, 'nome' => $t->nome])->values(),
        ];

        if ($this->isAluno($user) && $user->aluno) {
            $tentativasUsadas = QuizTentativa::query()
                ->where('quiz_id', $quiz->id)
                ->where('aluno_id', $user->aluno->id)
                ->count();

            $aprovado = QuizTentativa::query()
                ->where('quiz_id', $quiz->id)
                ->where('aluno_id', $user->aluno->id)
                ->where('aprovado', true)
                ->exists();

            $max = (int) $quiz->tentativas_max;
            $restantes = $max === 0 ? null : max(0, $max - $tentativasUsadas);

            $data['tentativas_usadas'] = $tentativasUsadas;
            $data['tentativas_restantes'] = $restantes;
            $data['aprovado'] = $aprovado;
        }

        return $data;
    }

    private function formatQuizDetalhe(Quiz $quiz, User $user, bool $revelarRespostas): array
    {
        $resumo = $this->formatQuizResumo($quiz, $user);

        $resumo['perguntas'] = $quiz->perguntas->map(function ($pergunta) use ($revelarRespostas) {
            return [
                'id' => $pergunta->id,
                'enunciado' => $pergunta->enunciado,
                'ordem' => (int) $pergunta->ordem,
                'opcoes' => $pergunta->opcoes->map(function ($opcao) use ($revelarRespostas) {
                    $item = [
                        'id' => $opcao->id,
                        'texto' => $opcao->texto,
                    ];

                    if ($revelarRespostas) {
                        $item['correta'] = (bool) $opcao->correta;
                    }

                    return $item;
                })->values(),
            ];
        })->values();

        return $resumo;
    }

    private function authorizeTentativaAccess(User $user, QuizTentativa $tentativa): void
    {
        $tentativa->loadMissing('aluno');

        if ($this->isAluno($user) && $user->aluno) {
            if ((int) $tentativa->aluno_id !== (int) $user->aluno->id) {
                abort(403);
            }

            return;
        }

        if ($this->isMaster($user)) {
            return;
        }

        if ((int) ($tentativa->aluno->unidade_id ?? 0) !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($this->isProfessor($user)) {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            if (! in_array((int) ($tentativa->aluno->turma_id ?? 0), $ids, true)) {
                abort(403);
            }
        }
    }

    private function formatTentativa(QuizTentativa $tentativa, bool $comRespostas = false): array
    {
        $data = [
            'id' => $tentativa->id,
            'quiz_id' => $tentativa->quiz_id,
            'aluno' => $tentativa->aluno ? [
                'id' => $tentativa->aluno->id,
                'nome' => $tentativa->aluno->nome,
            ] : null,
            'acertos' => (int) $tentativa->acertos,
            'total_perguntas' => (int) $tentativa->total_perguntas,
            'nota' => (int) $tentativa->nota,
            'aprovado' => (bool) $tentativa->aprovado,
            'xp_ganho' => (int) $tentativa->xp_ganho,
            'coins_ganho' => (int) $tentativa->coins_ganho,
            'completed_at' => $tentativa->completed_at?->toIso8601String(),
        ];

        if ($comRespostas && $tentativa->relationLoaded('respostas')) {
            $data['respostas'] = $tentativa->respostas
                ->sortBy(fn ($r) => $r->pergunta?->ordem ?? 0)
                ->values()
                ->map(fn ($r) => [
                    'pergunta_id' => $r->pergunta_id,
                    'enunciado' => $r->pergunta?->enunciado,
                    'opcao_id' => $r->opcao_id,
                    'opcao_texto' => $r->opcao?->texto,
                    'correta' => (bool) $r->correta,
                ]);
        }

        return $data;
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
