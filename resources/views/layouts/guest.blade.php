<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            background: var(--gs-system);
            background: linear-gradient(160deg, #1A1A1A 0%, #252525 50%, #1A1A1A 100%);
            color: var(--gs-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .btn-gs-primary {
            background-color: var(--gs-primary);
            border-color: var(--gs-primary);
            color: var(--gs-system);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn-gs-primary:hover {
            background-color: var(--gs-primary-hover);
            border-color: var(--gs-primary-hover);
            color: var(--gs-system);
            transform: translateY(-1px);
        }
        .gs-login-card {
            background-color: var(--gs-bg-panel);
            border: 1px solid rgba(44, 44, 44, 0.08);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255,255,255,0.03);
        }
        .gs-login-card .form-control {
            border-radius: 0.5rem;
            border-color: rgba(44, 44, 44, 0.2);
            padding: 0.65rem 1rem;
        }
        .gs-login-card .form-control:focus {
            border-color: var(--gs-primary);
            box-shadow: 0 0 0 3px rgba(242, 178, 51, 0.2);
        }
        .gs-login-card .form-check-input:checked {
            background-color: var(--gs-primary);
            border-color: var(--gs-primary);
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
