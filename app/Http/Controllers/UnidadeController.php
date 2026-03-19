<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnidadeController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = Unidade::query()->orderBy('titulo');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                    ->orWhere('endereco', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%");
            });
        }

        $unidades = $query->paginate($perPage)->withQueryString();

        return view('unidades.index', [
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ], [
            'titulo.required' => 'O título é obrigatório.',
        ]);

        Unidade::create($validated);

        return redirect()
            ->route('unidades.index', $request->only(['per_page', 'search']))
            ->with('success', 'Unidade adicionada com sucesso.');
    }

    public function update(Request $request, Unidade $unidade): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ], [
            'titulo.required' => 'O título é obrigatório.',
        ]);

        $unidade->update($validated);

        return redirect()
            ->route('unidades.index', $request->only(['per_page', 'search']))
            ->with('success', 'Unidade atualizada com sucesso.');
    }

    public function destroy(Request $request, Unidade $unidade): RedirectResponse
    {
        $unidade->delete();

        return redirect()
            ->route('unidades.index', $request->only(['per_page', 'search']))
            ->with('success', 'Unidade excluída com sucesso.');
    }
}
