@extends('layouts.app')

@section('title', 'Divisions')
@section('page-title', 'Divisions')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Divisions</h1>
        <p>DOLE RO9 organisational divisions (managed via HRIS API)</p>
    </div>
</div>

{{-- ── Search bar ────────────────────────────────────────────── --}}
<div class="card mb-2" style="margin-bottom:18px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('divisions.index') }}"
              style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" name="search" placeholder="Search name or code…"
                   value="{{ $search }}"
                   style="max-width:320px;margin-bottom:0;">
            <button type="submit" class="btn btn-outline btn-sm">Search</button>
            @if($search)
                <a href="{{ route('divisions.index') }}" class="btn btn-sm"
                   style="background:var(--bg);border:1.5px solid var(--border);color:var(--text-mid);">
                    Clear
                </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Table ─────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3>All Divisions</h3>
        <span class="text-muted" style="font-size:0.82rem;">
            {{ $divisions->total() }} {{ Str::plural('division', $divisions->total()) }}
        </span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:52px;">#</th>
                    <th style="width:90px;">Code</th>
                    <th>Division Name</th>
                    <th>Description</th>
                    <th style="width:90px;text-align:center;">Employees</th>
                    <th style="width:90px;text-align:center;">Status</th>
                    {{-- Actions column removed - divisions managed via HRIS API --}}
                </tr>
            </thead>
            <tbody>
                @forelse ($divisions as $division)
                <tr>
                    <td class="text-muted" style="font-size:0.80rem;">
                        {{ $divisions->firstItem() + $loop->index }}
                    </td>
                    <td>
                        <code style="background:var(--navy-light);color:var(--navy);
                                     padding:2px 8px;border-radius:4px;font-size:0.78rem;
                                     font-weight:700;letter-spacing:0.04em;">
                            {{ $division->code }}
                        </code>
                    </td>
                    <td class="fw-bold" style="color:var(--navy);">
                        {{ $division->name }}
                    </td>
                    <td class="text-muted" style="font-size:0.84rem;">
                        {{ Str::limit($division->description, 80, '…') ?: '—' }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge" style="background:var(--navy-light);color:var(--navy);">
                            {{ $division->employees_count }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @if ($division->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-light);">
                        @if($search)
                            No divisions matched "<strong>{{ $search }}</strong>".
                        @else
                            No divisions found. Divisions are synced from the HRIS API.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($divisions->hasPages())
    <div style="padding:4px 20px 8px;">
        {{ $divisions->links('pagination::custom') }}
    </div>
    @endif
</div>

@endsection

@section('scripts')
{{-- No scripts needed - divisions managed via HRIS API --}}
@endsection