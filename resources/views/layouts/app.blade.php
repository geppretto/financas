<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Financeiro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --page-bg: #f8fafc;
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
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #0a1222;
            height: 64px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            min-width: 0;
        }

        .topbar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.01em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .topbar-brand:hover { color: #fff; }

        .version-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            font-weight: 500;
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            padding: 0.15rem 0.4rem;
        }

        .topnav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            overflow-x: auto;
        }

        .topnav a {
            color: #cbd5e1;
            font-size: 0.86rem;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
        }

        .topnav a:hover { color: #fff; background: rgba(255, 255, 255, 0.06); }
        .topnav a.active { color: #fff; background: rgba(255, 255, 255, 0.12); }

        .top-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.55rem 0.9rem;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
        }

        .top-action:hover { background: rgba(255, 255, 255, 0.16); color: #fff; }
        .top-action.in { color: #34d399; }
        .top-action.out { color: #fb7185; }
        .top-action svg { width: 14px; height: 14px; }

        .top-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
        }

        .top-user-text { text-align: right; line-height: 1.15; }
        .top-user-name { font-size: 0.8rem; font-weight: 600; }
        .top-user-plan { font-size: 0.66rem; color: #94a3b8; font-weight: 500; }

        .top-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            display: grid;
            place-items: center;
            color: #fff;
        }

        .top-avatar svg { width: 16px; height: 16px; }

        .top-logout {
            background: none;
            border: 0;
            color: #cbd5e1;
            padding: 0.25rem;
            display: grid;
            place-items: center;
        }

        .top-logout:hover { color: #fff; }
        .top-logout svg { width: 18px; height: 18px; }

        .topbar.light {
            background: #fff;
            border-bottom: 1px solid #eef1f6;
        }
        .topbar.light .topbar-brand,
        .topbar.light .topbar-brand:hover { color: #0a1222; }
        .topbar.light .version-tag { background: #e8f0fe; color: #2563eb; }
        .topbar.light .topnav a { color: #334155; }
        .topbar.light .topnav a:hover { background: #f1f5f9; color: #0a1222; }
        .topbar.light .topnav a.active { background: #e8f0fe; color: #0a1222; font-weight: 700; }
        .topbar.light .top-action { background: #e8f0fe; }
        .topbar.light .top-action:hover { background: #dbe7fd; }
        .topbar.light .top-action.in { color: #059669; }
        .topbar.light .top-action.out { color: #dc2626; }
        .topbar.light .top-user { color: #0a1222; }
        .topbar.light .top-user-plan { color: #475569; font-family: 'JetBrains Mono', monospace; font-size: .62rem; }
        .topbar.light .top-avatar { background: #0a1222; }
        .topbar.light .top-logout { color: #0a1222; }

        .content-area {
            flex: 1 1 auto;
            width: 100%;
            margin: 0 auto;
            padding: 1.5rem 32px 3rem;
        }

        .content-narrow { max-width: 1180px; }
        .content-wide { max-width: 1680px; }

        .app-footer {
            background: #fff;
            border-top: 1px solid #eef1f6;
            padding: 1.75rem 32px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.82rem;
            color: #475569;
        }

        .app-footer .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: #64748b;
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

        html, body { overflow-x: hidden; }

        @media (max-width: 991.98px) {
            /* Topo em grade: marca | usuário, menu rolável, ações lado a lado */
            .topbar {
                position: static;
                height: auto;
                padding: .65rem 16px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .6rem .5rem;
                align-items: center;
            }
            .topbar-left, .topbar-right { display: contents; }
            .topbar-brand { order: 1; grid-column: 1; }
            .top-user { order: 2; grid-column: 2; justify-self: end; gap: .5rem; min-width: 0; }
            .top-user-name { max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .top-user-plan { display: none; }
            .topnav {
                order: 3; grid-column: 1 / -1;
                overflow-x: auto; scrollbar-width: none;
                padding-bottom: 2px;
            }
            .topnav::-webkit-scrollbar { display: none; }
            .top-action { order: 4; justify-content: center; padding: .6rem .5rem; }
            .content-area { padding: 1rem 16px 2rem; }
            .app-footer { padding: 1.25rem 16px; flex-direction: column; align-items: flex-start; }
            .page-title { font-size: 1.6rem; }
        }

        .action-bar .btn { flex: 1 1 auto; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        @php $navAtivo = trim($__env->yieldContent('nav_active')); @endphp
        <header class="topbar @yield('topbar_class')">
            <div class="topbar-left">
                <a class="topbar-brand" href="{{ route('resumo') }}">Finanças <span class="version-tag">v2.4</span></a>
                @auth
                    <nav class="topnav">
                        <a href="{{ route('resumo') }}" class="{{ request()->routeIs('resumo') ? 'active' : '' }}">Resumo</a>
                        <a href="{{ route('resumo') }}#graficos">Gráficos</a>
                        <a href="{{ route('resumo') }}#receitas" class="{{ $navAtivo === 'entradas' ? 'active' : '' }}">Entradas</a>
                        <a href="{{ route('resumo') }}#despesas" class="{{ $navAtivo === 'saidas' ? 'active' : '' }}">Saídas</a>
                        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Categorias</a>
                    </nav>
                @endauth
            </div>

            @auth
                <div class="topbar-right">
                    <a href="{{ route('receitas.create') }}" class="top-action in"><i data-lucide="plus"></i> Nova receita</a>
                    <a href="{{ route('despesas.create') }}" class="top-action out"><i data-lucide="minus"></i> Nova despesa</a>
                    <div class="top-user">
                        {{-- <div class="top-user-text">
                            <div class="top-user-name">{{ auth()->user()->name }}</div>
                            <div class="top-user-plan">Plano Individual Pro</div>
                        </div> --}}
                        <span class="top-avatar"><i data-lucide="user"></i></span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="top-logout" title="Sair"><i data-lucide="log-out"></i></button>
                        </form>
                    </div>
                </div>
            @endauth
        </header>

        <main class="content-area @yield('content_class', 'content-narrow')">
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

        <footer class="app-footer">
            <span>© {{ date('Y') }} Finanças v2.4 • Gestão Financeira Pessoal e Familiar. Todos os direitos reservados.</span>
            <span class="mono">Status: Operacional</span>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.469.0/dist/umd/lucide.min.js"></script>
    <script>window.lucide && lucide.createIcons();</script>
</body>
</html>
