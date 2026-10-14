<!DOCTYPE html>
<html lang="pt-BR" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <script>
        // Aplica o tema salvo antes de renderizar (evita "piscar"). Persiste no navegador:
        // continua o mesmo ao entrar, sair ou fechar o site, até você trocar.
        (function () {
            try {
                var t = localStorage.getItem('financas-tema');
                if (t === 'dark' || t === 'light') {
                    document.documentElement.dataset.theme = t;
                    document.documentElement.dataset.bsTheme = t;
                }
            } catch (e) {}
        })();
    </script>
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

        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 0;
            color: #fff;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
        }
        .theme-toggle:hover { background: rgba(255, 255, 255, 0.18); }
        .theme-toggle svg { width: 16px; height: 16px; }
        .theme-toggle .ic-sun { display: none; }
        html[data-theme="dark"] .theme-toggle .ic-moon { display: none; }
        html[data-theme="dark"] .theme-toggle .ic-sun { display: inline-block; }

        /* ===================== MODO NOTURNO ===================== */
        html[data-theme="dark"] {
            --page-bg: #0b1220;
            --surface: #111a2e;
            --border: #22304a;
            --text: #e6edf7;
            --muted: #9fb0c8;
            color-scheme: dark;
        }
        html[data-theme="dark"] body { background: #0b1220; color: #e6edf7; }
        html[data-theme="dark"] .topbar { background: #070d1a; border-bottom: 1px solid #1a2740; }
        html[data-theme="dark"] .app-footer { background: #0f1729; border-top-color: #1a2740; color: #9fb0c8; }
        html[data-theme="dark"] .app-footer .mono { color: #7f91ad; }
        html[data-theme="dark"] .panel,
        html[data-theme="dark"] .insight-card { background: #111a2e; border-color: #22304a; }
        html[data-theme="dark"] .panel-header { border-bottom-color: #22304a; }
        html[data-theme="dark"] .table thead th { background: #16223a; color: #9fb0c8; }
        html[data-theme="dark"] .table tbody tr.is-paid { background: #0f2a22; }
        html[data-theme="dark"] .table tbody tr.is-open { background: #2d1820; }
        html[data-theme="dark"] .badge-soft { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select { background-color: #16223a; border-color: #2a3a57; color: #e6edf7; }
        html[data-theme="dark"] .bg-light { background-color: #16223a !important; }
        html[data-theme="dark"] .text-muted { color: #9fb0c8 !important; }
        html[data-theme="dark"] .form-label { color: #9fb0c8; }
        html[data-theme="dark"] .list-group-item { background: #111a2e; color: #e6edf7; border-color: #22304a; }
        html[data-theme="dark"] .list-group-item-action:hover { background: #16223a; }
        html[data-theme="dark"] .alert-success { background: #0f2a22; color: #86efac; }
        html[data-theme="dark"] .alert-danger { background: #2d1820; color: #fda4af; }
        html[data-theme="dark"] .alert-info { background: #14243f; color: #93c5fd; }

        /* Bolinhas de legenda que usam a cor "tinta" ficariam invisíveis */
        html[data-theme="dark"] i[style*="#0a1222"] { background: #e6edf7 !important; }
        html[data-theme="dark"] .rs-bar > span,
        html[data-theme="dark"] .rs-tabs button.active { }

        /* ---- Home (rs) ---- */
        html[data-theme="dark"] .rs { color: #e6edf7; }
        html[data-theme="dark"] .rs-card { background: #111a2e; border-color: #22304a; box-shadow: none; }
        html[data-theme="dark"] .rs-hello { background: #1a2842; }
        html[data-theme="dark"] .rs-hello .mono, html[data-theme="dark"] .rs-fiscal, html[data-theme="dark"] .rs-meta { color: #9fb0c8; }
        html[data-theme="dark"] .rs-fiscal { border-left-color: #2a3a57; }
        html[data-theme="dark"] .rs-select { background-color: #1a2842; color: #e6edf7; }
        html[data-theme="dark"] .rs-select option { background: #111a2e; color: #e6edf7; }
        html[data-theme="dark"] .rs-btn-dark { background: #e6edf7; color: #0a1222; }
        html[data-theme="dark"] .rs-btn-icon { background: #16223a; border-color: #2a3a57; color: #cbd5e1; }
        html[data-theme="dark"] .rs-ico.blue { background: rgba(37, 99, 235, .2); color: #7aa7ff; }
        html[data-theme="dark"] .rs-ico.red { background: rgba(220, 38, 38, .2); color: #ff8a93; }
        html[data-theme="dark"] .rs-ico.green { background: rgba(22, 163, 74, .2); color: #5ee9a0; }
        html[data-theme="dark"] .rs-ico.slate { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .rs-kpi-title, html[data-theme="dark"] .rs-kpi-sub { color: #cbd5e1; }
        html[data-theme="dark"] .rs-kpi-foot { color: #9fb0c8; }
        html[data-theme="dark"] .rs-kpi-foot .strong { color: #e6edf7; }
        html[data-theme="dark"] .rs-kpi-value.green { color: #34d399; }
        html[data-theme="dark"] .rs-chip.green { background: rgba(34, 197, 94, .18); color: #4ade80; }
        html[data-theme="dark"] .rs-chip.blue { background: rgba(59, 130, 246, .2); color: #93c5fd; }
        html[data-theme="dark"] .rs-chip.red { background: rgba(244, 63, 94, .2); color: #fda4af; }
        html[data-theme="dark"] .rs-chip.slate { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .rs-bar { background: #22304a; }
        html[data-theme="dark"] .rs-bar > span { background: #e6edf7; }
        html[data-theme="dark"] .rs-eye { color: #cbd5e1; }
        html[data-theme="dark"] .rs-insight .label { color: #9fb0c8; }
        html[data-theme="dark"] .rs-insight .hint.red { color: #fda4af; }
        html[data-theme="dark"] .rs-insight .hint.green { color: #4ade80; }
        html[data-theme="dark"] .rs-insight .hint.slate { color: #9fb0c8; }
        html[data-theme="dark"] .rs-panel-head .sub { color: #9fb0c8; }
        html[data-theme="dark"] .rs-tag { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .rs-legend { color: #cbd5e1; }
        html[data-theme="dark"] .rs-donut-center span, html[data-theme="dark"] .rs-cat-row .pct, html[data-theme="dark"] .rs-chart-foot { color: #9fb0c8; }
        html[data-theme="dark"] .rs-aviso { background: #14243f; border-color: #22406e; color: #cbd5e1; }
        html[data-theme="dark"] .rs-aviso a { color: #7aa7ff; }
        html[data-theme="dark"] .rs-tabs { background: #1a2842; }
        html[data-theme="dark"] .rs-tabs button { color: #9fb0c8; }
        html[data-theme="dark"] .rs-tabs button.active { background: #2a3a57; color: #fff; box-shadow: none; }
        html[data-theme="dark"] .rs-table thead th { background: #16223a; color: #9fb0c8; }
        html[data-theme="dark"] .rs-table tbody td { border-bottom-color: #1a2740; }
        html[data-theme="dark"] .rs-table .desc small { color: #9fb0c8; }
        html[data-theme="dark"] .rs-table .act a, html[data-theme="dark"] .rs-table .act button { color: #cbd5e1; }
        html[data-theme="dark"] .rs-status.pago { background: rgba(34, 197, 94, .18); color: #4ade80; }
        html[data-theme="dark"] .rs-status.pend { background: rgba(245, 158, 11, .18); color: #fbbf24; }
        html[data-theme="dark"] .rs-table tr.urg-azul td { background: #14243f; }
        html[data-theme="dark"] .rs-table tr.urg-amarelo td { background: #33290f; }
        html[data-theme="dark"] .rs-table tr.urg-vermelho td { background: #3a1820; }
        html[data-theme="dark"] .rs-table tr.urg-ok td { background: #0f2a22; }
        html[data-theme="dark"] .rs-table tr.linha-despesa + tr.linha-despesa td { border-top-color: #111a2e; }
        html[data-theme="dark"] .rs-table tr.urg-vermelho .desc small { color: #fda4af; }
        html[data-theme="dark"] .rs-table tr.urg-amarelo .desc small { color: #fbbf24; }
        html[data-theme="dark"] .rs-empty { background: #16223a; }
        html[data-theme="dark"] .rs-empty .badge-ico { background: #111a2e; }
        html[data-theme="dark"] .rs-empty p { color: #9fb0c8; }
        html[data-theme="dark"] .rs-table-foot, html[data-theme="dark"] .rs-table-foot a { color: #cbd5e1; }
        html[data-theme="dark"] .rs-table-foot a.green { color: #4ade80; }
        html[data-theme="dark"] .rs-scroll { scrollbar-color: #2a3a57 transparent; }
        html[data-theme="dark"] .rs-scroll::-webkit-scrollbar-thumb { background: #2a3a57; }
        @media (max-width: 640px) {
            html[data-theme="dark"] .rs-table tbody tr { background: #16223a; }
            html[data-theme="dark"] .rs-table tr.urg-azul { background: #14243f; }
            html[data-theme="dark"] .rs-table tr.urg-amarelo { background: #33290f; }
            html[data-theme="dark"] .rs-table tr.urg-vermelho { background: #3a1820; }
            html[data-theme="dark"] .rs-table tr.urg-ok { background: #0f2a22; }
        }

        /* ---- Categorias (ct) ---- */
        html[data-theme="dark"] .ct { color: #e6edf7; }
        html[data-theme="dark"] .ct-card { background: #111a2e; border-color: #22304a; box-shadow: none; }
        html[data-theme="dark"] .ct-crumb, html[data-theme="dark"] .ct-sub { color: #9fb0c8; }
        html[data-theme="dark"] .ct-crumb strong { color: #e6edf7; }
        html[data-theme="dark"] .ct-teto, html[data-theme="dark"] .ct-export { background: #111a2e; border-color: #22304a; color: #cbd5e1; box-shadow: none; }
        html[data-theme="dark"] .ct-teto strong, html[data-theme="dark"] .ct-export { color: #e6edf7; }
        html[data-theme="dark"] .ct-mode { background: rgba(34, 197, 94, .18); color: #4ade80; }
        html[data-theme="dark"] .ct-mode.edit { background: rgba(245, 158, 11, .18); color: #fbbf24; }
        html[data-theme="dark"] .ct-lbl { color: #cbd5e1; }
        html[data-theme="dark"] .ct-lbl .mono, html[data-theme="dark"] .ct-help, html[data-theme="dark"] .ct-name .s { color: #9fb0c8; }
        html[data-theme="dark"] .ct-input, html[data-theme="dark"] .ct-money, html[data-theme="dark"] .ct-search input { background: #1a2842; color: #e6edf7; }
        html[data-theme="dark"] .ct-money input { color: #e6edf7; }
        html[data-theme="dark"] .ct-input::placeholder, html[data-theme="dark"] .ct-search input::placeholder { color: #7f91ad; }
        html[data-theme="dark"] .ct-input-wrap svg, html[data-theme="dark"] .ct-search svg { color: #9fb0c8; }
        html[data-theme="dark"] .ct-tipo label { background: #1a2842; color: #cbd5e1; }
        html[data-theme="dark"] .ct-tipo input:checked + label { background: #e6edf7; color: #0a1222; }
        html[data-theme="dark"] .ct-icons, html[data-theme="dark"] .ct-colors { background: #1a2842; }
        html[data-theme="dark"] .ct-icons label { color: #cbd5e1; }
        html[data-theme="dark"] .ct-icons input:checked + label { background: #2a3a57; box-shadow: none; }
        html[data-theme="dark"] .ct-colors input:checked + label { box-shadow: 0 0 0 2px #1a2842, 0 0 0 4px #e6edf7; }
        html[data-theme="dark"] .ct-save { background: #e6edf7; color: #0a1222; }
        html[data-theme="dark"] .ct-save:hover { background: #fff; }
        html[data-theme="dark"] .ct-clear { background: #1a2842; color: #cbd5e1; }
        html[data-theme="dark"] .ct-auto-ico { background: #16223a; }
        html[data-theme="dark"] .ct-auto p { color: #9fb0c8; }
        html[data-theme="dark"] .ct-count { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .ct-table thead th { background: #16223a; color: #9fb0c8; }
        html[data-theme="dark"] .ct-meta .row1 { color: #9fb0c8; }
        html[data-theme="dark"] .ct-track { background: #22304a; }
        html[data-theme="dark"] .ct-acts a, html[data-theme="dark"] .ct-acts button { color: #cbd5e1; }
        html[data-theme="dark"] .ct-foot .mono { color: #9fb0c8; }
        @media (max-width: 640px) {
            html[data-theme="dark"] .ct-table tbody tr { background: #16223a; }
            html[data-theme="dark"] .ct-table td:nth-child(2)::before, html[data-theme="dark"] .ct-table td:nth-child(3)::before { color: #9fb0c8; }
        }

        /* ---- Nova despesa (nd) ---- */
        html[data-theme="dark"] .nd { color: #e6edf7; }
        html[data-theme="dark"] .nd-card { background: #111a2e; border-color: #22304a; box-shadow: none; }
        html[data-theme="dark"] .nd-crumb, html[data-theme="dark"] .nd-crumb a, html[data-theme="dark"] .nd-sub, html[data-theme="dark"] .nd-head p { color: #cbd5e1; }
        html[data-theme="dark"] .nd-tag-red { background: rgba(244, 63, 94, .2); color: #fda4af; }
        html[data-theme="dark"] .nd-budget { background: #16223a; color: #cbd5e1; }
        html[data-theme="dark"] .nd-ico-red { background: rgba(220, 38, 38, .2); color: #ff8a93; }
        html[data-theme="dark"] .nd-head { border-bottom-color: #22304a; }
        html[data-theme="dark"] .nd-chip { background: #1c2a45; color: #cbd5e1; }
        html[data-theme="dark"] .nd-label { color: #e6edf7; }
        html[data-theme="dark"] .nd-field { background: #1a2842; box-shadow: none; }
        html[data-theme="dark"] .nd-field input, html[data-theme="dark"] .nd-field select, html[data-theme="dark"] .nd-field textarea { color: #e6edf7; }
        html[data-theme="dark"] .nd-field input::placeholder, html[data-theme="dark"] .nd-field textarea::placeholder { color: #7f91ad; }
        html[data-theme="dark"] .nd-field > svg, html[data-theme="dark"] .nd-field .prefix { color: #9fb0c8; }
        html[data-theme="dark"] .nd-field select option { background: #111a2e; color: #e6edf7; }
        html[data-theme="dark"] .nd-parcelas { background: #16223a; }
        html[data-theme="dark"] .nd-parcelas .hint { color: #9fb0c8; }
        html[data-theme="dark"] .nd-parcelas input[type=checkbox] { accent-color: #60a5fa; }
        html[data-theme="dark"] .nd-parcelas-box { background: #14203a; }
        html[data-theme="dark"] .nd-status { box-shadow: none; border: 1px solid #22304a; }
        html[data-theme="dark"] .nd-status p, html[data-theme="dark"] .nd-notes-head .mono { color: #9fb0c8; }
        html[data-theme="dark"] .nd-seg { background: #1a2842; }
        html[data-theme="dark"] .nd-seg label { color: #9fb0c8; }
        html[data-theme="dark"] .nd-seg input:checked + label { background: #2a3a57; box-shadow: none; }
        html[data-theme="dark"] .nd-seg input[value="1"]:checked + label { color: #4ade80; }
        html[data-theme="dark"] .nd-seg input[value="0"]:checked + label { color: #fbbf24; }
        html[data-theme="dark"] .nd-actions { border-top-color: #22304a; }
        html[data-theme="dark"] .nd-btn.light { background: #1a2842; color: #e6edf7; }
        html[data-theme="dark"] .nd-overview-body { background: #16223a; }
        html[data-theme="dark"] .nd-bar-row .track { background: #22304a; }
        html[data-theme="dark"] .nd-side-title .mono, html[data-theme="dark"] .nd-progress-foot, html[data-theme="dark"] .nd-last-main .m { color: #9fb0c8; }
        html[data-theme="dark"] .nd-progress { background: #22304a; }
        html[data-theme="dark"] .nd-tip { background: #14243f; }
        html[data-theme="dark"] .nd-tip p, html[data-theme="dark"] .nd-tip svg { color: #cbd5e1; }
        html[data-theme="dark"] .nd-donut-center span { color: #9fb0c8; }
        html[data-theme="dark"] .nd-last-item { border-bottom-color: #22304a; }
        html[data-theme="dark"] .nd-last-ico { background: #1c2a45; color: #e6edf7; }
        html[data-theme="dark"] .nd-last-val { color: #fda4af; }
        html[data-theme="dark"] .nd a[style*="color:#0a1222"] { color: #e6edf7 !important; }
        html[data-theme="dark"] .nd .form-text, html[data-theme="dark"] .nd div[style*="color:#475569"] { color: #9fb0c8 !important; }

        /* ---- Nova receita (nr) ---- */
        html[data-theme="dark"] .nr { color: #e6edf7; }
        html[data-theme="dark"] .nr-crumb, html[data-theme="dark"] .nr-sub, html[data-theme="dark"] .nr-head p, html[data-theme="dark"] .nr-hint, html[data-theme="dark"] .nr-help { color: #9fb0c8; }
        html[data-theme="dark"] .nr-crumb .tag { background: rgba(34, 197, 94, .18); color: #4ade80; }
        html[data-theme="dark"] .nr-back { background: #111a2e; border-color: #22304a; color: #e6edf7; box-shadow: none; }
        html[data-theme="dark"] .nr-card { background: #111a2e; border-color: #22304a; box-shadow: none; }
        html[data-theme="dark"] .nr-head, html[data-theme="dark"] .nr-last-head, html[data-theme="dark"] .nr-total { background: #16223a; }
        html[data-theme="dark"] .nr-brl { background: #111a2e; box-shadow: none; }
        html[data-theme="dark"] .nr-field { background: #1a2842; }
        html[data-theme="dark"] .nr-field input, html[data-theme="dark"] .nr-field select { color: #e6edf7; }
        html[data-theme="dark"] .nr-field input::placeholder { color: #7f91ad; }
        html[data-theme="dark"] .nr-field svg { color: #9fb0c8; }
        html[data-theme="dark"] .nr-field select option { background: #111a2e; color: #e6edf7; }
        html[data-theme="dark"] .nr-rec { background: #16223a; }
        html[data-theme="dark"] .nr-rec-ico { background: #111a2e; }
        html[data-theme="dark"] .nr-rec-text span { color: #9fb0c8; }
        html[data-theme="dark"] .nr-switch .track { background: #2a3a57; }
        html[data-theme="dark"] .nr-btn.light { background: #1a2842; color: #e6edf7; }
        html[data-theme="dark"] .nr-btn.dark { background: #e6edf7; color: #0a1222; }
        html[data-theme="dark"] .nr-last-head a, html[data-theme="dark"] .nr-item-bot { color: #9fb0c8; }
        html[data-theme="dark"] .nr-item-top .v { color: #4ade80; }
        html[data-theme="dark"] .nr-tip { background: #14243f; color: #cbd5e1; }
        html[data-theme="dark"] .nr-tip b { color: #e6edf7; }
        html[data-theme="dark"] .nr-tip svg { color: #cbd5e1; }


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

            <div class="topbar-right">
                @auth
                    <a href="{{ route('receitas.create') }}" class="top-action in"><i data-lucide="plus"></i> Nova receita</a>
                    <a href="{{ route('despesas.create') }}" class="top-action out"><i data-lucide="minus"></i> Nova despesa</a>
                @endauth
                <div class="top-user">
                    <button type="button" class="theme-toggle" id="themeToggle" title="Alternar modo claro / noturno" aria-label="Alternar modo claro / noturno">
                        <i data-lucide="moon" class="ic-moon"></i>
                        <i data-lucide="sun" class="ic-sun"></i>
                    </button>
                    @auth
                        {{-- <div class="top-user-text">
                            <div class="top-user-name">{{ auth()->user()->name }}</div>
                            <div class="top-user-plan">Plano Individual Pro</div>
                        </div> --}}
                        <span class="top-avatar"><i data-lucide="user"></i></span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="top-logout" title="Sair"><i data-lucide="log-out"></i></button>
                        </form>
                    @endauth
                </div>
            </div>
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
    <script>
        document.getElementById('themeToggle')?.addEventListener('click', function () {
            var atual = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
            var novo = atual === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = novo;
            document.documentElement.dataset.bsTheme = novo;
            try { localStorage.setItem('financas-tema', novo); } catch (e) {}
            window.dispatchEvent(new CustomEvent('themechange', { detail: novo }));
        });
    </script>
</body>
</html>
