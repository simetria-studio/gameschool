<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\Quiz;
use App\Models\QuizOpcao;
use App\Models\QuizResposta;
use App\Models\QuizTentativa;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizTentativaProcessor
{
    /**
     * @param  array<int, array{pergunta_id: int, opcao_id: int}>  $respostas
     */
    public static function processar(Quiz $quiz, Aluno $aluno, array $respostas): QuizTentativa
    {
        $quiz->load(['perguntas.opcoes']);

        if (! $quiz->isAtivo()) {
            throw ValidationException::withMessages([
                'quiz' => ['Este quiz não está disponível.'],
            ]);
        }

        if ($quiz->perguntas->isEmpty()) {
            throw ValidationException::withMessages([
                'quiz' => ['Este quiz ainda não possui perguntas.'],
            ]);
        }

        self::assertTentativasDisponiveis($quiz, $aluno);

        $perguntaIds = $quiz->perguntas->pluck('id')->all();
        $respostasMap = self::normalizarRespostas($respostas, $perguntaIds);

        return DB::transaction(function () use ($quiz, $aluno, $respostasMap) {
            $acertos = 0;
            $total = $quiz->perguntas->count();
            $linhasResposta = [];

            foreach ($quiz->perguntas as $pergunta) {
                $opcaoId = $respostasMap[$pergunta->id];
                $opcao = $pergunta->opcoes->firstWhere('id', $opcaoId);
                $correta = $opcao && $opcao->correta;

                if ($correta) {
                    $acertos++;
                }

                $linhasResposta[] = [
                    'pergunta_id' => $pergunta->id,
                    'opcao_id' => $opcaoId,
                    'correta' => $correta,
                ];
            }

            $nota = $total > 0 ? (int) round(($acertos / $total) * 100) : 0;
            $aprovado = $nota >= (int) $quiz->nota_minima;

            $jaRecompensado = QuizTentativa::query()
                ->where('quiz_id', $quiz->id)
                ->where('aluno_id', $aluno->id)
                ->where('aprovado', true)
                ->where(function ($q) {
                    $q->where('xp_ganho', '>', 0)->orWhere('coins_ganho', '>', 0);
                })
                ->exists();

            $xpGanho = 0;
            $coinsGanho = 0;

            if ($aprovado && ! $jaRecompensado) {
                $xpGanho = (int) $quiz->xp;
                $coinsGanho = (int) $quiz->coins;

                if ($xpGanho !== 0 || $coinsGanho !== 0) {
                    $aluno->increment('xp', $xpGanho);
                    $aluno->increment('coins', $coinsGanho);
                    NotificarRecompensaAluno::porQuiz($aluno->fresh(), $quiz, $xpGanho, $coinsGanho);
                }
            }

            $tentativa = QuizTentativa::create([
                'quiz_id' => $quiz->id,
                'aluno_id' => $aluno->id,
                'acertos' => $acertos,
                'total_perguntas' => $total,
                'nota' => $nota,
                'xp_ganho' => $xpGanho,
                'coins_ganho' => $coinsGanho,
                'aprovado' => $aprovado,
                'completed_at' => now(),
            ]);

            foreach ($linhasResposta as $linha) {
                QuizResposta::create([
                    'quiz_tentativa_id' => $tentativa->id,
                    'pergunta_id' => $linha['pergunta_id'],
                    'opcao_id' => $linha['opcao_id'],
                    'correta' => $linha['correta'],
                ]);
            }

            return $tentativa->load([
                'respostas.pergunta',
                'respostas.opcao',
            ]);
        });
    }

    private static function assertTentativasDisponiveis(Quiz $quiz, Aluno $aluno): void
    {
        $max = (int) $quiz->tentativas_max;

        if ($max === 0) {
            return;
        }

        $usadas = QuizTentativa::query()
            ->where('quiz_id', $quiz->id)
            ->where('aluno_id', $aluno->id)
            ->count();

        if ($usadas >= $max) {
            throw ValidationException::withMessages([
                'quiz' => ['Você atingiu o número máximo de tentativas para este quiz.'],
            ]);
        }
    }

    /**
     * @param  array<int, array{pergunta_id: int, opcao_id: int}>  $respostas
     * @param  array<int, int>  $perguntaIds
     * @return array<int, int>
     */
    private static function normalizarRespostas(array $respostas, array $perguntaIds): array
    {
        $map = [];

        foreach ($respostas as $item) {
            $perguntaId = (int) ($item['pergunta_id'] ?? 0);
            $opcaoId = (int) ($item['opcao_id'] ?? 0);

            if ($perguntaId <= 0 || $opcaoId <= 0) {
                throw ValidationException::withMessages([
                    'respostas' => ['Informe uma opção válida para cada pergunta.'],
                ]);
            }

            $map[$perguntaId] = $opcaoId;
        }

        if (count($map) !== count($perguntaIds)) {
            throw ValidationException::withMessages([
                'respostas' => ['Responda todas as perguntas do quiz.'],
            ]);
        }

        foreach ($perguntaIds as $pid) {
            if (! array_key_exists($pid, $map)) {
                throw ValidationException::withMessages([
                    'respostas' => ['Responda todas as perguntas do quiz.'],
                ]);
            }
        }

        $opcoes = QuizOpcao::query()
            ->whereIn('id', array_values($map))
            ->with('pergunta:id,quiz_id')
            ->get()
            ->keyBy('id');

        foreach ($map as $perguntaId => $opcaoId) {
            if (! in_array($perguntaId, $perguntaIds, true)) {
                throw ValidationException::withMessages([
                    'respostas' => ['Pergunta inválida para este quiz.'],
                ]);
            }

            $opcao = $opcoes->get($opcaoId);

            if (! $opcao || (int) $opcao->pergunta_id !== $perguntaId) {
                throw ValidationException::withMessages([
                    'respostas' => ['Opção inválida para a pergunta #' . $perguntaId . '.'],
                ]);
            }
        }

        return $map;
    }
}
