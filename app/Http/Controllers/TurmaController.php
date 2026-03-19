<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TurmaController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = Turma::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('nome', 'like', '%' . $search . '%')
                    ->orWhere('periodo', 'like', '%' . $search . '%');
            })
            ->orderBy('nome');

        $turmas = $query->paginate($perPage)->withQueryString();

        return view('turmas.index', [
            'turmas' => $turmas,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['required', 'in:0,1'],
            'periodo' => ['required', 'in:manha,tarde,noite'],
        ], [], [
            'nome' => 'nome da turma',
            'ativo' => 'ativo',
            'periodo' => 'período',
        ]);

        $validated['ativo'] = (bool) $validated['ativo'];

        Turma::create($validated);

        return redirect()
            ->route('turmas.index', $request->only(['per_page', 'search']))
            ->with('success', 'Turma adicionada com sucesso.');
    }

    public function update(Request $request, Turma $turma): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['required', 'in:0,1'],
            'periodo' => ['required', 'in:manha,tarde,noite'],
        ], [], [
            'nome' => 'nome da turma',
            'ativo' => 'ativo',
            'periodo' => 'período',
        ]);

        $validated['ativo'] = (bool) $validated['ativo'];
        $turma->update($validated);

        return redirect()
            ->route('turmas.index', $request->only(['per_page', 'search']))
            ->with('success', 'Turma atualizada com sucesso.');
    }

    public function destroy(Request $request, Turma $turma): RedirectResponse
    {
        $turma->delete();

        return redirect()
            ->route('turmas.index', $request->only(['per_page', 'search']))
            ->with('success', 'Turma excluída com sucesso.');
    }
}
