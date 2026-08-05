<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\AvatarPeca;
use App\Models\User;
use App\Support\AvatarAluno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvatarApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $aluno = $this->resolveAluno($request);

        $avatar = $aluno->avatar;

        if ($avatar) {
            $avatar = AvatarAluno::completarConfig($aluno, $avatar);
        }

        return response()->json([
            'data' => [
                'aluno' => [
                    'id' => $aluno->id,
                    'nome' => $aluno->nome,
                ],
                'avatar' => AvatarAluno::formatarAvatar($avatar),
                'precisa_escolher_genero' => $avatar === null,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $aluno = $this->resolveAluno($request);

        $slotRules = [];
        foreach (AvatarAluno::SLOTS_EQUIPAVEIS as $slot) {
            $slotRules["slots.$slot"] = ['nullable', 'integer'];
        }

        $validated = $request->validate(array_merge([
            'genero' => ['required', 'in:masculino,feminino'],
            'slots' => ['nullable', 'array'],
        ], $slotRules));

        $avatar = AvatarAluno::salvar($aluno, $validated);

        return response()->json([
            'data' => [
                'avatar' => AvatarAluno::formatarAvatar($avatar),
            ],
            'message' => 'Avatar salvo com sucesso.',
        ]);
    }

    public function pecas(Request $request): JsonResponse
    {
        $aluno = $this->resolveAluno($request);
        $avatar = $aluno->avatar;
        $generoFiltro = $request->input('genero', $avatar?->genero);

        $query = AvatarPeca::query()
            ->where('status', 'ativo')
            ->whereIn('slot', AvatarPeca::SLOTS_ATIVOS)
            ->when($request->filled('slot'), fn ($q) => $q->where('slot', $request->string('slot')))
            ->when($generoFiltro, function ($q) use ($generoFiltro) {
                $q->where(function ($inner) use ($generoFiltro) {
                    $inner->where('genero', $generoFiltro)
                        ->orWhere('genero', 'unissex');
                });
            })
            ->when($aluno->unidade_id, function ($q) use ($aluno) {
                $q->where(function ($inner) use ($aluno) {
                    $inner->whereNull('unidade_id')
                        ->orWhere('unidade_id', $aluno->unidade_id);
                });
            }, fn ($q) => $q->whereNull('unidade_id'))
            ->orderBy('slot')
            ->orderBy('titulo');

        $idsPossuidos = $aluno->avatarPecas()->pluck('avatar_pecas.id')->all();

        $itens = $query->get()->map(function (AvatarPeca $peca) use ($idsPossuidos, $generoFiltro) {
            $possui = in_array($peca->id, $idsPossuidos, true)
                || ($peca->is_starter && (! $generoFiltro || $peca->compativelComGenero($generoFiltro)));

            return AvatarAluno::formatarPeca($peca, $possui);
        });

        return response()->json([
            'data' => [
                'slots' => AvatarPeca::SLOTS_ATIVOS,
                'slot_labels' => collect(AvatarPeca::SLOTS_ATIVOS)
                    ->mapWithKeys(fn ($s) => [$s => AvatarPeca::SLOT_LABELS[$s] ?? $s])
                    ->all(),
                'itens' => $itens,
            ],
        ]);
    }

    private function resolveAluno(Request $request): Aluno
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('aluno');

        if (($user->access_role ?? '') === 'aluno' && $user->aluno) {
            return $user->aluno;
        }

        abort(403, 'Somente alunos podem personalizar o avatar.');
    }
}
