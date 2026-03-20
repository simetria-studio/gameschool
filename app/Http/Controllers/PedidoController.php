<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\LojaItem;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $pedidosQuery = Pedido::with(['aluno', 'produto'])
            ->when(!$isMaster, function ($q) use ($user) {
                $q->whereHas('aluno', fn ($qa) => $qa->where('unidade_id', $user->unidade_id));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('aluno', fn ($qa) => $qa->where('nome', 'like', '%' . $search . '%'))
                        ->orWhereHas('produto', fn ($qp) => $qp->where('titulo', 'like', '%' . $search . '%'))
                        ->orWhere('status', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at');

        $pedidos = $pedidosQuery->paginate($perPage)->withQueryString();
        $total = $pedidos->total();

        $alunosQuery = Aluno::query()->orderBy('nome');
        $produtosQuery = LojaItem::query()
            ->where('status', 'ativo')
            ->where('quantidade', '>', 0)
            ->orderBy('titulo');

        if (!$isMaster) {
            $alunosQuery->where('unidade_id', $user->unidade_id);
            $produtosQuery->where('unidade_id', $user->unidade_id);
        }

        $alunos = $alunosQuery->get(['id', 'nome', 'unidade_id']);
        $produtos = $produtosQuery->get(['id', 'titulo', 'coins', 'unidade_id']);

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'alunos' => $alunos,
            'produtos' => $produtos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $validated = $request->validate([
            'aluno_id' => ['required', 'exists:alunos,id'],
            'loja_item_id' => ['required', 'exists:loja_itens,id'],
            'qnt_atual' => ['required', 'integer', 'min:1'],
            'coins' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:pendente,aprovado,recusado'],
        ], [], [
            'aluno_id' => 'aluno',
            'loja_item_id' => 'produto',
            'qnt_atual' => 'quantidade',
            'coins' => 'coins',
            'status' => 'status',
        ]);

        $aluno = Aluno::findOrFail((int) $validated['aluno_id']);
        $produto = LojaItem::findOrFail((int) $validated['loja_item_id']);
        $this->authorizeByUnidade($isMaster, (int) $user->unidade_id, $aluno, $produto);

        Pedido::create($validated);

        return redirect()
            ->route('pedidos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Pedido adicionado com sucesso.');
    }

    public function update(Request $request, Pedido $pedido): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $validated = $request->validate([
            'aluno_id' => ['required', 'exists:alunos,id'],
            'loja_item_id' => ['required', 'exists:loja_itens,id'],
            'qnt_atual' => ['required', 'integer', 'min:1'],
            'coins' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:pendente,aprovado,recusado'],
        ]);

        $aluno = Aluno::findOrFail((int) $validated['aluno_id']);
        $produto = LojaItem::findOrFail((int) $validated['loja_item_id']);
        $this->authorizeByUnidade($isMaster, (int) $user->unidade_id, $aluno, $produto, $pedido);

        $pedido->update($validated);

        return redirect()
            ->route('pedidos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy(Request $request, Pedido $pedido): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $pedido->loadMissing('aluno');

        if (!$isMaster && (int) ($pedido->aluno->unidade_id ?? 0) !== (int) $user->unidade_id) {
            abort(403);
        }

        $pedido->delete();

        return redirect()
            ->route('pedidos.index', $request->only(['per_page', 'search']))
            ->with('success', 'Pedido apagado com sucesso.');
    }

    private function authorizeByUnidade(bool $isMaster, int $unidadeId, Aluno $aluno, LojaItem $produto, ?Pedido $pedido = null): void
    {
        if ($isMaster) {
            return;
        }

        if ((int) $aluno->unidade_id !== $unidadeId || (int) $produto->unidade_id !== $unidadeId) {
            abort(403);
        }

        if ($pedido !== null) {
            $pedido->loadMissing('aluno');

            if ((int) ($pedido->aluno->unidade_id ?? 0) !== $unidadeId) {
                abort(403);
            }
        }
    }
}
