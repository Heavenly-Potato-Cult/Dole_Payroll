@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('styles')
<style>
/* ── User cards ──────────────────────────────────────────── */
.user-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 28px; }

.user-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    transition: box-shadow 0.15s;
}
.user-card:hover { box-shadow: var(--shadow-md); }

.user-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: var(--navy-light);
    color: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.92rem;
    flex-shrink: 0;
}

.user-info { flex: 1; min-width: 0; }
.user-name {
    font-size: 0.90rem; font-weight: 600; color: var(--navy);
    display: flex; align-items: center; gap: 6px;
    flex-wrap: wrap;
}
.user-email {
    font-size: 0.78rem; color: var(--text-light);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-top: 1px;
}

.you-pill {
    font-size: 0.65rem; font-weight: 600;
    padding: 1px 8px; border-radius: 20px;
    background: var(--gold-light); color: var(--gold-dark);
    border: 1px solid rgba(200,120,0,0.2);
}

.user-role { flex-shrink: 0; }
.user-actions { display: flex; gap: 6px; flex-shrink: 0; }

/* ── Role guide ──────────────────────────────────────────── */
.section-eyebrow {
    font-size: 0.70rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-light);
    margin-bottom: 12px;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.role-card {
    padding: 14px 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    border-top: 3px solid var(--navy);
}

.role-card-name {
    font-size: 0.86rem; font-weight: 700; color: var(--navy);
    margin-bottom: 4px;
}

.role-card-desc {
    font-size: 0.78rem; color: var(--text-mid);
    line-height: 1.5; margin-bottom: 10px;
}

.access-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.access-tag {
    font-size: 0.65rem; font-weight: 600;
    padding: 2px 8px; border-radius: 20px;
    background: var(--bg); color: var(--text-mid);
    border: 1px solid var(--border);
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 600px) {
    .user-card { flex-wrap: wrap; }
    .user-actions { width: 100%; justify-content: flex-end; }
    .role-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 400px) {
    .role-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>System Users</h1>
        <p>{{ $users->count() }} {{ Str::plural('account', $users->count()) }} registered</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Add User</a>
</div>

{{-- ── User list ──────────────────────────────────────────── --}}
<div class="user-list">
    @forelse ($users as $user)
    <div class="user-card">

        <div class="user-avatar">
            @php
                $parts = explode(' ', trim($user->name));
                echo strtoupper(substr($parts[0], 0, 1)) . strtoupper(substr($parts[1] ?? '', 0, 1));
            @endphp
        </div>

        <div class="user-info">
            <div class="user-name">
                {{ $user->name }}
                @if ($user->id === auth()->id())
                    <span class="you-pill">You</span>
                @endif
            </div>
            <div class="user-email">{{ $user->email }}</div>
        </div>

        <div class="user-role">
            @foreach ($user->roles as $role)
                <span class="badge badge-approved">
                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                </span>
            @endforeach
            @if ($user->roles->isEmpty())
                <span class="badge badge-draft">No Role</span>
            @endif
        </div>

        <div class="user-actions">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline btn-sm">✎ Edit</a>
            @if ($user->id !== auth()->id())
            <form method="POST" action="{{ route('users.destroy', $user) }}"
                  onsubmit="return confirm('Remove {{ addslashes($user->name) }}?\nThis cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">✕</button>
            </form>
            @endif
        </div>

    </div>
    @empty
    <div style="padding:40px;text-align:center;color:var(--text-light);background:white;border:1px solid var(--border);border-radius:var(--radius);">
        No users yet. <a href="{{ route('users.create') }}">Add the first user →</a>
    </div>
    @endforelse
</div>

{{-- ── Role permissions guide ────────────────────────────── --}}
<div class="section-eyebrow">Role permissions guide</div>
<div class="role-grid">

    <div class="role-card">
        <div class="role-card-name">Super Admin</div>
        <div class="role-card-desc">Manages system users and accounts. Can view all modules across the system. No payroll or TEV action rights.</div>
        <div class="access-tags">
            <span class="access-tag">User Management</span>
            <span class="access-tag">View All Modules</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">Payroll Officer</div>
        <div class="role-card-desc">Manages payroll processing and employee records. No access to TEV.</div>
        <div class="access-tags">
            <span class="access-tag">Employees</span>
            <span class="access-tag">Payroll</span>
            <span class="access-tag">Special Payroll</span>
            <span class="access-tag">Payroll Reports</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">HRMO</div>
        <div class="role-card-desc">Manages employee records, views payroll, creates Office Orders, and files TEV requests on behalf of traveling employees.</div>
        <div class="access-tags">
            <span class="access-tag">Employees</span>
            <span class="access-tag">View Payroll</span>
            <span class="access-tag">Office Orders</span>
            <span class="access-tag">TEV (create & submit)</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">Accountant</div>
        <div class="role-card-desc">First approver in the TEV workflow after submission. Certifies fund availability, generates remittance reports, and views payroll entries.</div>
        <div class="access-tags">
            <span class="access-tag">View Payroll</span>
            <span class="access-tag">Payroll Reports</span>
            <span class="access-tag">TEV (1st approval)</span>
            <span class="access-tag">TEV Reports</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">Budget Officer</div>
        <div class="role-card-desc">Views TEV spending and Office Orders for budget monitoring purposes. No approval actions in any workflow.</div>
        <div class="access-tags">
            <span class="access-tag">View Office Orders</span>
            <span class="access-tag">View TEV</span>
            <span class="access-tag">TEV Reports</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">Chief Admin Officer</div>
        <div class="role-card-desc">Division-level approver for both payroll and TEV.</div>
        <div class="access-tags">
            <span class="access-tag">View Payroll</span>
            <span class="access-tag">Approve Payroll</span>
            <span class="access-tag">TEV (division approval)</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">ARD</div>
        <div class="role-card-desc">Final approver for both payroll and TEV. Can generate payroll release.</div>
        <div class="access-tags">
            <span class="access-tag">Final Payroll Approval</span>
            <span class="access-tag">Payroll Reports</span>
            <span class="access-tag">Final TEV Approval</span>
            <span class="access-tag">TEV Reports</span>
        </div>
    </div>

    <div class="role-card">
        <div class="role-card-name">Cashier</div>
        <div class="role-card-desc">Marks payroll and TEV as released or paid. Final step in both workflows.</div>
        <div class="access-tags">
            <span class="access-tag">Release Payroll</span>
            <span class="access-tag">TEV (release/reimburse)</span>
        </div>
    </div>

    <div class="role-card" style="border-top-color: var(--text-light);">
        <div class="role-card-name">Employee</div>
        <div class="role-card-desc">Role reserved for rank-and-file employees. Specific page access is pending confirmation — no module access assigned yet.</div>
        <div class="access-tags">
            <span class="access-tag" style="background:var(--gold-light);color:var(--gold-dark);border-color:rgba(200,120,0,0.2);">Pending configuration</span>
        </div>
    </div>

</div>

@endsection
