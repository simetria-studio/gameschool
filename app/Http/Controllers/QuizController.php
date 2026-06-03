<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Quiz;
use App\Models\QuizPergunta;
use App\Models\QuizTentativa;
use App\Models\Turma;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizController extends Controller
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

        $query = Quiz::with(['unidade', 'turmas'])
            ->withCount(['perguntas', 'tentativas'])
            ->when($isProfessor, function ($q) use ($professorTurmaIds) {
                if ($professorTurmaIds === []) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereHas('turmas', fn ($qt) => $qt->whereIn('turmas.id', $professorTurmaIds));
                }
            })
            ->when(! $isMaster && ! $isProfessor, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('titulo', 'like', '%' . $search . '%')
                        ->orWhere('descricao', 'like', '%' . $search . '%')
                        ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'))
                        ->orWhereHas('turmas', fn ($t) => $t->where('nome', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy('created_at', 'desc');

        $quizzes = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        $turmasQuery = Turma::query()->orderBy('nome');
        if (! $isMaster) {
            $turmasQuery->where('unidade_id', $user->unidade_id);
        }
        $turmas = $turmasQuery->get(['id', 'nome', 'unidade_id']);

        return view('quizzes.index', [
            'quizzes' => $quizzes,
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

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateQuiz($request);

        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);

        if ($isProfessor) {
            $this->assertProfessorOwnsTurmas($user, $validated['turma_ids']);
            if ((int) $validated['unidade_id'] !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $quiz = Quiz::create($validated);
        $quiz->turmas()->sync($turmaIds);

        return redirect()
            ->route('quizzes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Quiz adicionado com sucesso.');
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateQuiz($request);
        $this->assertTurmasBelongToUnidade($validated['unidade_id'], $validated['turma_ids']);

        if ($isProfessor) {
            $this->assertProfessorOwnsTurmas($user, $validated['turma_ids']);
        }

        $turmaIds = $validated['turma_ids'];
        unset($validated['turma_ids']);

        $quiz->update($validated);
        $quiz->turmas()->sync($turmaIds);

        return redirect()
            ->route('quizzes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Quiz atualizado com sucesso.');
    }

    public function destroy(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        $quiz->turmas()->detach();
        $quiz->delete();

        return redirect()
            ->route('quizzes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Quiz excluído com sucesso.');
    }

    public function perguntas(Quiz $quiz): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        $quiz->load(['unidade', 'turmas', 'perguntas.opcoes']);

        return view('quizzes.perguntas', [
            'quiz' => $quiz,
        ]);
    }

    public function storePergunta(Request $request, Quiz $quiz): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        $opcoes = collect($request->input('opcoes', []))
            ->filter(fn ($o) => trim((string) ($o['texto'] ?? '')) !== '')
            ->values()
            ->all();

        $request->merge(['opcoes' => $opcoes]);

        $validated = $this->validatePergunta($request);

        DB::transaction(function () use ($quiz, $validated) {
            $ordem = ((int) $quiz->perguntas()->max('ordem')) + 1;

            $pergunta = $quiz->perguntas()->create([
                'enunciado' => $validated['enunciado'],
                'ordem' => $ordem,
            ]);

            foreach ($validated['opcoes'] as $opcao) {
                $pergunta->opcoes()->create([
                    'texto' => $opcao['texto'],
                    'correta' => (bool) $opcao['correta'],
                ]);
            }
        });

        return redirect()
            ->route('quizzes.perguntas', $quiz)
            ->with('success', 'Pergunta adicionada com sucesso.');
    }

    public function destroyPergunta(Quiz $quiz, QuizPergunta $pergunta): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        if ((int) $pergunta->quiz_id !== (int) $quiz->id) {
            abort(404);
        }

        $pergunta->delete();

        return redirect()
            ->route('quizzes.perguntas', $quiz)
            ->with('success', 'Pergunta excluída com sucesso.');
    }

    public function tentativas(Request $request, Quiz $quiz): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;
        $search = trim((string) $request->get('search', ''));

        $query = Aluno::query()
            ->with(['turma:id,nome'])
            ->with(['tentativas' => function ($q) use ($quiz) {
                $q->where('quiz_id', $quiz->id)
                    ->withCount('respostas')
                    ->orderByDesc('completed_at');
            }])
            ->whereHas('tentativas', fn ($q) => $q->where('quiz_id', $quiz->id))
            ->when($search !== '', fn ($q) => $q->where('nome', 'like', '%' . $search . '%'));

        if (! $isMaster) {
            $query->where('unidade_id', $user->unidade_id);
        }

        if ($isProfessor) {
            $ids = $user->turmas()->pluck('id')->all();
            $query->whereIn('turma_id', $ids ?: [0]);
        }

        $query->orderByDesc(
            QuizTentativa::query()
                ->select('completed_at')
                ->whereColumn('aluno_id', 'alunos.id')
                ->where('quiz_id', $quiz->id)
                ->latest('completed_at')
                ->limit(1)
        );

        $alunos = $query->paginate($perPage)->withQueryString();
        $quiz->load(['unidade', 'turmas']);

        return view('quizzes.tentativas', [
            'quiz' => $quiz,
            'alunos' => $alunos,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function showTentativa(Quiz $quiz, QuizTentativa $tentativa): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';
        $isProfessor = ($user->access_role ?? 'professor') === 'professor';

        $this->assertCanManageQuiz($user, $quiz, $isMaster, $isProfessor);

        if ((int) $tentativa->quiz_id !== (int) $quiz->id) {
            abort(404);
        }

        $this->assertTentativaVisivel($tentativa, $user, $isMaster, $isProfessor);

        $tentativa->load([
            'aluno.turma:id,nome',
            'respostas.pergunta',
            'respostas.opcao',
        ]);

        $quiz->load(['unidade', 'turmas']);

        $respostasOrdenadas = $tentativa->respostas
            ->sortBy(fn ($r) => $r->pergunta?->ordem ?? 0)
            ->values();

        return view('quizzes.tentativa', [
            'quiz' => $quiz,
            'tentativa' => $tentativa,
            'respostas' => $respostasOrdenadas,
        ]);
    }

    private function scopedTentativasQuery(Quiz $quiz, $user, bool $isMaster, bool $isProfessor)
    {
        $query = QuizTentativa::query()->where('quiz_id', $quiz->id);

        if ($isMaster) {
            return $query;
        }

        $query->whereHas('aluno', fn ($a) => $a->where('unidade_id', $user->unidade_id));

        if ($isProfessor) {
            $ids = $user->turmas()->pluck('id')->all();
            $query->whereHas('aluno', fn ($a) => $a->whereIn('turma_id', $ids ?: [0]));
        }

        return $query;
    }

    private function assertTentativaVisivel(QuizTentativa $tentativa, $user, bool $isMaster, bool $isProfessor): void
    {
        if ($isMaster) {
            return;
        }

        $tentativa->loadMissing('aluno');

        if ((int) ($tentativa->aluno->unidade_id ?? 0) !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($isProfessor) {
            $allowed = $user->turmas()->pluck('id')->all();
            if (! in_array((int) ($tentativa->aluno->turma_id ?? 0), $allowed, true)) {
                abort(403);
            }
        }
    }

    private function validateQuiz(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_ids' => ['required', 'array', 'min:1'],
            'turma_ids.*' => ['integer', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'xp' => ['required', 'integer', 'min:0'],
            'coins' => ['required', 'integer', 'min:0'],
            'nota_minima' => ['required', 'integer', 'min:0', 'max:100'],
            'tentativas_max' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'turma_ids' => 'turmas',
            'descricao' => 'descrição',
            'nota_minima' => 'nota mínima',
            'tentativas_max' => 'tentativas máximas',
            'data_encerramento' => 'data de encerramento',
        ]);
    }

    private function validatePergunta(Request $request): array
    {
        $validated = $request->validate([
            'enunciado' => ['required', 'string', 'max:2000'],
            'opcoes' => ['required', 'array', 'min:2', 'max:6'],
            'opcoes.*.texto' => ['required', 'string', 'max:500'],
            'opcoes.*.correta' => ['required', 'boolean'],
        ], [], [
            'enunciado' => 'enunciado',
            'opcoes' => 'opções',
            'opcoes.*.texto' => 'texto da opção',
            'opcoes.*.correta' => 'opção correta',
        ]);

        $corretas = collect($validated['opcoes'])->where('correta', true)->count();

        if ($corretas !== 1) {
            throw ValidationException::withMessages([
                'opcoes' => 'Marque exatamente uma opção como correta.',
            ]);
        }

        return $validated;
    }

    private function assertCanManageQuiz($user, Quiz $quiz, bool $isMaster, bool $isProfessor): void
    {
        if ($isMaster) {
            return;
        }

        if ((int) $quiz->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($isProfessor) {
            $allowed = $user->turmas()->pluck('id')->all();
            $quizTurmaIds = $quiz->turmas()->pluck('turmas.id')->all();

            if ($quizTurmaIds === [] || count(array_intersect($quizTurmaIds, $allowed)) === 0) {
                abort(403);
            }
        }
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
