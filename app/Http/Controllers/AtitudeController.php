<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Atitude;
use App\Models\Turma;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtitudeController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = Atitude::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('titulo', 'like', '%' . $search . '%')
                    ->orWhere('descricao', 'like', '%' . $search . '%')
                    ->orWhere('tipo', 'like', '%' . $search . '%');
            })
            ->orderBy('titulo');

        $atitudes = $query->paginate($perPage)->withQueryString();

        return view('atitudes.index', [
            'atitudes' => $atitudes,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:positiva,negativa'],
            'coins' => ['required', 'integer'],
            'xp' => ['required', 'integer'],
        ], [], [
            'titulo' => 'título',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
        ]);

        Atitude::create($validated);

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude adicionada com sucesso.');
    }

    public function update(Request $request, Atitude $atitude): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:positiva,negativa'],
            'coins' => ['required', 'integer'],
            'xp' => ['required', 'integer'],
        ], [], [
            'titulo' => 'título',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
        ]);

        $atitude->update($validated);

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude atualizada com sucesso.');
    }

    public function destroy(Request $request, Atitude $atitude): RedirectResponse
    {
        $atitude->delete();

        return redirect()
            ->route('atitudes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Atitude excluída com sucesso.');
    }

    public function showRecompensar(Atitude $atitude): View
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        $turmas = Turma::orderBy('nome')->get();

        $alunosQuery = Aluno::with(['unidade', 'turma'])->orderBy('nome');
        if (!$isMaster) {
            $alunosQuery->where('unidade_id', $user->unidade_id);
        }

        $alunos = $alunosQuery->get();

        return view('atitudes.recompensar', [
            'atitude' => $atitude,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'alunos' => $alunos,
        ]);
    }

    public function recompensar(Request $request, Atitude $atitude): RedirectResponse
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $validated = $request->validate([
            'aluno_id' => ['required', 'exists:alunos,id'],
        ], [], ['aluno_id' => 'aluno']);

        $aluno = Aluno::findOrFail($validated['aluno_id']);

        if (!$isMaster) {
            if ((int) $aluno->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $aluno->increment('coins', $atitude->coins);
        $aluno->increment('xp', $atitude->xp);

        return redirect()
            ->route('atitudes.index')
            ->with('success', "Atitude \"{$atitude->titulo}\" aplicada ao aluno {$aluno->nome}. Coins: {$atitude->coins}, XP: {$atitude->xp}.");
    }
}
