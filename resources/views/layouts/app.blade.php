<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Itinex')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        /* ══════════════════════════════════════════════════════
           ITINEX DESIGN SYSTEM v2.0
           Global CSS Variables — change these to retheme
           ══════════════════════════════════════════════════════ */
        :root {
            --font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --color-primary: #4f46e5;
            --color-primary-hover: #4338ca;
            --color-primary-light: #eef2ff;
            --color-primary-ring: rgba(99,102,241,0.18);
            --color-primary-50: #eef2ff;
            --color-primary-100: #e0e7ff;
            --color-primary-600: #4f46e5;
            --color-primary-700: #4338ca;
            --color-success: #059669;
            --color-success-light: #dcfce7;
            --color-warning: #d97706;
            --color-warning-light: #fef3c7;
            --color-danger: #dc2626;
            --color-danger-light: #fee2e2;
            --color-info: #0284c7;
            --color-info-light: #e0f2fe;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-sidebar: #0f172a;
            --bg-sidebar-hover: #1e293b;
            --bg-sidebar-active: rgba(99,102,241,0.12);
            --bg-input: #ffffff;
            --bg-table-head: #f8fafc;
            --bg-table-hover: #f8fafc;
            --bg-muted: #f1f5f9;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-sidebar: rgba(148,163,184,0.9);
            --text-sidebar-active: #ffffff;
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
            --radius-xs: 4px;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 18px;
            --radius-full: 9999px;
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
            --duration-fast: 0.15s;
            --duration-normal: 0.25s;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-family);
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: inherit; text-decoration: none; }
        ::selection { background: var(--color-primary-light); color: var(--color-primary); }

        /* ── Layout ── */
        .app-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            color: #fff;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 50;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .sidebar-brand {
            padding: 20px 24px;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.75px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand span { color: #818cf8; }
        .sidebar-nav { padding: 8px 0; flex: 1; }
        .sidebar-section {
            padding: 24px 24px 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: rgba(148,163,184,0.5);
            font-weight: 700;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 24px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-sidebar);
            transition: all var(--duration-fast) var(--ease);
            border-left: 3px solid transparent;
            margin: 1px 0;
        }
        .sidebar-nav a:hover {
            color: var(--text-sidebar-active);
            background: var(--bg-sidebar-hover);
        }
        .sidebar-nav a.active {
            color: var(--text-sidebar-active);
            background: var(--bg-sidebar-active);
            border-left-color: #818cf8;
            font-weight: 600;
        }
        .sidebar-nav a .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
            opacity: 0.7;
        }
        .sidebar-nav a.active .nav-icon { opacity: 1; }
        .sidebar-footer { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-footer .sidebar-user { display: flex; align-items: center; gap: 10px; }
        .sidebar-footer .sidebar-avatar { width: 34px; height: 34px; border-radius: var(--radius-full); background: linear-gradient(135deg, #818cf8, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-footer .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-footer .sidebar-user-name { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-footer .sidebar-user-role { font-size: 10px; color: var(--text-sidebar); text-transform: uppercase; letter-spacing: 0.5px; }

        .main-content { flex: 1; display: flex; flex-direction: column; margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: rgba(255,255,255,0.97);
            padding: 0 32px;
            height: var(--topbar-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 40;
            backdrop-filter: blur(12px);
        }
        .topbar h2 { font-size: 17px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.3px; }
        .topbar-user { display: flex; align-items: center; gap: 14px; font-size: 13px; color: var(--text-secondary); }
        .topbar-user .user-name { font-weight: 600; color: var(--text-primary); }
        .topbar-user .role-badge {
            background: var(--color-primary-50);
            color: var(--color-primary-600);
            padding: 3px 12px;
            border-radius: var(--radius-full);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .content-area { padding: 28px 32px; flex: 1; }

        /* ── Cards ── */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: var(--shadow-xs);
        }
        .card-header {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-flush { padding: 0; overflow: hidden; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 20px 24px;
            transition: all var(--duration-fast) var(--ease);
            box-shadow: var(--shadow-xs);
            cursor: default;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
        .stat-card .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; }
        .stat-card .stat-value { font-size: 26px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.75px; line-height: 1.2; }
        .stat-card .stat-icon {
            width: 40px; height: 40px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            margin-bottom: 12px;
        }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; border-radius: var(--radius-md); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            text-align: left;
            padding: 10px 16px;
            background: var(--bg-table-head);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        td { padding: 12px 16px; border-bottom: 1px solid var(--border-light); color: var(--text-secondary); }
        tr:hover td { background: var(--bg-table-hover); }
        .td-name { font-weight: 600; color: var(--text-primary); }
        .td-muted { color: var(--text-muted); font-size: 12px; }
        .td-money { font-weight: 600; font-variant-numeric: tabular-nums; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 10px;
            border-radius: var(--radius-full);
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .badge-green { background: var(--color-success-light); color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: var(--color-primary-light); color: #4338ca; }
        .badge-amber { background: var(--color-warning-light); color: #92400e; }
        .badge-red { background: var(--color-danger-light); color: #991b1b; }
        .badge-gray { background: var(--bg-muted); color: var(--text-secondary); }
        .badge-teal { background: #ccfbf1; color: #115e59; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--duration-fast) var(--ease);
            line-height: 1.4;
            font-family: inherit;
            white-space: nowrap;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: var(--color-primary); color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,0.25); }
        .btn-primary:hover { background: var(--color-primary-hover); box-shadow: 0 4px 12px rgba(79,70,229,0.25); }
        .btn-success { background: var(--color-success); color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: var(--color-danger); }
        .btn-warning { background: var(--color-warning); color: #fff; }
        .btn-warning:hover { background: #b45309; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); }
        .btn-outline:hover { background: var(--bg-muted); border-color: #cbd5e1; }
        .btn-ghost { background: none; border: none; color: var(--text-secondary); padding: 8px 16px; border-radius: var(--radius-sm); cursor: pointer; font-size: 13px; font-weight: 500; transition: all var(--duration-fast); font-family: inherit; }
        .btn-ghost:hover { background: var(--bg-muted); color: var(--text-primary); }
        .btn-sm { padding: 5px 14px; font-size: 12px; }
        .btn-xs { padding: 3px 10px; font-size: 11px; }
        .btn-icon { width: 34px; height: 34px; padding: 0; border-radius: var(--radius-sm); }
        .btn-icon.sm { width: 28px; height: 28px; font-size: 14px; }

        /* ── Forms ── */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 5px;
            letter-spacing: 0.1px;
        }
        .form-control {
            width: 100%;
            padding: 9px 14px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            transition: all var(--duration-fast) var(--ease);
            background: var(--bg-input);
            color: var(--text-primary);
            font-family: inherit;
        }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 9px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13.5px; transition: all var(--duration-fast) var(--ease); background: var(--bg-input); color: var(--text-primary); font-family: inherit; }
        .form-control:focus,
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-ring); }
        .form-control::placeholder,
        .form-group input::placeholder { color: var(--text-muted); }
        .form-row { display: grid; gap: 16px; }
        .form-row.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-row.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .form-row.cols-4 { grid-template-columns: repeat(4, 1fr); }
        .help-text { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ── Login page ── */
        .login-wrapper { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, #312e81 0%, #4f46e5 40%, #7c3aed 100%); }
        .login-card { background: #fff; border-radius: var(--radius-xl); padding: 44px; width: 100%; max-width: 420px; box-shadow: var(--shadow-xl); }
        .login-card h1 { font-size: 28px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px; }
        .login-card p.subtitle { color: var(--text-muted); margin-bottom: 28px; font-size: 14px; }
        .login-btn { width: 100%; padding: 12px; background: var(--color-primary); color: #fff; border: none; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600; cursor: pointer; transition: all var(--duration-fast); font-family: inherit; }
        .login-btn:hover { background: var(--color-primary-hover); box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
        .error-msg { background: #fef2f2; color: #991b1b; padding: 10px 14px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 16px; border: 1px solid #fecaca; }
        .logout-form button { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 13px; padding: 6px 12px; border-radius: var(--radius-sm); transition: all var(--duration-fast); font-family: inherit; }
        .logout-form button:hover { background: var(--bg-muted); color: var(--text-primary); }

        /* ── Modal ── */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-backdrop.open { display: flex; }
        .modal { background: var(--bg-card); border-radius: var(--radius-xl); padding: 32px; width: 100%; max-width: 520px; box-shadow: var(--shadow-xl); animation: modalIn 0.2s var(--ease); }
        .modal.lg { max-width: 720px; }
        .modal.xl { max-width: 900px; }
        @keyframes modalIn { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
        .modal h3 { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }

        /* ── Toast ── */
        .toast { position: fixed; top: 20px; right: 20px; z-index: 200; padding: 14px 24px; border-radius: var(--radius-md); font-size: 13px; font-weight: 500; box-shadow: var(--shadow-lg); animation: toastIn 0.35s cubic-bezier(0.21,1.02,0.73,1), toastOut 0.3s ease 3s forwards; display: flex; align-items: center; gap: 8px; }
        .toast-success { background: var(--color-success); color: #fff; }
        .toast-error { background: var(--color-danger); color: #fff; }
        @keyframes toastIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toastOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-10px); } }

        /* ── Page header ── */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 16px; flex-wrap: wrap; }
        .page-header h2 { font-size: 20px; font-weight: 800; letter-spacing: -0.4px; }
        .page-header .header-actions { display: flex; gap: 8px; align-items: center; }
        .page-title-count { color: var(--text-muted); font-weight: 400; font-size: 15px; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 56px 24px; color: var(--text-muted); }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
        .empty-state p { font-size: 14px; }

        /* ── Delete form inline ── */
        .delete-form { display: inline; }
        .delete-form button { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: var(--radius-sm); transition: all var(--duration-fast); font-family: inherit; }
        .delete-form button:hover { background: var(--color-danger-light); }

        /* ── Tabs ── */
        .tab-nav { display: flex; gap: 0; border-bottom: 2px solid var(--border-color); margin-bottom: 24px; overflow-x: auto; }
        .tab-nav a, .tab-nav button { padding: 10px 20px; font-size: 13px; font-weight: 600; color: var(--text-muted); border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all var(--duration-fast); white-space: nowrap; font-family: inherit; }
        .tab-nav a:hover, .tab-nav button:hover { color: var(--text-primary); }
        .tab-nav a.active, .tab-nav button.active { color: var(--color-primary); border-bottom-color: var(--color-primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .sub-tab-nav { display: flex; gap: 4px; margin-bottom: 20px; background: var(--bg-muted); border-radius: var(--radius-sm); padding: 4px; }
        .sub-tab-nav button { padding: 7px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); border: none; background: none; cursor: pointer; border-radius: var(--radius-sm); transition: all var(--duration-fast); font-family: inherit; }
        .sub-tab-nav button:hover { color: var(--text-primary); }
        .sub-tab-nav button.active { background: var(--bg-card); color: var(--color-primary); box-shadow: var(--shadow-sm); }

        /* ── Section/Accordion ── */
        .form-section { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); margin-bottom: 16px; box-shadow: var(--shadow-xs); overflow: hidden; }
        .form-section-header { padding: 16px 24px; display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none; transition: all var(--duration-fast); }
        .form-section-header:hover { background: var(--bg-table-head); }
        .form-section-header .sec-num { width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
        .form-section-header h3 { font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0; }
        .form-section-header .sec-arrow { margin-left: auto; font-size: 12px; color: var(--text-muted); transition: transform var(--duration-normal); }
        .form-section.collapsed .form-section-header .sec-arrow { transform: rotate(-90deg); }
        .form-section-body { padding: 20px 24px; border-top: 1px solid var(--border-light); }
        .form-section.collapsed .form-section-body { display: none; }

        /* ── Search & Filters ── */
        .search-input {
            width: 100%; max-width: 400px;
            padding: 9px 14px 9px 38px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: inherit;
            background: var(--bg-card) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") 12px center no-repeat;
            transition: all var(--duration-fast);
        }
        .search-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-ring); }
        .filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .filter-bar .search-input { flex: 1; min-width: 200px; }
        .filter-select { padding: 9px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; background: var(--bg-card); color: var(--text-primary); min-width: 140px; cursor: pointer; font-family: inherit; }
        .filter-select:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-ring); }
        .alpha-bar { display: flex; gap: 2px; flex-wrap: wrap; margin-bottom: 16px; }
        .alpha-bar a, .alpha-bar button {
            min-width: 30px; height: 30px;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            font-size: 11px; font-weight: 600;
            cursor: pointer;
            color: var(--text-muted);
            transition: all var(--duration-fast);
            text-decoration: none;
            padding: 0 6px;
        }
        .alpha-bar a:hover, .alpha-bar a.active,
        .alpha-bar button:hover, .alpha-bar button.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

        /* ── Side Drawer ── */
        .drawer-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.4); z-index: 90; backdrop-filter: blur(2px); }
        .drawer-overlay.open { display: block; }
        .side-drawer { position: fixed; top: 0; right: -560px; width: 540px; max-width: 92vw; height: 100vh; background: var(--bg-card); box-shadow: -8px 0 30px rgba(0,0,0,0.12); z-index: 95; transition: right 0.3s var(--ease); display: flex; flex-direction: column; }
        .side-drawer.open { right: 0; }
        .drawer-header { padding: 20px 28px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .drawer-header h3 { font-size: 17px; font-weight: 700; margin: 0; }
        .drawer-close { width: 34px; height: 34px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--text-muted); transition: all var(--duration-fast); }
        .drawer-close:hover { background: var(--bg-muted); color: var(--text-primary); }
        .drawer-body { flex: 1; overflow-y: auto; padding: 24px 28px; }
        .drawer-footer { padding: 16px 28px; border-top: 1px solid var(--border-color); display: flex; gap: 12px; justify-content: flex-end; flex-shrink: 0; background: var(--bg-muted); }

        /* ── Sticky save ── */
        .sticky-save { position: sticky; bottom: 0; background: var(--bg-card); border-top: 1px solid var(--border-color); padding: 16px 24px; display: flex; gap: 12px; justify-content: flex-end; z-index: 10; box-shadow: 0 -4px 12px rgba(0,0,0,0.04); }

        /* ── Action cells ── */
        .action-cell { display: flex; gap: 4px; align-items: center; }
        .action-cell a, .action-cell button { padding: 4px 10px; font-size: 11.5px; font-weight: 600; border-radius: var(--radius-sm); border: none; cursor: pointer; transition: all var(--duration-fast); text-decoration: none; font-family: inherit; }
        .act-open { background: var(--color-primary-light); color: var(--color-primary); }
        .act-open:hover { background: var(--color-primary); color: #fff; }
        .act-edit { background: var(--color-warning-light); color: #92400e; }
        .act-edit:hover { background: var(--color-warning); color: #fff; }
        .act-delete { background: var(--color-danger-light); color: #991b1b; }
        .act-delete:hover { background: var(--color-danger); color: #fff; }

        /* ── Status indicators ── */
        .status-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 5px; }
        .status-dot.active { background: var(--color-success); }
        .status-dot.inactive { background: var(--text-muted); }

        /* ── Category & type tags ── */
        .cat-tag { display: inline-block; padding: 2px 10px; border-radius: var(--radius-full); font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .cat-budget { background: var(--color-warning-light); color: #92400e; }
        .cat-midrange { background: var(--color-info-light); color: #1e40af; }
        .cat-luxury { background: #fce7f3; color: #9d174d; }
        .type-tag { display: inline-block; padding: 2px 10px; border-radius: var(--radius-full); font-size: 10.5px; font-weight: 600; letter-spacing: 0.3px; }
        .type-national_park { background: #dcfce7; color: #166534; }
        .type-conservancy { background: #fef3c7; color: #92400e; }
        .type-reserve { background: #dbeafe; color: #1e40af; }
        .type-marine_park { background: #e0e7ff; color: #4338ca; }
        .type-other { background: var(--bg-muted); color: var(--text-secondary); }

        /* ── Pagination ── */
        .pagination-wrap { display:flex; justify-content:center; margin-top:16px; }
        .pagination-wrap nav { display:flex; gap:4px; align-items:center; }
        .pagination-wrap nav a, .pagination-wrap nav span {
            display:inline-flex; align-items:center; justify-content:center;
            min-width:32px; height:32px; padding:0 8px;
            font-size:12px; border-radius:var(--radius-sm);
            border:1px solid var(--border-color); text-decoration:none; color:var(--text-secondary); font-family: inherit;
        }
        .pagination-wrap nav span[aria-current] { background:var(--color-primary); color:#fff; border-color:var(--color-primary); font-weight:600; }
        .pagination-wrap nav a:hover { background:var(--bg-muted); }
        .pagination-wrap nav span.disabled { opacity:.4; }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .sidebar { width: 220px; }
            .main-content { margin-left: 220px; }
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .content-area { padding: 20px 16px; }
            .form-row.cols-3, .form-row.cols-4 { grid-template-columns: 1fr 1fr; }
            .form-row.cols-2 { grid-template-columns: 1fr; }
            .topbar { padding: 0 16px; }
        }

        @yield('styles')
    </style>
</head>
<body>
    @yield('body')

    <script>
    /* ── Global Tab System ── */
    function initTabs(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const navBtns = container.querySelectorAll('.tab-nav [data-tab]');
        const contents = container.querySelectorAll('.tab-content');
        navBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                navBtns.forEach(b => b.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                const target = container.querySelector('#' + btn.dataset.tab);
                if (target) target.classList.add('active');
            });
        });
    }
    function initSubTabs(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const navBtns = container.querySelectorAll('.sub-tab-nav [data-subtab]');
        const contents = container.querySelectorAll('[data-subtab-content]');
        navBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                navBtns.forEach(b => b.classList.remove('active'));
                contents.forEach(c => c.style.display = 'none');
                btn.classList.add('active');
                const target = container.querySelector('[data-subtab-content="' + btn.dataset.subtab + '"]');
                if (target) target.style.display = 'block';
            });
        });
    }
    /* ── Accordion ── */
    function toggleSection(id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('collapsed');
    }
    /* ── Side Drawer ── */
    function openDrawer(id) {
        document.getElementById(id).classList.add('open');
        const overlay = document.getElementById(id + 'Overlay') || document.getElementById(id + '-overlay');
        if (overlay) overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer(id) {
        document.getElementById(id).classList.remove('open');
        const overlay = document.getElementById(id + 'Overlay') || document.getElementById(id + '-overlay');
        if (overlay) overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    /* ── Tabs helper ── */
    function showTab(name, prefix) {
        prefix = prefix || 'panel-';
        const btnPrefix = prefix === 'panel-' ? 'tab-' : prefix.replace('panel-', 'tab-');
        document.querySelectorAll('[id^="' + prefix + '"]').forEach(p => p.style.display = 'none');
        document.querySelectorAll('[id^="' + btnPrefix + '"]').forEach(b => { b.classList.remove('active'); });
        const panel = document.getElementById(prefix + name);
        const btn = document.getElementById(btnPrefix + name);
        if (panel) panel.style.display = 'block';
        if (btn) btn.classList.add('active');
    }
    /* ── Table filter ── */
    function filterTable(tableId, searchInputId, filters) {
        const search = (document.getElementById(searchInputId)?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr[data-search]');
        rows.forEach(row => {
            const text = row.dataset.search.toLowerCase();
            let show = !search || text.includes(search);
            if (show && filters) {
                for (const [key, val] of Object.entries(filters)) {
                    if (val && !row.dataset[key]?.toLowerCase().includes(val.toLowerCase())) { show = false; break; }
                }
            }
            row.style.display = show ? '' : 'none';
        });
    }
    </script>
    @yield('scripts')
</body>
</html>
