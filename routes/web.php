<?php

use App\Http\Controllers\AtitudeController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\MissaoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\LojaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/login/qr/{token}', [AuthController::class, 'loginWithToken'])->name('login.qr');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');

    Route::get('/missoes', [MissaoController::class, 'index'])->name('missoes.index');
    Route::post('/missoes', [MissaoController::class, 'store'])->name('missoes.store');
    Route::put('/missoes/{missao}', [MissaoController::class, 'update'])->name('missoes.update');
    Route::delete('/missoes/{missao}', [MissaoController::class, 'destroy'])->name('missoes.destroy');
    Route::get('/atitudes', [AtitudeController::class, 'index'])->name('atitudes.index');
    Route::post('/atitudes', [AtitudeController::class, 'store'])->name('atitudes.store');
    Route::put('/atitudes/{atitude}', [AtitudeController::class, 'update'])->name('atitudes.update');
    Route::delete('/atitudes/{atitude}', [AtitudeController::class, 'destroy'])->name('atitudes.destroy');
    Route::get('/atitudes/{atitude}/recompensar', [AtitudeController::class, 'showRecompensar'])->name('atitudes.recompensar');
    Route::post('/atitudes/{atitude}/recompensar', [AtitudeController::class, 'recompensar'])->name('atitudes.recompensar.store');
    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index');
    Route::post('/turmas', [TurmaController::class, 'store'])->name('turmas.store');
    Route::put('/turmas/{turma}', [TurmaController::class, 'update'])->name('turmas.update');
    Route::delete('/turmas/{turma}', [TurmaController::class, 'destroy'])->name('turmas.destroy');
    Route::get('/loja', [LojaController::class, 'index'])->name('loja.index');
    Route::post('/loja', [LojaController::class, 'store'])->name('loja.store');
    Route::put('/loja/{loja}', [LojaController::class, 'update'])->name('loja.update');
    Route::delete('/loja/{loja}', [LojaController::class, 'destroy'])->name('loja.destroy');
    Route::get('/alunos', [AlunoController::class, 'index'])->name('alunos.index');
    Route::get('/alunos/crachas/lote', [AlunoController::class, 'crachasLote'])->name('alunos.crachas.lote');
    Route::post('/alunos', [AlunoController::class, 'store'])->name('alunos.store');
    Route::put('/alunos/{aluno}', [AlunoController::class, 'update'])->name('alunos.update');
    Route::delete('/alunos/{aluno}', [AlunoController::class, 'destroy'])->name('alunos.destroy');
    Route::get('/alunos/{aluno}/cracha', [AlunoController::class, 'cracha'])->name('alunos.cracha');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    Route::get('/unidades', [UnidadeController::class, 'index'])->name('unidades.index');
    Route::post('/unidades', [UnidadeController::class, 'store'])->name('unidades.store');
    Route::put('/unidades/{unidade}', [UnidadeController::class, 'update'])->name('unidades.update');
    Route::delete('/unidades/{unidade}', [UnidadeController::class, 'destroy'])->name('unidades.destroy');

    Route::get('/conta', [ContaController::class, 'index'])->name('conta.index');
    Route::put('/conta', [ContaController::class, 'update'])->name('conta.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
