<?php

namespace App\Http\Controllers;

use App\Models\RoletaItem;
use App\Models\Unidade;
use App\Support\RoletaImagemStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoletaColecionavelController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48], true) ? $perPage : 12;
        $search = trim((string) $request->get('search', ''));
        $tipo = trim((string) $request->get('tipo', ''));

        $query = RoletaItem::with('unidade')
            ->whereIn('tipo', ['personagem', 'figurinha'])
            ->when(! $isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', fn ($q) => $q->where('titulo', 'like', '%' . $search . '%'))
            ->when($tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->orderBy('tipo')
            ->orderBy('titulo');

        $itens = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->get();

        return view('roleta-colecionaveis.index', [
            'itens' => $itens,
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
            'tipo' => $tipo,
            'canManageAllUnits' => $isMaster,
            'tamanhoMaxUpload' => RoletaImagemStorage::tamanhoMaximoRotulo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateColecionavel($request, true);
        $upload = RoletaImagemStorage::uploadColecionavel($request->file('arquivo'), $validated['tipo']);
        $validated['imagem'] = $upload['imagem'];
        $validated['imagem_bloqueada'] = $upload['imagem_bloqueada'];
        $validated['emoji'] = null;
        unset($validated['arquivo']);

        RoletaItem::create($validated);

        return redirect()
            ->route('roleta-colecionaveis.index', $request->only(['per_page', 'search', 'tipo']))
            ->with('success', 'Cadastro salvo com sucesso.');
    }

    public function update(Request $request, RoletaItem $roletaItem): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $this->assertColecionavel($roletaItem);

        if (! $isMaster && (int) $roletaItem->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateColecionavel($request, false);

        if ($request->hasFile('arquivo')) {
            $upload = RoletaImagemStorage::uploadColecionavel(
                $request->file('arquivo'),
                $validated['tipo'],
                $roletaItem->imagem,
                $roletaItem->imagem_bloqueada
            );
            $validated['imagem'] = $upload['imagem'];
            $validated['imagem_bloqueada'] = $upload['imagem_bloqueada'];
        } elseif ($validated['tipo'] === 'personagem' && $roletaItem->imagem_bloqueada) {
            RoletaImagemStorage::delete($roletaItem->imagem_bloqueada);
            $validated['imagem_bloqueada'] = null;
        } elseif ($validated['tipo'] === 'figurinha' && ! $roletaItem->imagem_bloqueada && $roletaItem->imagem) {
            $validated['imagem_bloqueada'] = RoletaImagemStorage::gerarSilhueta($roletaItem->imagem);
        }

        unset($validated['arquivo']);
        $roletaItem->update($validated);

        return redirect()
            ->route('roleta-colecionaveis.index', $request->only(['per_page', 'search', 'tipo']))
            ->with('success', 'Cadastro atualizado com sucesso.');
    }

    public function destroy(Request $request, RoletaItem $roletaItem): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $this->assertColecionavel($roletaItem);

        if (! $isMaster && (int) $roletaItem->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        RoletaImagemStorage::delete($roletaItem->imagem);
        RoletaImagemStorage::delete($roletaItem->imagem_bloqueada);
        $roletaItem->delete();

        return redirect()
            ->route('roleta-colecionaveis.index', $request->only(['per_page', 'search', 'tipo']))
            ->with('success', 'Cadastro excluído com sucesso.');
    }

    private function assertColecionavel(RoletaItem $item): void
    {
        if (! in_array($item->tipo, ['personagem', 'figurinha'], true)) {
            abort(404);
        }
    }

    private function validateColecionavel(Request $request, bool $criando): array
    {
        $rules = [
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'tipo' => ['required', 'in:personagem,figurinha'],
            'raridade' => ['required', 'in:comum,raro,epico,lendario'],
            'status' => ['required', 'in:ativo,inativo'],
        ];

        if ($criando) {
            $rules['arquivo'] = ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:' . RoletaImagemStorage::TAMANHO_MAXIMO_KB];
        } else {
            $rules['arquivo'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:' . RoletaImagemStorage::TAMANHO_MAXIMO_KB];
        }

        $validated = $request->validate($rules, [
            'arquivo.max' => 'A imagem deve ter no máximo ' . RoletaImagemStorage::tamanhoMaximoRotulo() . '.',
            'arquivo.image' => 'Envie um arquivo de imagem válido.',
        ], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'tipo' => 'tipo',
            'arquivo' => 'imagem',
            'raridade' => 'raridade',
        ]);

        if ($criando && ! $request->hasFile('arquivo')) {
            throw ValidationException::withMessages([
                'arquivo' => 'Selecione uma imagem para upload.',
            ]);
        }

        return $validated;
    }
}
