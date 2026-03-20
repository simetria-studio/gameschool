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

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index')->middleware('role:master,direcao');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store')->middleware('role:master,direcao');
    Route::put('/pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update')->middleware('role:master,direcao');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy')->middleware('role:master,direcao');

    Route::get('/missoes', [MissaoController::class, 'index'])->name('missoes.index')->middleware('role:master,direcao,professor');
    Route::post('/missoes', [MissaoController::class, 'store'])->name('missoes.store')->middleware('role:master,direcao,professor');
    Route::put('/missoes/{missao}', [MissaoController::class, 'update'])->name('missoes.update')->middleware('role:master,direcao,professor');
    Route::delete('/missoes/{missao}', [MissaoController::class, 'destroy'])->name('missoes.destroy')->middleware('role:master,direcao,professor');
    Route::get('/atitudes', [AtitudeController::class, 'index'])->name('atitudes.index')->middleware('role:master,direcao,professor');
    Route::post('/atitudes', [AtitudeController::class, 'store'])->name('atitudes.store')->middleware('role:master,direcao,professor');
    Route::put('/atitudes/{atitude}', [AtitudeController::class, 'update'])->name('atitudes.update')->middleware('role:master,direcao,professor');
    Route::delete('/atitudes/{atitude}', [AtitudeController::class, 'destroy'])->name('atitudes.destroy')->middleware('role:master,direcao,professor');
    Route::get('/atitudes/{atitude}/recompensar', [AtitudeController::class, 'showRecompensar'])->name('atitudes.recompensar')->middleware('role:master,direcao,professor');
    Route::post('/atitudes/{atitude}/recompensar', [AtitudeController::class, 'recompensar'])->name('atitudes.recompensar.store')->middleware('role:master,direcao,professor');
    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index')->middleware('role:master');
    Route::post('/turmas', [TurmaController::class, 'store'])->name('turmas.store')->middleware('role:master');
    Route::put('/turmas/{turma}', [TurmaController::class, 'update'])->name('turmas.update')->middleware('role:master');
    Route::delete('/turmas/{turma}', [TurmaController::class, 'destroy'])->name('turmas.destroy')->middleware('role:master');
    Route::get('/loja', [LojaController::class, 'index'])->name('loja.index')->middleware('role:master,direcao');
    Route::post('/loja', [LojaController::class, 'store'])->name('loja.store')->middleware('role:master,direcao');
    Route::put('/loja/{loja}', [LojaController::class, 'update'])->name('loja.update')->middleware('role:master,direcao');
    Route::delete('/loja/{loja}', [LojaController::class, 'destroy'])->name('loja.destroy')->middleware('role:master,direcao');
    Route::get('/alunos', [AlunoController::class, 'index'])->name('alunos.index')->middleware('role:master,direcao');
    Route::get('/alunos/crachas/lote', [AlunoController::class, 'crachasLote'])->name('alunos.crachas.lote')->middleware('role:master,direcao');
    Route::post('/alunos', [AlunoController::class, 'store'])->name('alunos.store')->middleware('role:master,direcao');
    Route::put('/alunos/{aluno}', [AlunoController::class, 'update'])->name('alunos.update')->middleware('role:master,direcao');
    Route::delete('/alunos/{aluno}', [AlunoController::class, 'destroy'])->name('alunos.destroy')->middleware('role:master,direcao');
    Route::get('/alunos/{aluno}/cracha', [AlunoController::class, 'cracha'])->name('alunos.cracha')->middleware('role:master,direcao');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index')->middleware('role:master');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store')->middleware('role:master');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update')->middleware('role:master');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy')->middleware('role:master');

    Route::get('/unidades', [UnidadeController::class, 'index'])->name('unidades.index')->middleware('role:master');
    Route::post('/unidades', [UnidadeController::class, 'store'])->name('unidades.store')->middleware('role:master');
    Route::put('/unidades/{unidade}', [UnidadeController::class, 'update'])->name('unidades.update')->middleware('role:master');
    Route::delete('/unidades/{unidade}', [UnidadeController::class, 'destroy'])->name('unidades.destroy')->middleware('role:master');

    Route::get('/conta', [ContaController::class, 'index'])->name('conta.index');
    Route::put('/conta', [ContaController::class, 'update'])->name('conta.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
