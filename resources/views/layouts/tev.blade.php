<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TEV Dashboard') — DOLE RO9 Payroll</title>
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
                <strong>DOLE RO9 TEV</strong>
                <span>Travel & Expense Voucher</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <a href="{{ route('tev.dashboard') }}"
               class="nav-item {{ request()->routeIs('tev.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">⊞</span> TEV Dashboard
            </a>

            {{-- ── Office Orders ─────────────────────────────────────── --}}
            @role('hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer|super_admin')
            <div class="nav-section-label">Travel Management</div>
            <a href="{{ route('tev.office-orders.index') }}"
               class="nav-item {{ request()->routeIs('tev.office-orders.*') ? 'active' : '' }}">
                <span class="nav-icon">📝</span> Office Orders
            </a>
            <a href="{{ route('tev.requests.index') }}"
               class="nav-item {{ request()->routeIs('tev.requests.*') ? 'active' : '' }}">
                <span class="nav-icon">✈</span> TEV Requests
            </a>
            @endrole

            {{-- ── Reports ─────────────────────────────────────────────── --}}
            @role('hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer|super_admin')
            <div class="nav-section-label">Reports</div>
            <a href="{{ route('reports.tev-register') }}"
               class="nav-item {{ request()->routeIs('reports.tev-register*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> TEV Register
            </a>
            @endrole

            {{-- ── Switch to Payroll ───────────────────────────────────── --}}
            @role('payroll_officer|hrmo|accountant|ard|cashier|chief_admin_officer|super_admin')
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border);">
                <a href="{{ route('dashboard') }}"
                   class="nav-item"
                   style="font-size: 0.75rem; color: var(--text-light);">
                    <span class="nav-icon">💰</span> Switch to Payroll
                </a>
            </div>
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
                <span class="topbar-title">@yield('page-title', 'TEV Dashboard')</span>
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
