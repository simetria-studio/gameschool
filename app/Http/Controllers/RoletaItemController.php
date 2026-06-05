<?php

namespace App\Http\Controllers;

use App\Models\RoletaItem;
use App\Models\Unidade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoletaItemController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;
        $search = trim((string) $request->get('search', ''));

        $query = RoletaItem::with('unidade')
            ->where('tipo', 'emote')
            ->when(! $isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', fn ($q) => $q->where('titulo', 'like', '%' . $search . '%'))
            ->orderBy('titulo');

        $itens = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->get();

        return view('roleta-itens.index', [
            'itens' => $itens,
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
            'canManageAllUnits' => $isMaster,
            'emojisSugeridos' => self::emojisSugeridos(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateEmote($request);
        $validated['tipo'] = 'emote';
        $validated['imagem'] = null;

        RoletaItem::create($validated);

        return redirect()->route('roleta-itens.index', $request->only(['per_page', 'search']))
            ->with('success', 'Emote adicionado com sucesso.');
    }

    public function update(Request $request, RoletaItem $roletaItem): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if ($roletaItem->tipo !== 'emote') {
            abort(404);
        }

        if (! $isMaster && (int) $roletaItem->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validateEmote($request);
        $validated['tipo'] = 'emote';
        $validated['imagem'] = null;

        $roletaItem->update($validated);

        return redirect()->route('roleta-itens.index', $request->only(['per_page', 'search']))
            ->with('success', 'Emote atualizado com sucesso.');
    }

    public function destroy(Request $request, RoletaItem $roletaItem): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if ($roletaItem->tipo !== 'emote') {
            abort(404);
        }

        if (! $isMaster && (int) $roletaItem->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        $roletaItem->delete();

        return redirect()->route('roleta-itens.index', $request->only(['per_page', 'search']))
            ->with('success', 'Emote excluído com sucesso.');
    }

    /**
     * @return array<int, array{emoji: string, nome: string}>
     */
    public static function emojisSugeridos(): array
    {
        return [
            ['emoji' => '💙', 'nome' => 'Coração azul'],
            ['emoji' => '👏', 'nome' => 'Palmas'],
            ['emoji' => '👏🏼', 'nome' => 'Palmas (tom claro)'],
            ['emoji' => '🚀', 'nome' => 'Foguete'],
            ['emoji' => '⚽', 'nome' => 'Bola de futebol'],
            ['emoji' => '😍', 'nome' => 'Apaixonado'],
            ['emoji' => '😳', 'nome' => 'Envergonhado'],
            ['emoji' => '🔥', 'nome' => 'Fogo'],
            ['emoji' => '🎂', 'nome' => 'Bolo'],
            ['emoji' => '💩', 'nome' => 'Cocô'],
            ['emoji' => '⭐', 'nome' => 'Estrela'],
            ['emoji' => '🏆', 'nome' => 'Troféu'],
            ['emoji' => '💎', 'nome' => 'Diamante'],
            ['emoji' => '🎮', 'nome' => 'Videogame'],
            ['emoji' => '🦄', 'nome' => 'Unicórnio'],
            ['emoji' => '🐉', 'nome' => 'Dragão'],
            ['emoji' => '🦸', 'nome' => 'Herói'],
            ['emoji' => '👑', 'nome' => 'Coroa'],
            ['emoji' => '❤️', 'nome' => 'Coração'],
            ['emoji' => '💪', 'nome' => 'Força'],
            ['emoji' => '🎯', 'nome' => 'Alvo'],
            ['emoji' => '🎁', 'nome' => 'Presente'],
            ['emoji' => '✨', 'nome' => 'Brilho'],
            ['emoji' => '😎', 'nome' => 'Estiloso'],
        ];
    }

    private function validateEmote(Request $request): array
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'emoji' => ['required', 'string', 'max:16'],
            'raridade' => ['required', 'in:comum,raro,epico,lendario'],
            'status' => ['required', 'in:ativo,inativo'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'emoji' => 'emoji',
            'raridade' => 'raridade',
        ]);

        $validated['emoji'] = trim($validated['emoji']);

        if ($validated['emoji'] === '') {
            throw ValidationException::withMessages([
                'emoji' => 'Selecione ou digite um emoji.',
            ]);
        }

        return $validated;
    }
}
