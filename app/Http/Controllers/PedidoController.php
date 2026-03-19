<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search', '');

        // Placeholder: sem model ainda, tabela vazia com paginação fake
        $pedidos = collect();
        $total = 0;

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
        ]);
    }
}
