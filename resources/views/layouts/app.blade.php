<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — DOLE RO9 Payroll</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>
<body>

<div class="app-shell">

    {{-- Mobile overlay (tap to close sidebar) --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-brand">
            <div class="sidebar-logo-wrap">
                <img src="{{ asset('assets/img/dole_logo.png') }}" alt="DOLE" class="sidebar-logo">
            </div>
            <div class="sidebar-title">
                <strong>DOLE RO9 Payroll</strong>
                <span>Zamboanga Peninsula</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">⊞</span> Dashboard
            </a>

            {{-- ── Employees ─────────────────────────────────────────── --}}
            @role('payroll_officer|hrmo|accountant|chief_admin_officer')
            <div class="nav-section-label">Employees</div>
            <a href="{{ route('employees.index') }}"
               class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <span class="nav-icon">👤</span> Employees
            </a>
            @role('payroll_officer|hrmo')
            <a href="{{ route('divisions.index') }}"
               class="nav-item {{ request()->routeIs('divisions.*') ? 'active' : '' }}">
                <span class="nav-icon">🏢</span> Divisions
            </a>
            @endrole
            @endrole

            {{-- ── Payroll ────────────────────────────────────────────── --}}
            @role('payroll_officer|hrmo|accountant|ard|cashier|chief_admin_officer')
            <div class="nav-section-label">Payroll</div>
            <a href="{{ route('payroll.index') }}"
               class="nav-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span> Regular Payroll
            </a>

            @role('payroll_officer|hrmo|accountant|ard|chief_admin_officer')
            <div class="nav-section-label" style="padding-left:12px; font-size:0.65rem;">Special Payroll</div>
            <a href="{{ route('special-payroll.newly-hired.index') }}"
               class="nav-item {{ request()->routeIs('special-payroll.newly-hired.*') ? 'active' : '' }}"
               style="padding-left:28px;">
                <span class="nav-icon">🆕</span> Newly Hired
            </a>
            <a href="{{ route('special-payroll.differential.index') }}"
               class="nav-item {{ request()->routeIs('special-payroll.differential.*') ? 'active' : '' }}"
               style="padding-left:28px;">
                <span class="nav-icon">📈</span> Salary Differential
            </a>
            {{-- <a href="{{ route('special-payroll.nosi-nosa.index') }}"
               class="nav-item {{ request()->routeIs('special-payroll.nosi-nosa.*') ? 'active' : '' }}"
               style="padding-left:28px;">
                <span class="nav-icon">📑</span> NOSI / NOSA
            </a> --}}
            @endrole

            @endrole

            {{-- ── Travel (TEV) ───────────────────────────────────────── --}}
            @role('hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer')
            <div class="nav-section-label">Travel (TEV)</div>
            <a href="{{ route('office-orders.index') }}"
               class="nav-item {{ request()->routeIs('office-orders.*') ? 'active' : '' }}">
                <span class="nav-icon">📝</span> Office Orders
            </a>
            <a href="{{ route('tev.index') }}"
               class="nav-item {{ request()->routeIs('tev.*') ? 'active' : '' }}">
                <span class="nav-icon">✈</span> TEV Requests
            </a>
            @endrole

            {{-- ── Reports ─────────────────────────────────────────────── --}}
            {{--
                Show Reports section to any role that can access at least one report.
                payroll_officer + accountant + ard + chief_admin_officer → payroll/remittance reports
                hrmo + budget_officer + cashier → TEV register / employee TEV history
                Single @role block avoids the duplicate-section bug in the original layout.
            --}}
            @role('payroll_officer|hrmo|accountant|ard|cashier|chief_admin_officer|budget_officer')
            <div class="nav-section-label">Reports</div>

            {{-- TEV Register — all TEV roles --}}
            @role('hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer')
            <a href="{{ route('reports.tev-register') }}"
               class="nav-item {{ request()->routeIs('reports.tev-register*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> TEV Register
            </a>
            @endrole

{{-- GSIS Remittance — payroll_officer + hrmo + accountant --}}
@role('payroll_officer|hrmo|accountant')
<a href="{{ route('reports.gsis') }}"
   class="nav-item {{ request()->routeIs('reports.gsis*') ? 'active' : '' }}">
    <span class="nav-icon">🏦</span> GSIS Remittance
</a>
<a href="{{ route('reports.hdmf') }}"
   class="nav-item {{ request()->routeIs('reports.hdmf*') ? 'active' : '' }}">
    <span class="nav-icon">🏧</span> HDMF / Pag-IBIG
</a>
<a href="{{ route('reports.remittances') }}"
   class="nav-item {{ request()->routeIs('reports.remittances*') || request()->routeIs('reports.caress*') || request()->routeIs('reports.lbp*') || request()->routeIs('reports.mass*') || request()->routeIs('reports.provident*') || request()->routeIs('reports.btr*') || request()->routeIs('reports.phic*') || request()->routeIs('reports.sss*') ? 'active' : '' }}">
    <span class="nav-icon">📑</span> All Remittances
</a>
@endrole

            @endrole

            {{-- ── Administration ─────────────────────────────────────── --}}
            @role('payroll_officer')
            <div class="nav-section-label">Administration</div>
            <a href="{{ route('users.index') }}"
               class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="nav-icon">⚙</span> User Management
            </a>
            @endrole

        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->getRoleNames()->first() ?? 'No Role' }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">⏻ Sign Out</button>
            </form>
        </div>

    </aside>

    {{-- ═══ MAIN AREA ═══ --}}
    <div class="main-content">

        <header class="topbar">
            <div class="topbar-left">
                <button class="burger-btn" onclick="openSidebar()" aria-label="Open menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="role-badge">
                        {{ str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? '') }}
                    </span>
                </div>
            </div>
        </header>

        <main class="page-body">

            @if (session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">⚠ {{ session('error') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">⚠ {{ session('warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin-top:6px; padding-left:16px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

</div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
    document.body.style.overflow = '';
}
document.querySelectorAll('.nav-item').forEach(link => {
    link.addEventListener('click', closeSidebar);
});
</script>
@yield('scripts')

</body>
</html>
