<?php

namespace App\Http\Controllers;

use App\Models\Missao;
use App\Models\Unidade;
use App\Models\Turma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MissaoController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search', ''));

        $query = Missao::with(['unidade', 'turma'])
            ->when(!$isMaster, fn ($q) => $q->where('unidade_id', $user->unidade_id))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('titulo', 'like', '%' . $search . '%')
                    ->orWhere('descricao', 'like', '%' . $search . '%')
                    ->orWhereHas('unidade', fn ($u) => $u->where('titulo', 'like', '%' . $search . '%'))
                    ->orWhereHas('turma', fn ($t) => $t->where('nome', 'like', '%' . $search . '%'));
            })
            ->orderBy('created_at', 'desc');

        $missoes = $query->paginate($perPage)->withQueryString();
        $unidades = $isMaster
            ? Unidade::orderBy('titulo')->get()
            : Unidade::where('id', $user->unidade_id)->orderBy('titulo')->get();
        $turmas = Turma::orderBy('nome')->get();

        return view('missoes.index', [
            'missoes' => $missoes,
            'unidades' => $unidades,
            'turmas' => $turmas,
            'perPage' => $perPage,
            'search' => $search,
            'canManageAllUnits' => $isMaster,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_id' => ['required', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'xp' => ['required', 'integer'],
            'coins' => ['required', 'integer'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'turma_id' => 'turma',
            'descricao' => 'descrição',
            'data_encerramento' => 'data de encerramento',
        ]);

        Missao::create($validated);

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão adicionada com sucesso.');
    }

    public function update(Request $request, Missao $missao): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $missao->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }

            $request->merge([
                'unidade_id' => $user->unidade_id,
            ]);
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'unidade_id' => ['required', 'exists:unidades,id'],
            'turma_id' => ['required', 'exists:turmas,id'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'xp' => ['required', 'integer'],
            'coins' => ['required', 'integer'],
            'status' => ['required', 'in:ativa,inativa'],
            'data_encerramento' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'unidade_id' => 'unidade',
            'turma_id' => 'turma',
            'descricao' => 'descrição',
            'data_encerramento' => 'data de encerramento',
        ]);

        $missao->update($validated);

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão atualizada com sucesso.');
    }

    public function destroy(Request $request, Missao $missao): RedirectResponse
    {
        $user = Auth::user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        if (!$isMaster) {
            if ((int) $missao->unidade_id !== (int) $user->unidade_id) {
                abort(403);
            }
        }

        $missao->delete();

        return redirect()
            ->route('missoes.index', $request->only(['per_page', 'search']))
            ->with('success', 'Missão excluída com sucesso.');
    }
}
