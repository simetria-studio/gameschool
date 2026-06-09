<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\User;
use App\Support\FigurinhaAlbum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FigurinhaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $aluno = $this->resolveAluno($user, $request);

        return response()->json([
            'data' => FigurinhaAlbum::montar($aluno),
        ]);
    }

    private function resolveAluno(User $user, Request $request): Aluno
    {
        if ($request->filled('id_aluno')) {
            if (! $this->isStaff($user)) {
                abort(403, 'Sem permissão para consultar álbum de outro aluno.');
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

        abort(403, 'Somente alunos possuem álbum de figurinhas.');
    }

    private function canAccessAluno(User $user, Aluno $aluno): bool
    {
        if (($user->access_role ?? 'professor') === 'master') {
            return true;
        }

        return (int) $user->unidade_id === (int) $aluno->unidade_id;
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
