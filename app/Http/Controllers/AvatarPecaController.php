<?php

namespace App\Http\Controllers;

use App\Models\AvatarPeca;
use App\Models\Unidade;
use App\Support\AvatarImagemStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvatarPecaController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48], true) ? $perPage : 12;
        $search = trim((string) $request->get('search', ''));
        $slot = trim((string) $request->get('slot', ''));
        $genero = trim((string) $request->get('genero', ''));
        $status = trim((string) $request->get('status', ''));

        $query = AvatarPeca::with('unidade')
            ->when(! $isMaster, function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->whereNull('unidade_id')
                        ->orWhere('unidade_id', $user->unidade_id);
                });
            })
            ->when($search !== '', fn ($q) => $q->where('titulo', 'like', '%' . $search . '%'))
            ->when($slot !== '', fn ($q) => $q->where('slot', $slot))
            ->when($genero !== '', fn ($q) => $q->where('genero', $genero))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('slot')
            ->orderBy('titulo');

        $itens = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->get();

        return view('avatar-pecas.index', [
            'itens' => $itens,
            'unidades' => $unidades,
            'perPage' => $perPage,
            'search' => $search,
            'slot' => $slot,
            'genero' => $genero,
            'status' => $status,
            'canManageAllUnits' => $isMaster,
            'tamanhoMaxUpload' => AvatarImagemStorage::tamanhoMaximoRotulo(),
            'slots' => AvatarPeca::SLOTS,
            'slotLabels' => AvatarPeca::SLOT_LABELS,
            'generos' => AvatarPeca::GENEROS,
            'raridades' => AvatarPeca::RARIDADES,
            'tiposAsset' => AvatarPeca::TIPOS_ASSET,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id]);
        }

        $validated = $this->validatePeca($request, true);

        if ($request->hasFile('arquivo')) {
            $validated['asset_url'] = AvatarImagemStorage::uploadAsset($request->file('arquivo'));
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_url'] = AvatarImagemStorage::uploadThumbnail($request->file('thumbnail'));
        } else {
            $validated['thumbnail_url'] = $validated['asset_url'] ?? null;
        }

        $validated['is_starter'] = $request->boolean('is_starter');
        $validated['meta_json'] = [
            'z_index' => $this->zIndexForSlot($validated['slot']),
        ];

        unset($validated['arquivo'], $validated['thumbnail']);

        AvatarPeca::create($validated);

        return redirect()
            ->route('avatar-pecas.index', $request->only(['per_page', 'search', 'slot', 'genero', 'status']))
            ->with('success', 'Peça de avatar salva com sucesso.');
    }

    public function update(Request $request, AvatarPeca $avatarPeca): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (! $isMaster && $avatarPeca->unidade_id && (int) $avatarPeca->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if (! $isMaster) {
            $request->merge(['unidade_id' => $user->unidade_id ?: $avatarPeca->unidade_id]);
        }

        $validated = $this->validatePeca($request, false);

        if ($request->hasFile('arquivo')) {
            $validated['asset_url'] = AvatarImagemStorage::uploadAsset(
                $request->file('arquivo'),
                $avatarPeca->asset_url
            );
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_url'] = AvatarImagemStorage::uploadThumbnail(
                $request->file('thumbnail'),
                $avatarPeca->thumbnail_url
            );
        }

        $validated['is_starter'] = $request->boolean('is_starter');
        $meta = $avatarPeca->meta_json ?? [];
        $meta['z_index'] = $this->zIndexForSlot($validated['slot']);
        $validated['meta_json'] = $meta;

        unset($validated['arquivo'], $validated['thumbnail']);
        $avatarPeca->update($validated);

        return redirect()
            ->route('avatar-pecas.index', $request->only(['per_page', 'search', 'slot', 'genero', 'status']))
            ->with('success', 'Peça de avatar atualizada.');
    }

    public function destroy(Request $request, AvatarPeca $avatarPeca): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (! $isMaster && $avatarPeca->unidade_id && (int) $avatarPeca->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        AvatarImagemStorage::delete($avatarPeca->asset_url);
        if ($avatarPeca->thumbnail_url !== $avatarPeca->asset_url) {
            AvatarImagemStorage::delete($avatarPeca->thumbnail_url);
        }
        $avatarPeca->delete();

        return redirect()
            ->route('avatar-pecas.index', $request->only(['per_page', 'search', 'slot', 'genero', 'status']))
            ->with('success', 'Peça excluída.');
    }

    private function validatePeca(Request $request, bool $criando): array
    {
        $rules = [
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['nullable', 'exists:unidades,id'],
            'slot' => ['required', Rule::in(AvatarPeca::SLOTS)],
            'genero' => ['required', Rule::in(AvatarPeca::GENEROS)],
            'tipo_asset' => ['required', Rule::in(AvatarPeca::TIPOS_ASSET)],
            'raridade' => ['required', Rule::in(AvatarPeca::RARIDADES)],
            'status' => ['required', 'in:ativo,inativo'],
            'is_starter' => ['sometimes', 'boolean'],
            'thumbnail' => ['nullable', 'file', 'max:' . AvatarImagemStorage::TAMANHO_MAXIMO_KB],
        ];

        if ($criando) {
            $rules['arquivo'] = ['required', 'file', 'max:' . AvatarImagemStorage::TAMANHO_MAXIMO_KB];
        } else {
            $rules['arquivo'] = ['nullable', 'file', 'max:' . AvatarImagemStorage::TAMANHO_MAXIMO_KB];
        }

        return $request->validate($rules);
    }

    private function zIndexForSlot(string $slot): int
    {
        return AvatarPeca::zIndexForSlot($slot);
    }
}
