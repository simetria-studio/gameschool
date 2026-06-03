<?php

use App\Http\Controllers\Api\AppDataController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NotificacaoApiController;
use App\Http\Controllers\Api\PedidoApiController;
use App\Http\Controllers\Api\QuizApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/qr-login', [AuthApiController::class, 'qrLogin']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
    });

    // Endpoints majoritariamente de leitura para consumo do app Flutter
    Route::get('/unidades', [AppDataController::class, 'unidades']);
    Route::get('/turmas', [AppDataController::class, 'turmas']);
    Route::get('/alunos', [AppDataController::class, 'alunos']);
    Route::get('/missoes', [AppDataController::class, 'missoes']);
    Route::get('/quizzes', [QuizApiController::class, 'index']);
    Route::get('/quizzes/{quiz}', [QuizApiController::class, 'show']);
    Route::post('/quizzes/{quiz}/tentativas', [QuizApiController::class, 'storeTentativa']);
    Route::get('/quizzes/{quiz}/tentativas', [QuizApiController::class, 'tentativas']);
    Route::get('/quizzes/{quiz}/tentativas/{tentativa}', [QuizApiController::class, 'showTentativa']);
    Route::get('/atitudes', [AppDataController::class, 'atitudes']);
    Route::get('/loja-itens', [AppDataController::class, 'loja']);
    Route::get('/pedidos', [AppDataController::class, 'pedidos']);
    Route::post('/pedidos', [PedidoApiController::class, 'store']);
    Route::post('/pedidos/{pedido}/aprovar', [PedidoApiController::class, 'approve']);
    Route::get('/ranking', [AppDataController::class, 'ranking']);

    Route::get('/notificacoes', [NotificacaoApiController::class, 'index']);
    Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoApiController::class, 'marcarTodasLidas']);
    Route::post('/notificacoes/{id}/marcar-lida', [NotificacaoApiController::class, 'marcarLida'])
        ->whereUuid('id');
});
