{{-- resources/views/tev/index.blade.php --}}
{{--
    Expects from TevController@index:
      $tevRequests — paginated TevRequest with employee, officeOrder
      $currentYear — int
--}}

@extends('layouts.app')

@section('title', 'TEV Requests')
@section('page-title', 'Travel (TEV)')

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>TEV Requests</h1>
        <p>Travel Expense Vouchers — Cash Advance and Reimbursement.</p>
    </div>
    @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
        <a href="{{ route('tev.create') }}" class="btn btn-primary">+ New TEV</a>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ── Filter bar ── --}}
<div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('tev.index') }}"
              style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">

            <div class="form-group" style="margin:0; min-width:180px;">
                <label for="track" style="margin-bottom:4px;">Track</label>
                <select name="track" id="track">
                    <option value="">All Tracks</option>
                    <option value="cash_advance"   {{ request('track') === 'cash_advance'   ? 'selected' : '' }}>Cash Advance</option>
                    <option value="reimbursement"  {{ request('track') === 'reimbursement'  ? 'selected' : '' }}>Reimbursement</option>
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:200px;">
                <label for="status" style="margin-bottom:4px;">Status</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="draft"                {{ request('status') === 'draft'                ? 'selected' : '' }}>Draft</option>
                    <option value="submitted"            {{ request('status') === 'submitted'            ? 'selected' : '' }}>Submitted</option>
                    <option value="hr_approved"          {{ request('status') === 'hr_approved'          ? 'selected' : '' }}>HR Approved</option>
                    <option value="accountant_certified" {{ request('status') === 'accountant_certified' ? 'selected' : '' }}>Accountant Certified</option>
                    <option value="rd_approved"          {{ request('status') === 'rd_approved'          ? 'selected' : '' }}>RD Approved</option>
                    <option value="cashier_released"     {{ request('status') === 'cashier_released'     ? 'selected' : '' }}>Released</option>
                    <option value="reimbursed"           {{ request('status') === 'reimbursed'           ? 'selected' : '' }}>Reimbursed</option>
                    <option value="rejected"             {{ request('status') === 'rejected'             ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="form-group" style="margin:0; min-width:120px;">
                <label for="year" style="margin-bottom:4px;">Year</label>
                <select name="year" id="year">
                    <option value="">All Years</option>
                    @foreach (range($currentYear, $currentYear - 3) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>

        </form>
    </div>
</div>

{{-- ── Table ── --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>TEV No.</th>
                        <th>Employee</th>
                        <th>Track</th>
                        <th>Office Order</th>
                        <th>Travel Dates</th>
                        <th class="text-right">Grand Total</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tevRequests as $tev)
                        @php
                            $emp = $tev->employee;

                            $trackLabel = $tev->track === 'cash_advance' ? 'Cash Advance' : 'Reimbursement';
                            $trackStyle = $tev->track === 'cash_advance'
                                ? 'background:#E8F5E9; color:#1B5E20; border:1px solid #43A047;'
                                : 'background:#E8EAF6; color:#1A237E; border:1px solid #3949AB;';

                            $statusClass = match ($tev->status) {
                                'submitted'            => 'badge-pending',
                                'hr_approved'          => 'badge-computed',
                                'accountant_certified' => 'badge-computed',
                                'rd_approved'          => 'badge-released',
                                'cashier_released'     => 'badge-locked',
                                'reimbursed'           => 'badge-locked',
                                'rejected'             => 'badge-inactive',
                                default                => 'badge-draft',
                            };
                            $statusLabel = ucwords(str_replace('_', ' ', $tev->status));

                            $isOwner = $emp && $emp->user_id === auth()->id();
                            $canSubmit = $tev->status === 'draft'
                                && ($isOwner || auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']));
                        @endphp
                        <tr>
                            <td class="fw-bold" style="color:var(--navy); white-space:nowrap;">
                                {{ $tev->tev_no }}
                            </td>

                            <td class="fw-bold">
                                {{ optional($emp)->last_name }},
                                {{ optional($emp)->first_name }}
                                @if (optional($emp)->middle_name)
                                    {{ substr($emp->middle_name, 0, 1) }}.
                                @endif
                            </td>

                            <td>
                                <span style="font-size:0.72rem; font-weight:700; padding:3px 8px;
                                             border-radius:12px; {{ $trackStyle }}">
                                    {{ $trackLabel }}
                                </span>
                            </td>

                            <td style="font-size:0.82rem;">
                                {{ optional($tev->officeOrder)->office_order_no ?? '—' }}
                            </td>

                            <td class="text-muted" style="font-size:0.82rem; white-space:nowrap;">
                                {{ $tev->travel_date_start->format('M d') }}
                                –
                                {{ $tev->travel_date_end->format('M d, Y') }}
                            </td>

                            <td class="text-right fw-bold">
                                ₱{{ number_format($tev->grand_total, 2) }}
                            </td>

                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            <td>
                                <div class="d-flex gap-2" style="justify-content:center;">
                                    <a href="{{ route('tev.show', $tev->id) }}"
                                       class="btn btn-outline btn-sm">View</a>

                                    @if ($canSubmit)
                                        <form method="POST"
                                              action="{{ route('tev.submit', $tev->id) }}"
                                              onsubmit="return confirm('Submit this TEV for approval?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-light);">
                                No TEV requests found.
                                @if (auth()->user()->hasAnyRole(['payroll_officer', 'hrmo']))
                                    <a href="{{ route('tev.create') }}">Create one now →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:12px;">{{ $tevRequests->links() }}</div>

@endsection