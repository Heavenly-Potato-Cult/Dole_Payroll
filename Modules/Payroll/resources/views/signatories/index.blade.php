@extends('layouts.app')

@section('title', 'Signatories')
@section('page-title', 'Signatories')

@section('styles')
<style>
/* ── Signatory cards ──────────────────────────────────────── */
.sig-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 32px; }

.sig-card {
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
.sig-card:hover { box-shadow: var(--shadow-md); }
.sig-card.is-active { border-left: 3px solid #2E7D52; }
.sig-card.is-inactive { opacity: 0.65; }

.sig-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: var(--navy-light);
    color: var(--navy);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.88rem;
    flex-shrink: 0;
}
.sig-card.is-active .sig-avatar {
    background: #E8F5E9;
    color: #2E7D52;
}

.sig-info { flex: 1; min-width: 0; }
.sig-name {
    font-size: 0.90rem; font-weight: 600; color: var(--navy);
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.sig-meta {
    font-size: 0.78rem; color: var(--text-light);
    margin-top: 1px;
}

.sig-role-tag {
    font-size: 0.68rem; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
    background: var(--navy-light); color: var(--navy);
    border: 1px solid rgba(26,43,107,0.15);
    flex-shrink: 0;
}

.active-pill {
    font-size: 0.65rem; font-weight: 700;
    padding: 1px 8px; border-radius: 20px;
    background: #E8F5E9; color: #2E7D52;
    border: 1px solid #A5D6A7;
}

.sig-actions { display: flex; gap: 6px; flex-shrink: 0; }

/* ── Role group header ────────────────────────────────────── */
.role-group-header {
    font-size: 0.70rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-light);
    padding: 4px 0 8px;
    margin-top: 8px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 10px;
}
.role-group-header:first-child { margin-top: 0; }

/* ── Info banner ──────────────────────────────────────────── */
.sig-info-banner {
    background: #EEF1FA;
    border: 1px solid #C8D2EE;
    border-radius: var(--radius);
    padding: 14px 18px;
    font-size: 0.83rem;
    color: var(--navy);
    margin-bottom: 24px;
    line-height: 1.6;
}
.sig-info-banner strong { color: var(--navy); }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 600px) {
    .sig-card { flex-wrap: wrap; }
    .sig-actions { width: 100%; justify-content: flex-end; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Signatories</h1>
        <p>Signing officers shown on payslips and official reports</p>
    </div>
    <a href="{{ route('signatories.create') }}" class="btn btn-primary">+ Add Signatory</a>
</div>

{{-- How it works banner --}}
<div class="sig-info-banner">
    <strong>How this works:</strong>
    Only <strong>one signatory per role can be active</strong> at a time.
    Activating a new person automatically deactivates the previous one for that role.
    The active signatory's name appears on all payslips and reports generated from that point forward.
    When a designate changes, simply activate the new officer here — no code changes needed.
</div>

{{-- Alerts --}}
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ── Group signatories by role_type ── --}}
@php
    $roleLabels = [
        'hrmo_designate' => 'HRMO Designate',
        'accountant'     => 'Accountant',
        'ard'            => 'ARD / RD',
        'cashier'        => 'Cashier',
    ];
    $grouped = $signatories->groupBy('role_type');
@endphp

@if ($signatories->isEmpty())
    <div style="padding:48px; text-align:center; background:white; border:1px solid var(--border);
                border-radius:var(--radius); color:var(--text-light);">
        <div style="font-size:2rem; margin-bottom:12px;">✍</div>
        <p>No signatories yet.</p>
        <a href="{{ route('signatories.create') }}" class="btn btn-primary" style="margin-top:12px;">
            + Add the first signatory
        </a>
    </div>
@else
    <div class="sig-list">
        @foreach ($grouped as $roleType => $group)

            <div class="role-group-header">
                {{ $roleLabels[$roleType] ?? ucwords(str_replace('_', ' ', $roleType)) }}
                <span style="font-weight:400; margin-left:6px; font-size:0.68rem;">
                    ({{ $group->where('is_active', true)->count() }} active
                    of {{ $group->count() }})
                </span>
            </div>

            @foreach ($group as $sig)
            @php
                $initials = collect(explode(' ', trim($sig->full_name)))
                    ->filter()->take(2)
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->join('');
            @endphp

            <div class="sig-card {{ $sig->is_active ? 'is-active' : 'is-inactive' }}">

                <div class="sig-avatar">{{ $initials }}</div>

                <div class="sig-info">
                    <div class="sig-name">
                        {{ $sig->full_name }}
                        @if ($sig->is_active)
                            <span class="active-pill">✓ Active</span>
                        @endif
                    </div>
                    <div class="sig-meta">
                        {{ $sig->position_title ?? '—' }}
                        · Added {{ $sig->created_at->format('M d, Y') }}
                    </div>
                </div>

                <div class="sig-role-tag">
                    {{ $roleLabels[$sig->role_type] ?? ucwords(str_replace('_', ' ', $sig->role_type)) }}
                </div>

                <div class="sig-actions">

                    {{-- Toggle active/inactive --}}
                    <form method="POST" action="{{ route('signatories.toggle', $sig) }}"
                          onsubmit="return confirm('{{ $sig->is_active
                              ? 'Deactivate ' . addslashes($sig->full_name) . '? Payslips will show no active signatory for this role until another is activated.'
                              : 'Set ' . addslashes($sig->full_name) . ' as the active signatory for this role? The current active person will be deactivated.' }}')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="btn btn-sm {{ $sig->is_active ? 'btn-outline' : 'btn-primary' }}"
                                title="{{ $sig->is_active ? 'Deactivate' : 'Set as Active' }}">
                            {{ $sig->is_active ? '⏸ Deactivate' : '▶ Set Active' }}
                        </button>
                    </form>

                    <a href="{{ route('signatories.edit', $sig) }}"
                       class="btn btn-outline btn-sm">✎ Edit</a>

                    <form method="POST" action="{{ route('signatories.destroy', $sig) }}"
                          onsubmit="return confirm('Remove {{ addslashes($sig->full_name) }}?\nThis cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>

                </div>
            </div>

            @endforeach
        @endforeach
    </div>
@endif

@endsection
