<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Financeiro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --page-bg: #f4f7fb;
            --surface: #ffffff;
            --border: #dbe3ef;
            --text: #172033;
            --muted: #64748b;
            --brand: #1f7a6b;
            --brand-dark: #14594f;
            --danger: #c2414b;
            --success: #237a57;
            --warning: #a16207;
        }

        body {
            background: var(--page-bg);
            color: var(--text);
            font-size: 0.95rem;
        }

        .app-shell {
            min-height: 100vh;
        }

        .app-layout {
            display: flex;
            min-height: calc(100vh - 73px);
        }

        .sidebar {
            width: 248px;
            flex: 0 0 248px;
            padding: 0 1rem 1.5rem;
        }

        .sidebar-panel {
            position: sticky;
            top: 1rem;
            background: #152033;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.75rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        }

        .sidebar-title {
            color: #cbd5e1;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.55rem 0.75rem;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: #e2e8f0;
            border-radius: 7px;
            font-weight: 650;
            padding: 0.72rem 0.75rem;
            text-decoration: none;
        }

        .sidebar-link:hover,
        .sidebar-link:focus {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .sidebar-link small {
            color: #94a3b8;
            font-weight: 700;
        }

        .content-area {
            flex: 1 1 auto;
            min-width: 0;
        }

        .navbar {
            background: #152033;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0;
        }

        .page-container {
            max-width: 1180px;
        }

        .page-title {
            font-size: clamp(1.65rem, 2vw, 2.25rem);
            font-weight: 750;
            letter-spacing: 0;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .panel-header {
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
        }

        .metric {
            color: #fff;
            border: 0;
            border-radius: 8px;
        }

        .metric-success { background: var(--success); }
        .metric-danger { background: var(--danger); }
        .metric-brand { background: var(--brand); }
        .metric-warning { background: var(--warning); }

        .metric .card-header {
            border-bottom-color: rgba(255, 255, 255, 0.18);
            background: transparent;
            font-weight: 600;
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #f8fafc;
            color: var(--muted);
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody tr.table-success td,
        .table tbody tr.table-danger td {
            --bs-table-bg: transparent;
        }

        .table tbody tr.is-paid {
            background: #edf8f1;
        }

        .table tbody tr.is-open {
            background: #fff1f2;
        }

        .btn {
            border-radius: 7px;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--brand);
            border-color: var(--brand);
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .form-control,
        .form-select {
            border-color: var(--border);
            border-radius: 7px;
        }

        .form-label {
            color: var(--muted);
            font-weight: 600;
        }

        .badge-soft {
            background: #e2e8f0;
            color: #334155;
            border-radius: 999px;
            font-weight: 700;
            padding: 0.4rem 0.65rem;
        }

        .chart-box {
            position: relative;
            min-height: 300px;
        }

        .insight-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            background: #f8fafc;
            height: 100%;
        }

        .insight-label {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 750;
            text-transform: uppercase;
        }

        @media (max-width: 767.98px) {
            .app-layout {
                display: block;
            }

            .sidebar {
                width: 100%;
                padding: 0 0.75rem 1rem;
            }

            .sidebar-panel {
                position: static;
                overflow-x: auto;
                white-space: nowrap;
            }

            .sidebar-nav {
                display: flex;
                gap: 0.4rem;
            }

            .sidebar-title,
            .sidebar-link small {
                display: none;
            }

            .sidebar-link {
                flex: 0 0 auto;
            }

            .action-bar {
                width: 100%;
            }

            .action-bar .btn {
                flex: 1 1 auto;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container page-container d-flex justify-content-between gap-3">
                <a class="navbar-brand" href="{{ route('resumo') }}">Finanças</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
                    </form>
                @endauth
            </div>
        </nav>

        <div class="app-layout container-fluid px-0">
            @auth
                <aside class="sidebar">
                    <div class="sidebar-panel">
                        <div class="sidebar-title">Menu</div>
                        <nav class="sidebar-nav">
                            <a class="sidebar-link" href="{{ route('resumo') }}">Resumo <small>01</small></a>
                            <a class="sidebar-link" href="{{ route('resumo') }}#graficos">Gráficos <small>02</small></a>
                            <a class="sidebar-link" href="{{ route('resumo') }}#receitas">Entradas <small>03</small></a>
                            <a class="sidebar-link" href="{{ route('resumo') }}#despesas">Saídas <small>04</small></a>
                            <a class="sidebar-link" href="{{ route('categories.index') }}">Categorias <small>05</small></a>
                            <a class="sidebar-link" href="{{ route('receitas.create') }}">Nova entrada <small>+</small></a>
                            <a class="sidebar-link" href="{{ route('despesas.create') }}">Nova saída <small>+</small></a>
                        </nav>
                    </div>
                </aside>
            @endauth

            <main class="content-area container page-container pb-5">
                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
