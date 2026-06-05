<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\AlunoItem;
use App\Models\RoletaItem;
use App\Support\InventarioAluno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $alunos = Aluno::query()
            ->when(! $isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->orderBy('nome')
            ->get(['id', 'nome', 'coins', 'xp']);

        $alunoId = (int) $request->integer('aluno_id');
        $tipo = trim((string) $request->get('tipo', ''));

        $aluno = null;
        $resumo = null;
        $categorias = [];

        if ($alunoId > 0) {
            $aluno = Aluno::findOrFail($alunoId);

            if (! $isMaster && (int) $aluno->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }

            $registros = AlunoItem::query()
                ->with('item')
                ->where('aluno_id', $aluno->id)
                ->where('quantidade', '>', 0)
                ->when($tipo !== '', fn ($q) => $q->whereHas('item', fn ($i) => $i->where('tipo', $tipo)))
                ->orderByDesc('updated_at')
                ->get();

            $resumo = InventarioAluno::montarResumo($registros);
            $categorias = InventarioAluno::montarCategorias($registros);
        }

        return view('inventarios.index', [
            'alunos' => $alunos,
            'aluno' => $aluno,
            'alunoId' => $alunoId,
            'tipo' => $tipo,
            'resumo' => $resumo,
            'categorias' => $categorias,
            'tipos' => RoletaItem::TIPOS,
        ]);
    }
}
