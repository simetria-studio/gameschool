<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacaoApiController extends Controller
{
    /**
     * Lista notificações do usuário autenticado (destinado ao app do aluno).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (($user->access_role ?? '') !== 'aluno') {
            return response()->json([
                'message' => 'Notificações neste endpoint são apenas para perfil aluno.',
                'data' => [],
                'meta' => ['unread_count' => 0],
            ], 403);
        }

        $user->loadMissing('aluno');
        $aluno = $user->aluno;
        if (! $aluno) {
            return response()->json([
                'message' => 'Nenhum cadastro de aluno vinculado a este usuário.',
                'data' => [],
                'meta' => ['unread_count' => 0],
            ], 422);
        }

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 50);

        $unreadCount = $aluno->unreadNotifications()->count();

        $paginator = $aluno->notifications()
            ->latest()
            ->paginate($perPage);

        $paginator->through(function ($n) {
            return [
                'id' => $n->id,
                'type' => class_basename($n->type),
                'read' => $n->read_at !== null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
                ...$n->data,
            ];
        });

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'unread_count' => $unreadCount,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    public function marcarLida(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (($user->access_role ?? '') !== 'aluno') {
            abort(403);
        }

        $user->loadMissing('aluno');
        $aluno = $user->aluno;
        if (! $aluno) {
            abort(422, 'Nenhum cadastro de aluno vinculado a este usuário.');
        }

        $notification = $aluno->notifications()->whereKey($id)->first();
        if (! $notification) {
            abort(404, 'Notificação não encontrada.');
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notificação marcada como lida.',
            'data' => [
                'id' => $notification->id,
                'read' => true,
            ],
        ]);
    }

    public function marcarTodasLidas(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (($user->access_role ?? '') !== 'aluno') {
            abort(403);
        }

        $user->loadMissing('aluno');
        $aluno = $user->aluno;
        if (! $aluno) {
            abort(422, 'Nenhum cadastro de aluno vinculado a este usuário.');
        }

        $aluno->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas.',
        ]);
    }
}
