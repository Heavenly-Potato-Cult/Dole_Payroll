{{-- resources/views/tev/show.blade.php --}}
{{--
    Expects from TevController@show:
      $tev         — TevRequest with employee, officeOrder, itineraryLines,
                     approvalLogs (with user), certification.certifier
      $canApprove  — bool
      $nextAction  — string label for the approve button
--}}

@extends('layouts.app')

@section('title', 'TEV — ' . $tev->tev_no)
@section('page-title', 'Travel (TEV)')

@section('styles')
<style>
/* Approval timeline */
.tev-timeline { display:flex; gap:0; margin-bottom:24px; overflow:hidden; border-radius:8px; border:1px solid var(--border); }
.tev-step {
    flex:1; padding:12px 16px; font-size:0.78rem; font-weight:600;
    display:flex; align-items:center; gap:8px;
    background:var(--surface); color:var(--text-light);
    border-right:1px solid var(--border); position:relative;
}
.tev-step:last-child { border-right:none; }
.tev-step.done     { background:#F1FAF5; color:#1B6B3A; }
.tev-step.active   { background:#EEF1FA; color:var(--navy); }
.tev-step.terminal { background:var(--navy); color:#fff; }
.tev-step.rejected-step { background:#FFF0F0; color:#B71C1C; }
.tev-step-dot {
    width:24px; height:24px; border-radius:50%; border:2px solid currentColor;
    display:flex; align-items:center; justify-content:center;
    font-size:0.75rem; font-weight:700; flex-shrink:0; background:#fff; color:inherit;
}
.tev-step.done .tev-step-dot    { background:#2E7D52; border-color:#2E7D52; color:#fff; }
.tev-step.active .tev-step-dot  { background:var(--navy); border-color:var(--navy); color:#fff; }
.tev-step.terminal .tev-step-dot { background:rgba(255,255,255,.15); color:#fff; border-color:rgba(255,255,255,.5); }

/* Detail grid */
.detail-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px,1fr)); gap:10px 20px; }
.detail-item { display:flex; flex-direction:column; gap:2px; }
.detail-item .label { font-size:0.70rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-light); }
.detail-item .value { font-weight:600; color:var(--text); font-size:0.88rem; }

/* Itinerary table */
.itin-tbl { width:100%; border-collapse:collapse; font-size:0.78rem; white-space:nowrap; }
.itin-tbl thead th {
    background:var(--navy); color:#fff; padding:7px 10px; text-align:center;
    font-size:0.70rem; font-weight:600; letter-spacing:0.03em;
    border:1px solid rgba(255,255,255,0.15);
}
.itin-tbl tbody td { padding:8px 10px; border:1px solid var(--border); vertical-align:middle; }
.itin-tbl tfoot td { padding:8px 10px; font-weight:700; background:#f0f2ff; border:1px solid var(--border); }
.itin-tbl td.text-right { text-align:right; }
.itin-tbl td.text-center { text-align:center; }

/* Log timeline */
.log-timeline { display:flex; flex-direction:column; gap:0; }
.log-item { display:flex; gap:14px; padding:12px 0; border-bottom:1px solid var(--border); }
.log-item:last-child { border-bottom:none; }
.log-dot { width:32px; height:32px; border-radius:50%; flex-shrink:0;
           display:flex; align-items:center; justify-content:center; font-size:0.85rem; }
.log-dot.approved { background:#E8F5E9; }
.log-dot.rejected { background:#FFEBEE; }
.log-dot.returned { background:#FFF3E0; }
.log-meta { font-size:0.75rem; color:var(--text-light); margin-top:2px; }

@media print {
    .no-print { display:none !important; }
    .tev-timeline { display:none !important; }
    .card { box-shadow:none !important; border:1px solid #ccc !important; }
    body { font-size:9pt; }
    @page { margin:1.2cm 1cm; }
}
</style>
@endsection

@section('content')

@php
    $emp = $tev->employee;

    $trackLabel = $tev->track === 'cash_advance' ? 'Cash Advance' : 'Reimbursement';
    $trackStyle = $tev->track === 'cash_advance'
        ? 'background:#E8F5E9; color:#1B5E20; border:1px solid #43A047;'
        : 'background:#E8EAF6; color:#1A237E; border:1px solid #3949AB;';

    $typeStyle = match ($tev->travel_type) {
        'regional' => 'background:#FFF8E1; color:#F57F17; border:1px solid #F9A825;',
        'national' => 'background:#E8EAF6; color:#1A237E; border:1px solid #3949AB;',
        default    => 'background:#E8F5E9; color:#1B5E20; border:1px solid #43A047;',
    };

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

    // Timeline steps
    $caSteps    = ['Draft','Submitted','HR Approved','Acct. Certified','RD Approved','Released'];
    $reimbSteps = ['Draft','Submitted','HR Approved','Acct. Certified','RD Approved','Reimbursed'];
    $steps      = $tev->track === 'cash_advance' ? $caSteps : $reimbSteps;
    $stepOrder  = ['draft','submitted','hr_approved','accountant_certified','rd_approved',
                   $tev->track === 'cash_advance' ? 'cashier_released' : 'reimbursed'];
    $currentIdx = array_search($tev->status, $stepOrder);
    if ($currentIdx === false) $currentIdx = -1;

    // Action permissions
    $isOwner   = $emp && $emp->user_id === auth()->id();
    $canSubmit = $tev->status === 'draft'
              && ($isOwner || auth()->user()->hasAnyRole(['payroll_officer','hrmo']));
    $canReject = !in_array($tev->status, ['draft','rejected','cashier_released','reimbursed'])
              && auth()->user()->hasAnyRole(['payroll_officer','hrmo','accountant','ard','chief_admin_officer','cashier']);
    $canCertify = in_array($tev->status, ['rd_approved','cashier_released','reimbursed'])
               && auth()->user()->hasAnyRole(['payroll_officer','hrmo','accountant']);
@endphp

<div class="page-header no-print">
    <div class="page-header-left">
        <h1>{{ $tev->tev_no }}</h1>
        <p>
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            &nbsp;
            <span style="font-size:0.73rem; font-weight:700; padding:3px 10px; border-radius:12px; {{ $trackStyle }}">
                {{ $trackLabel }}
            </span>
            &nbsp;
            <span style="font-size:0.73rem; font-weight:700; padding:3px 10px; border-radius:12px; {{ $typeStyle }}">
                {{ ucfirst($tev->travel_type) }}
            </span>
        </p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline btn-sm">🖨 Print</button>
        <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm">← Back</a>
    </div>
</div>

{{-- ── Approval timeline ── --}}
<div class="tev-timeline no-print">
    @foreach ($steps as $i => $label)
        @php
            $isDone   = $i < $currentIdx;
            $isActive = $i === $currentIdx;
            $isTerminal = $isActive && $i === count($steps) - 1;
            $isRejected = $tev->status === 'rejected' && $isActive;

            if ($isRejected) $stepClass = 'rejected-step';
            elseif ($isTerminal) $stepClass = 'terminal';
            elseif ($isDone)   $stepClass = 'done';
            elseif ($isActive) $stepClass = 'active';
            else               $stepClass = '';
        @endphp
        <div class="tev-step {{ $stepClass }}">
            <div class="tev-step-dot">
                {{ $isDone ? '✓' : ($i + 1) }}
            </div>
            {{ $label }}
        </div>
    @endforeach
</div>

<div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">

    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- ── TEV Info ── --}}
        <div class="card">
            <div class="card-header">
                <h3>✈ TEV Information</h3>
                <span style="font-size:0.78rem; color:var(--text-light);">
                    Filed: {{ $tev->created_at->format('M d, Y') }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid" style="margin-bottom:16px;">
                    <div class="detail-item">
                        <span class="label">TEV No.</span>
                        <span class="value" style="color:var(--navy);">{{ $tev->tev_no }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Track</span>
                        <span class="value">
                            <span style="font-size:0.73rem; font-weight:700; padding:3px 8px; border-radius:12px; {{ $trackStyle }}">
                                {{ $trackLabel }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Travel Type</span>
                        <span class="value">
                            <span style="font-size:0.73rem; font-weight:700; padding:3px 8px; border-radius:12px; {{ $typeStyle }}">
                                {{ ucfirst($tev->travel_type) }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Office Order</span>
                        <span class="value">
                            <a href="{{ route('office-orders.show', $tev->office_order_id) }}"
                               style="color:var(--navy);">
                                {{ optional($tev->officeOrder)->office_order_no ?? '—' }}
                            </a>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Employee</span>
                        <span class="value">
                            {{ optional($emp)->last_name }}, {{ optional($emp)->first_name }}
                            @if (optional($emp)->middle_name)
                                {{ substr($emp->middle_name, 0, 1) }}.
                            @endif
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Position</span>
                        <span class="value">{{ optional($emp)->position_title ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Travel Start</span>
                        <span class="value">{{ $tev->travel_date_start->format('F j, Y') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Travel End</span>
                        <span class="value">{{ $tev->travel_date_end->format('F j, Y') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Total Days</span>
                        <span class="value">{{ $tev->total_days }} day(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </span>
                    </div>
                </div>

                <div class="detail-item" style="margin-bottom:12px;">
                    <span class="label">Destination</span>
                    <span class="value" style="font-weight:400;">{{ $tev->destination }}</span>
                </div>
                <div class="detail-item" style="margin-bottom:12px;">
                    <span class="label">Purpose</span>
                    <span class="value" style="font-weight:400; line-height:1.5;">{{ $tev->purpose }}</span>
                </div>
                @if ($tev->remarks)
                <div style="padding:10px 14px; background:#f8f9ff; border-left:3px solid var(--navy);
                            border-radius:4px; font-size:0.82rem;">
                    <strong>Remarks:</strong> {{ $tev->remarks }}
                </div>
                @endif
            </div>
        </div>

        {{-- ── Itinerary Table ── --}}
        <div class="card">
            <div class="card-header"><h3>🗓 Itinerary of Travel</h3></div>
            <div class="card-body" style="padding:0; overflow-x:auto;">
                <table class="itin-tbl">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Mode</th>
                            <th>Transport (₱)</th>
                            <th>Half Day</th>
                            <th>Per Diem (₱)</th>
                            <th>Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tev->itineraryLines as $line)
                            @php
                                $lineTotal = (float)$line->transportation_cost + (float)$line->per_diem_amount;
                            @endphp
                            <tr>
                                <td>{{ $line->travel_date->format('M d, Y') }}</td>
                                <td>{{ $line->origin }}</td>
                                <td>{{ $line->destination }}</td>
                                <td class="text-center">
                                    {{ $line->departure_time ? \Carbon\Carbon::parse($line->departure_time)->format('h:iA') : '—' }}
                                </td>
                                <td class="text-center">
                                    {{ $line->arrival_time ? \Carbon\Carbon::parse($line->arrival_time)->format('h:iA') : '—' }}
                                </td>
                                <td class="text-center">{{ ucfirst($line->mode_of_transport ?? '—') }}</td>
                                <td class="text-right">{{ number_format($line->transportation_cost, 2) }}</td>
                                <td class="text-center">{{ $line->is_half_day ? 'Yes' : '—' }}</td>
                                <td class="text-right">{{ number_format($line->per_diem_amount, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center; padding:24px; color:var(--text-light);">
                                    No itinerary lines added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align:right;">TOTALS</td>
                            <td class="text-right" style="color:var(--navy);">
                                ₱{{ number_format($tev->total_transportation, 2) }}
                            </td>
                            <td></td>
                            <td class="text-right" style="color:var(--navy);">
                                ₱{{ number_format($tev->total_per_diem, 2) }}
                            </td>
                            <td class="text-right" style="color:var(--navy); font-size:0.9rem;">
                                ₱{{ number_format($tev->grand_total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Approval Log ── --}}
        @if ($tev->approvalLogs->count() > 0)
        <div class="card">
            <div class="card-header"><h3>📋 Approval Timeline</h3></div>
            <div class="card-body">
                <div class="log-timeline">
                    @foreach ($tev->approvalLogs as $log)
                        @php
                            $logIcon = match($log->action) {
                                'rejected' => '✕',
                                'returned' => '↩',
                                default    => '✓',
                            };
                        @endphp
                        <div class="log-item">
                            <div class="log-dot {{ $log->action }}">{{ $logIcon }}</div>
                            <div>
                                <div style="font-weight:600; font-size:0.85rem;">
                                    {{ ucwords(str_replace('_', ' ', $log->step)) }}
                                    <span style="font-size:0.75rem; color:var(--text-light); font-weight:400;">
                                        — {{ strtoupper($log->action) }}
                                    </span>
                                </div>
                                <div class="log-meta">
                                    {{ optional($log->user)->name ?? 'System' }}
                                    &nbsp;·&nbsp;
                                    {{ $log->performed_at ? $log->performed_at->format('M d, Y h:i A') : '—' }}
                                </div>
                                @if ($log->remarks)
                                    <div style="font-size:0.78rem; color:var(--text-mid); margin-top:4px;
                                                padding:4px 8px; background:#f8f9ff; border-radius:4px;">
                                        {{ $log->remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ── Certification Block ── --}}
        @if ($tev->certification)
        <div class="card">
            <div class="card-header"><h3>📜 Certification of Travel Completed</h3></div>
            <div class="card-body">
                @php $cert = $tev->certification; @endphp
                <div class="detail-grid" style="margin-bottom:12px;">
                    <div class="detail-item">
                        <span class="label">Travel Completed</span>
                        <span class="value">{{ $cert->travel_completed ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Date Returned</span>
                        <span class="value">
                            {{ $cert->date_returned ? $cert->date_returned->format('M d, Y') : '—' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Place Reported Back</span>
                        <span class="value">{{ $cert->place_reported_back ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Agency Visited</span>
                        <span class="value">{{ $cert->agency_visited ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Appearance Date</span>
                        <span class="value">
                            {{ $cert->appearance_date ? $cert->appearance_date->format('M d, Y') : '—' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Annex A Amount</span>
                        <span class="value">₱{{ number_format($cert->annex_a_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Certified By</span>
                        <span class="value">{{ optional($cert->certifier)->name ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Certified On</span>
                        <span class="value">
                            {{ $cert->certified_at ? $cert->certified_at->format('M d, Y') : '—' }}
                        </span>
                    </div>
                </div>
                @if ($cert->annex_a_particulars)
                <div style="font-size:0.82rem; padding:8px 12px; background:#f8f9ff;
                            border-radius:4px; border-left:3px solid var(--navy);">
                    <strong>Annex A Particulars:</strong> {{ $cert->annex_a_particulars }}
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right panel: actions ── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Totals summary --}}
        <div style="background:var(--navy); color:#fff; border-radius:8px; padding:16px 20px;">
            <div style="font-size:0.68rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:0.07em; opacity:0.6; margin-bottom:10px;">Summary</div>
            @foreach ([
                'Transportation' => $tev->total_transportation,
                'Per Diem'       => $tev->total_per_diem,
                'Other Expenses' => $tev->total_other_expenses,
            ] as $label => $val)
            <div style="display:flex; justify-content:space-between; padding:5px 0;
                        border-bottom:1px solid rgba(255,255,255,0.1); font-size:0.82rem;">
                <span>{{ $label }}</span>
                <span>₱{{ number_format($val, 2) }}</span>
            </div>
            @endforeach
            <div style="display:flex; justify-content:space-between; padding:8px 0 0;
                        font-size:1rem; font-weight:700; color:var(--gold);">
                <span>Grand Total</span>
                <span>₱{{ number_format($tev->grand_total, 2) }}</span>
            </div>
        </div>

        {{-- Submit --}}
        @if ($canSubmit)
        <div class="card no-print">
            <div class="card-header"><h3>📤 Submit TEV</h3></div>
            <div class="card-body">
                <p style="font-size:0.82rem; color:var(--text-mid); margin-bottom:12px;">
                    Submit this TEV for HR review. Make sure all itinerary lines are complete.
                </p>
                <form method="POST" action="{{ route('tev.submit', $tev->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Submit this TEV for approval?')">
                        📤 Submit for Approval
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Approve --}}
        @if ($canApprove)
        <div class="card no-print">
            <div class="card-header"><h3>✓ {{ $nextAction }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tev.approve', $tev->id) }}">
                    @csrf
                    <div class="form-group">
                        <label for="approve_remarks">Remarks (optional)</label>
                        <textarea id="approve_remarks" name="remarks" rows="2"
                                  placeholder="Add remarks..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Proceed with: {{ $nextAction }}?')">
                        ✓ {{ $nextAction }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Reject --}}
        @if ($canReject)
        <div class="card no-print">
            <div class="card-header" style="border-left:3px solid var(--red);">
                <h3 style="color:var(--red);">✕ Reject</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tev.reject', $tev->id) }}">
                    @csrf
                    <div class="form-group">
                        <label for="reject_remarks">
                            Reason for Rejection <span style="color:var(--red);">*</span>
                        </label>
                        <textarea id="reject_remarks" name="remarks" rows="3"
                                  placeholder="State the reason for rejection (required)..."
                                  required></textarea>
                        @error('remarks')
                            <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Reject this TEV? This action will be logged.')">
                        ✕ Reject TEV
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Certify --}}
        @if ($canCertify)
        <div class="card no-print">
            <div class="card-header"><h3>📜 Certify Travel</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tev.certify', $tev->id) }}">
                    @csrf

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="travel_completed" value="1"
                                   {{ optional($tev->certification)->travel_completed ? 'checked' : '' }}>
                            Travel Completed
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="date_returned">Date Returned</label>
                        <input type="date" id="date_returned" name="date_returned"
                               value="{{ optional($tev->certification)->date_returned?->toDateString() }}">
                    </div>

                    <div class="form-group">
                        <label for="place_reported_back">Place Reported Back</label>
                        <input type="text" id="place_reported_back" name="place_reported_back"
                               value="{{ optional($tev->certification)->place_reported_back }}"
                               placeholder="e.g. DOLE RO9 Office">
                    </div>

                    <div class="form-group">
                        <label for="annex_a_amount">Annex A Amount (₱)</label>
                        <input type="number" id="annex_a_amount" name="annex_a_amount"
                               value="{{ optional($tev->certification)->annex_a_amount ?? 0 }}"
                               step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label for="annex_a_particulars">Annex A Particulars</label>
                        <textarea id="annex_a_particulars" name="annex_a_particulars" rows="2"
                                  placeholder="Details of expenses not requiring receipts...">{{ optional($tev->certification)->annex_a_particulars }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="agency_visited">Agency Visited</label>
                        <input type="text" id="agency_visited" name="agency_visited"
                               value="{{ optional($tev->certification)->agency_visited }}"
                               placeholder="Name of agency...">
                    </div>

                    <div class="form-group">
                        <label for="appearance_date">Appearance Date</label>
                        <input type="date" id="appearance_date" name="appearance_date"
                               value="{{ optional($tev->certification)->appearance_date?->toDateString() }}">
                    </div>

                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person"
                               value="{{ optional($tev->certification)->contact_person }}"
                               placeholder="Name of contact person...">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        📜 Save Certification
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection