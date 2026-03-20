<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --gs-primary: #F2B233;
            --gs-primary-hover: #D99A22;
            --gs-system: #1A1A1A;
            --gs-bg-panel: #F5F6F8;
            --gs-text: #2C2C2C;
            --gs-text-secondary: #6B6B6B;
        }
        body {
            background-color: var(--gs-bg-panel);
            color: var(--gs-text);
            min-height: 100vh;
        }
        .gs-sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #F8F9FA 100%);
            border-right: 1px solid rgba(44, 44, 44, 0.08);
            width: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .gs-sidebar .gs-logo-wrap {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(44, 44, 44, 0.06);
            flex-shrink: 0;
        }
        .gs-sidebar .gs-logo-wrap a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .gs-sidebar .gs-logo-wrap img {
            max-height: 36px;
            max-width: 140px;
            object-fit: contain;
        }
        .gs-sidebar .gs-nav-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem 0.5rem;
        }
        .gs-sidebar .nav-link {
            color: var(--gs-text);
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: 0.02em;
            padding: 0.65rem 1rem;
            margin-bottom: 2px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            transition: background-color 0.15s ease, color 0.15s ease, transform 0.1s ease;
        }
        .gs-sidebar .nav-link i {
            color: var(--gs-text-secondary);
            font-size: 1.15rem;
            width: 1.5rem;
            height: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            flex-shrink: 0;
            transition: color 0.15s ease, background-color 0.15s ease;
        }
        .gs-sidebar .nav-link:hover {
            background-color: rgba(44, 44, 44, 0.05);
            color: var(--gs-system);
        }
        .gs-sidebar .nav-link:hover i {
            color: var(--gs-system);
        }
        .gs-sidebar .nav-link.active {
            background-color: var(--gs-primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(242, 178, 51, 0.25);
        }
        .gs-sidebar .nav-link.active i {
            color: #fff;
            background-color: rgba(0, 0, 0, 0.1);
        }
        .gs-sidebar .gs-nav-divider {
            height: 1px;
            background: rgba(44, 44, 44, 0.08);
            margin: 0.5rem 1rem;
        }
        .gs-sidebar .gs-nav-footer {
            padding: 0.75rem 0.5rem 1rem;
            border-top: 1px solid rgba(44, 44, 44, 0.06);
            flex-shrink: 0;
        }
        .gs-sidebar .gs-nav-logout {
            color: var(--gs-text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: 0.02em;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .gs-sidebar .gs-nav-logout i {
            color: var(--gs-text-secondary);
            font-size: 1.15rem;
            width: 1.5rem;
            height: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }
        .gs-sidebar .gs-nav-logout:hover {
            background-color: rgba(235, 80, 80, 0.08);
            color: #c0392b;
        }
        .gs-sidebar .gs-nav-logout:hover i {
            color: #c0392b;
        }
        .gs-topbar {
            background-color: var(--gs-system);
            color: #fff;
            padding: 0.6rem 1.5rem;
        }
        .gs-topbar .gs-text-muted {
            color: rgba(255,255,255,0.7);
            font-size: 0.875rem;
        }
        .gs-text-secondary {
            color: var(--gs-text-secondary) !important;
        }
        .btn-gs-primary {
            background-color: var(--gs-primary);
            border-color: var(--gs-primary);
            color: var(--gs-system);
        }
        .btn-gs-primary:hover {
            background-color: var(--gs-primary-hover);
            border-color: var(--gs-primary-hover);
            color: var(--gs-system);
        }
        .gs-card {
            background: #fff;
            border-radius: 0.5rem;
            border: 1px solid rgba(44, 44, 44, 0.08);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .gs-table th {
            color: var(--gs-text-secondary);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 1px solid rgba(44, 44, 44, 0.1);
        }
        .gs-table td {
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex min-vh-100">
        <aside class="gs-sidebar flex-shrink-0">
            <div class="gs-logo-wrap">
                <a href="{{ route('pedidos.index') }}">
                    <img src="{{ asset('imgs/logo.png') }}" alt="Game School">
                </a>
            </div>
            <div class="gs-nav-scroll">
                <nav class="nav flex-column">
                    @php
                        $role = auth()->user()->access_role ?? 'professor';
                        $isMaster = $role === 'master';
                        $isDirecao = $role === 'direcao';
                        $isProfessor = $role === 'professor';
                    @endphp

                    @if ($isMaster || $isDirecao)
                        <a class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}" href="{{ route('pedidos.index') }}">
                            <i class="bi bi-card-list"></i>
                            <span>PEDIDOS</span>
                        </a>
                    @endif

                    @if ($isMaster || $isDirecao || $isProfessor)
                        <a class="nav-link {{ request()->routeIs('missoes.*') ? 'active' : '' }}" href="{{ route('missoes.index') }}">
                            <i class="bi bi-flag"></i>
                            <span>MISSÕES</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('atitudes.*') ? 'active' : '' }}" href="{{ route('atitudes.index') }}">
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>ATITUDES</span>
                        </a>
                    @endif

                    @if ($isMaster)
                        <a class="nav-link {{ request()->routeIs('turmas.*') ? 'active' : '' }}" href="{{ route('turmas.index') }}">
                            <i class="bi bi-mortarboard"></i>
                            <span>TURMAS</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                            <i class="bi bi-person"></i>
                            <span>USUÁRIOS</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('unidades.*') ? 'active' : '' }}" href="{{ route('unidades.index') }}">
                            <i class="bi bi-building"></i>
                            <span>UNIDADES</span>
                        </a>
                    @endif

                    @if ($isMaster || $isDirecao)
                        <a class="nav-link {{ request()->routeIs('loja.*') ? 'active' : '' }}" href="{{ route('loja.index') }}">
                            <i class="bi bi-bag"></i>
                            <span>LOJA</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('alunos.*') ? 'active' : '' }}" href="{{ route('alunos.index') }}">
                            <i class="bi bi-people"></i>
                            <span>ALUNOS</span>
                        </a>
                    @endif

                    <a class="nav-link {{ request()->routeIs('conta.*') ? 'active' : '' }}" href="{{ route('conta.index') }}">
                        <i class="bi bi-person-circle"></i>
                        <span>CONTA</span>
                    </a>
                </nav>
            </div>
            <div class="gs-nav-divider"></div>
            <div class="gs-nav-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="gs-nav-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>SAIR</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="flex-grow-1 d-flex flex-column min-vw-0">
            <header class="gs-topbar d-flex align-items-center justify-content-between">
                <span class="gs-text-muted">@yield('breadcrumb', 'Dashboard')</span>
                <span class="gs-text-muted small">{{ auth()->user()->name ?? auth()->user()->username }}</span>
            </header>
            <main class="p-4 flex-grow-1">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
