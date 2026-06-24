<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CineVault') — Movie Rental System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg:      #0a0a0f;
            --bg2:     #12121a;
            --bg3:     #1a1a26;
            --bg4:     #22223a;
            --card:    #15151f;
            --border:  #2a2a42;
            --border2: #3a3a5a;
            --gold:    #c8a04a;
            --gold2:   #e8c46a;
            --text:    #f0ede8;
            --text2:   #b8b4aa;
            --text3:   #787068;
            --green:   #52c07a;
            --red:     #e05252;
            --blue:    #5294e0;
            --purple:  #9452e0;
            --sidebar-w: 220px;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            overflow-x: hidden; /* prevent horizontal scroll on body */
        }

        /* ── Sidebar ──────────────────────────────── */
        .sidebar {
            background: var(--bg2);
            border-right: 1px solid var(--border);
            width: var(--sidebar-w);
            height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 40;
            transition: transform .25s ease;
            overflow: hidden; /* nothing bleeds out */
        }

        /* Sidebar overlay (mobile) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            z-index: 39;
        }
        .sidebar-overlay.open { display: block; }

        /* ── Main content ──────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0; /* critical: prevents content from forcing width */
            overflow-x: hidden;
        }

        /* ── Topbar ────────────────────────────────── */
        .topbar {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 10px;
            position: sticky;
            top: 0;
            z-index: 30;
            min-width: 0;
        }

        .topbar-title {
            flex: 1;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0; /* actions never get squeezed away */
        }

        /* Hamburger — hidden on desktop */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text2);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .sidebar-toggle:hover { background: var(--bg3); color: var(--text); }

        /* ── Page content ──────────────────────────── */
        .page-content {
            padding: 24px;
            flex: 1;
            min-width: 0;
            overflow-x: hidden;
        }

        /* ── Nav ───────────────────────────────────── */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 6px;
            color: var(--text2);
            font-size: 13px;
            transition: all .15s;
            cursor: pointer;
            width: 100%;
            border: none;
            background: none;
            text-decoration: none;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-item:hover { background: var(--bg3); color: var(--text); }
        .nav-item.active { background: rgba(200,160,74,.12); color: var(--gold); border: 1px solid rgba(200,160,74,.2); }
        .nav-item svg, .nav-item i { width: 16px; flex-shrink: 0; font-size: 16px; }
        .nav-badge { margin-left: auto; background: var(--red); color: #fff; font-size: 10px; padding: 1px 6px; border-radius: 10px; font-weight: 600; flex-shrink: 0; }
        .nav-badge.green { background: var(--green); }
        .nav-section-label { font-size: 9px; color: var(--text3); letter-spacing: 1.5px; text-transform: uppercase; padding: 8px 12px 3px; white-space: nowrap; }

        /* ── Buttons ───────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all .15s;
            text-decoration: none;
            white-space: nowrap; /* button labels never wrap */
            flex-shrink: 0;
        }
        .btn-primary { background: var(--gold); color: #000; }
        .btn-primary:hover { background: var(--gold2); }
        .btn-secondary { background: transparent; color: var(--text2); border: 1px solid var(--border2); }
        .btn-secondary:hover { background: var(--bg3); color: var(--text); }
        .btn-danger { background: rgba(224,82,82,.12); color: var(--red); border: 1px solid rgba(224,82,82,.25); }
        .btn-danger:hover { background: rgba(224,82,82,.22); }
        .btn-success { background: rgba(82,192,122,.12); color: var(--green); border: 1px solid rgba(82,192,122,.25); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ── Cards ─────────────────────────────────── */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .card-body { padding: 20px; }

        /* ── Forms ─────────────────────────────────── */
        .form-input, .form-select, .form-textarea {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 9px 12px;
            color: var(--text);
            font-size: 13px;
            width: 100%;
            outline: none;
            transition: border .15s;
            font-family: inherit;
            min-width: 0; /* allow shrinking inside flex/grid */
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--gold); }
        .form-label { display: block; font-size: 12px; color: var(--text2); font-weight: 500; margin-bottom: 5px; }
        .form-group { margin-bottom: 14px; }

        /* ── Table ─────────────────────────────────── */
        /* Wrap .data-table in .table-wrapper for horizontal scroll on small screens */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 560px; /* table never gets too squished; wrapper scrolls instead */
        }
        .data-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .8px;
            border-bottom: 1px solid var(--border);
            background: var(--bg2);
            font-weight: 600;
            white-space: nowrap;
        }
        .data-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            /* long text in cells truncates rather than breaks layout */
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .data-table tbody tr:hover td { background: var(--bg3); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* ── Badges ────────────────────────────────── */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-green  { background: rgba(82,192,122,.12); color: var(--green); border: 1px solid rgba(82,192,122,.2); }
        .badge-red    { background: rgba(224,82,82,.12);  color: var(--red);   border: 1px solid rgba(224,82,82,.2); }
        .badge-blue   { background: rgba(82,148,224,.12); color: var(--blue);  border: 1px solid rgba(82,148,224,.2); }
        .badge-gold   { background: rgba(200,160,74,.12); color: var(--gold);  border: 1px solid rgba(200,160,74,.2); }
        .badge-purple { background: rgba(148,82,224,.12); color: var(--purple);border: 1px solid rgba(148,82,224,.2); }

        /* ── Alerts ────────────────────────────────── */
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; overflow: hidden; }
        .alert-success { background: rgba(82,192,122,.1); border: 1px solid rgba(82,192,122,.25); color: var(--green); }
        .alert-error   { background: rgba(224,82,82,.1);  border: 1px solid rgba(224,82,82,.25);  color: var(--red); }

        /* ── Pulse ─────────────────────────────────── */
        .pulse { width: 7px; height: 7px; border-radius: 50%; background: var(--red); display: inline-block; animation: pulse 1.5s infinite; flex-shrink: 0; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }

        /* ── Modal ─────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.75);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px; /* ensures modal never touches edges */
        }
        .modal {
            background: var(--bg2);
            border: 1px solid var(--border2);
            border-radius: 14px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header { padding: 18px 22px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .modal-header-title { font-size: 15px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .modal-body { padding: 22px; }
        .modal-footer { padding: 14px 22px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }

        /* ── Stat cards ────────────────────────────── */
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; position: relative; overflow: hidden; }
        .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background: var(--accent-color, var(--gold)); }
        /* Responsive stat grid helper — use class="stat-grid" on the wrapper */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; }

        /* ── Poster watermark ──────────────────────── */
        .poster-watermark { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(0deg,rgba(10,10,15,.9),transparent); padding: 10px 9px 7px; display: flex; align-items: center; gap: 5px; overflow: hidden; }
        .watermark-text { font-size: 9px; color: rgba(200,160,74,.7); font-weight: 600; letter-spacing: 1px; white-space: nowrap; }

        /* ── Pagination ────────────────────────────── */
        .pagination { display: flex; gap: 4px; align-items: center; justify-content: center; padding: 16px 0; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 6px 10px; border-radius: 6px; font-size: 12px; border: 1px solid var(--border); color: var(--text2); text-decoration: none; white-space: nowrap; }
        .pagination a:hover { background: var(--bg3); color: var(--text); }
        .pagination .active span { background: var(--gold); color: #000; border-color: var(--gold); }

        /* ── Search ────────────────────────────────── */
        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0 12px;
            height: 36px;
            min-width: 0;
        }
        .search-box input { background: none; border: none; outline: none; color: var(--text); font-size: 13px; min-width: 0; width: 100%; }

        /* ── User footer (sidebar) ─────────────────── */
        .user-footer-name {
            font-size: 12px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════════
           RESPONSIVE BREAKPOINTS
        ═══════════════════════════════════════════ */

        /* Tablet & below: sidebar becomes a drawer */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                /* sidebar slides in/out via JS toggling .open */
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0,0,0,.5);
            }
            .main-content {
                margin-left: 0; /* full width when sidebar is hidden */
            }
            .sidebar-toggle {
                display: flex; /* show hamburger */
            }
            .page-content {
                padding: 16px;
            }
            .topbar {
                padding: 0 14px;
                gap: 8px;
            }
        }

        /* Very small screens */
        @media (max-width: 480px) {
            .page-content { padding: 12px; }
            .card-body { padding: 14px; }
            .modal-body { padding: 16px; }
            .modal-header, .modal-footer { padding-left: 16px; padding-right: 16px; }
            .btn { padding: 7px 11px; font-size: 12px; }
            /* On tiny screens, action buttons in topbar can shrink to icon-only */
            .btn-label-hide { display: none; }
        }
    </style>
</head>
<body>

{{-- ── Sidebar overlay (mobile tap-to-close) ──────────────────────────── --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
<div class="sidebar" id="sidebar">
    {{-- Logo --}}
    <div style="padding: 18px 16px 14px; border-bottom: 1px solid var(--border); flex-shrink: 0;">
        <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
            <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                <rect width="34" height="34" rx="7" fill="#1a1a26"/>
                <rect x="5" y="8" width="24" height="14" rx="2" fill="none" stroke="#c8a04a" stroke-width="1.4"/>
                <rect x="5" y="10" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <rect x="5" y="14" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <rect x="5" y="18" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <rect x="26" y="10" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <rect x="26" y="14" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <rect x="26" y="18" width="3" height="2.5" rx=".5" fill="#c8a04a"/>
                <path d="M14 13l4.5 2.5L14 18V13z" fill="#c8a04a"/>
                <rect x="9" y="24" width="16" height="2" rx="1" fill="#c8a04a" opacity=".4"/>
            </svg>
            <div style="min-width:0;">
                <div style="font-size:18px; font-weight:700; letter-spacing:-.5px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Cine<span style="color:var(--gold)">Vault</span></div>
                <div style="font-size:9px; color:var(--text3); letter-spacing:2px; text-transform:uppercase;">Rental System</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav style="flex:1; padding:10px 8px; overflow-y:auto; overflow-x:hidden;">
        @auth
            @if(auth()->user()->isAdmin())
                <div class="nav-section-label">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.movies.index') }}" class="nav-item {{ request()->routeIs('admin.movies.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                    Movies
                    <span class="nav-badge green">{{ \App\Models\Movie::count() }}</span>
                </a>
                <a href="{{ route('admin.rentals.index') }}" class="nav-item {{ request()->routeIs('admin.rentals.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Rentals
                    <span class="nav-badge">{{ \App\Models\Rental::where('status','active')->count() }}</span>
                </a>

                <div class="nav-section-label" style="margin-top:8px;">Admin</div>
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.approvals.index') }}" class="nav-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Approvals
                    @php $pending = \App\Models\Approval::pending()->count() @endphp
                    @if($pending > 0)
                        <span class="nav-badge">{{ $pending }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Reports
                </a>
                <a href="{{ route('admin.audit.index') }}" class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Audit Logs
                </a>

            @elseif(auth()->user()->isStaff())
                <div class="nav-section-label">Staff Panel</div>
                <a href="{{ route('staff.dashboard') }}" class="nav-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('staff.movies.index') }}" class="nav-item {{ request()->routeIs('staff.movies.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                    Movies
                </a>
                <a href="{{ route('staff.rentals.index') }}" class="nav-item {{ request()->routeIs('staff.rentals.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Rentals
                </a>
                <a href="{{ route('staff.approvals.index') }}" class="nav-item {{ request()->routeIs('staff.approvals.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    My Requests
                </a>

            @else
                <div class="nav-section-label">Member</div>
                <a href="{{ route('user.dashboard') }}" class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('user.movies.index') }}" class="nav-item {{ request()->routeIs('user.movies.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                    Browse Movies
                </a>
                <a href="{{ route('user.rentals.index') }}" class="nav-item {{ request()->routeIs('user.rentals.*') ? 'active' : '' }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    My Rentals
                </a>
            @endif
        @endauth
    </nav>

    {{-- User footer --}}
    @auth
    <div style="padding: 10px; border-top: 1px solid var(--border); flex-shrink: 0;">
        <div style="display:flex; align-items:center; gap:10px; padding:9px; background:var(--bg3); border-radius:6px; overflow:hidden;">
            <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,var(--gold),var(--gold2)); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:#000; flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div style="flex:1; min-width:0;">
                <a href="{{ route('profile.edit') }}" class="user-footer-name" style="display:block; color:inherit; text-decoration:none; cursor:pointer;">{{ auth()->user()->name }}</a>
                <div style="font-size:10px; color:var(--text3); text-transform:capitalize; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->role }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0;">
                @csrf
                <button type="submit" style="background:none; border:none; color:var(--text3); cursor:pointer; font-size:12px;" title="Logout">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
    @endauth
</div>

{{-- ── Main Content ────────────────────────────────────────────────────────── --}}
<div class="main-content">
    {{-- Topbar --}}
    <div class="topbar">
        {{-- Hamburger (visible on mobile only) --}}
        <button class="sidebar-toggle" onclick="openSidebar()" aria-label="Open navigation">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>

        <div class="topbar-actions">
            @yield('topbar-actions')
        </div>
    </div>

    {{-- Flash messages --}}
    <div style="padding: 0 20px;">
        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                <span style="overflow:hidden; text-overflow:ellipsis;">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" style="margin-top:16px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="overflow:hidden; text-overflow:ellipsis;">{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error" style="margin-top:16px; flex-direction:column; align-items:flex-start;">
                @foreach($errors->all() as $error)
                    <div style="overflow:hidden; text-overflow:ellipsis; max-width:100%;">• {{ $error }}</div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Page content --}}
    <div class="page-content">
        @yield('content')
    </div>
</div>

{{-- ── Sidebar JS ──────────────────────────────────────────────────────────── --}}
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
        document.body.style.overflow = 'hidden'; // prevent background scroll
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }
    // Close sidebar on nav-item click (mobile UX)
    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
</script>

@yield('scripts')
</body>
</html>