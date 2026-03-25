<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        if (! Auth::attempt(['username' => $validated['username'], 'password' => $validated['password']])) {
            throw ValidationException::withMessages([
                'username' => ['Credenciais inválidas.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken($validated['device_name'] ?? 'flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->serializeUser($user),
        ]);
    }

    public function qrLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('qr_login_token', $validated['qr_token'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'qr_token' => ['QR code inválido.'],
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->serializeUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        return response()->json(['user' => $this->serializeUser($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        if ($user && $token) {
            $user->tokens()->whereKey($token->id)->delete();
        }

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    private function serializeUser(User $user): array
    {
        $user->loadMissing(['unidade:id,titulo', 'turmas:id,nome', 'aluno:id,user_id,nome,turma_id,unidade_id,coins,xp']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'access_role' => $user->access_role,
            'unidade' => $user->unidade ? [
                'id' => $user->unidade->id,
                'titulo' => $user->unidade->titulo,
            ] : null,
            'turmas' => $user->turmas->map(fn ($t) => ['id' => $t->id, 'nome' => $t->nome])->values(),
            'aluno' => $user->aluno ? [
                'id' => $user->aluno->id,
                'nome' => $user->aluno->nome,
                'turma_id' => $user->aluno->turma_id,
                'unidade_id' => $user->aluno->unidade_id,
                'coins' => $user->aluno->coins,
                'xp' => $user->aluno->xp,
            ] : null,
        ];
    }
}

