<?php

namespace App\Http\Controllers;

use App\Models\LojaItem;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LojaController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = LojaItem::with('unidade')
            ->when(!$isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('titulo', 'like', '%' . $search . '%')
                        ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy('created_at', 'desc');

        $itens = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();

        return view('loja.index', [
            'itens' => $itens,
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $request->validate([
            'unidade_id' => ['required', 'exists:unidades,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'coins' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:ativo,inativo'],
        ], [], [
            'unidade_id' => 'unidade',
            'titulo' => 'título do produto',
            'quantidade' => 'quantidade',
            'coins' => 'coins',
            'status' => 'status',
        ]);

        LojaItem::create($validated);

        return redirect()
            ->route('loja.index', ['per_page' => $request->get('per_page'), 'search' => $request->get('search')])
            ->with('success', 'Item adicionado com sucesso.');
    }

    public function update(Request $request, LojaItem $loja): RedirectResponse
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $loja->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }

            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $request->validate([
            'unidade_id' => ['required', 'exists:unidades,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'coins' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:ativo,inativo'],
        ], [], [
            'unidade_id' => 'unidade',
            'titulo' => 'título do produto',
            'quantidade' => 'quantidade',
            'coins' => 'coins',
            'status' => 'status',
        ]);

        $loja->update($validated);

        return redirect()
            ->route('loja.index', ['per_page' => $request->get('per_page'), 'search' => $request->get('search')])
            ->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Request $request, LojaItem $loja): RedirectResponse
    {
        $user = auth()->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $loja->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $loja->delete();

        return redirect()
            ->route('loja.index', ['per_page' => $request->get('per_page'), 'search' => $request->get('search')])
            ->with('success', 'Item excluído com sucesso.');
    }
}
