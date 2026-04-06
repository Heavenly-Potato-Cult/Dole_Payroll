{{-- resources/views/tev/create.blade.php --}}
{{--
    Expects from TevController@create:
      $approvedOrders — collection of approved OfficeOrder with employee
      $perDiemRates   — collection of PerDiemRate grouped by travel_type
--}}

@extends('layouts.app')

@section('title', 'New TEV Request')
@section('page-title', 'Travel (TEV)')

@section('styles')
<style>
/* ── Section label ── */
.section-title {
    font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--text-light); margin-bottom: 12px;
    padding-bottom: 6px; border-bottom: 1px solid var(--border);
}

/* ── Helper / hint text ── */
.field-hint {
    font-size: 0.74rem; color: var(--text-light);
    margin-top: 4px; line-height: 1.4;
}

/* ── Track radio cards ── */
.track-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.track-card {
    display: flex; align-items: flex-start; gap: 12px; cursor: pointer;
    padding: 14px 16px; border-radius: 8px; border: 2px solid var(--border);
    background: transparent; transition: all 0.2s ease;
}
.track-card-body { display: flex; flex-direction: column; gap: 3px; }
.track-card-title { font-weight: 700; font-size: 0.90rem; }
.track-card-desc  { font-size: 0.76rem; color: var(--text-light); line-height: 1.35; }
.track-card.cash_advance  .track-card-title { color: #1B5E20; }
.track-card.reimbursement .track-card-title { color: #1A237E; }
.track-card.selected-cash  { border-color: #1B5E20; background: #E8F5E9; }
.track-card.selected-reimb { border-color: #1A237E; background: #E8EAF6; }
.track-card input[type="radio"] { margin-top: 2px; flex-shrink: 0; }

/* ── Totals panel ── */
.totals-panel { background: var(--navy); color: #fff; border-radius: 8px; padding: 16px 20px; font-size: 0.83rem; }
.totals-panel .totals-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.12);
}
.totals-panel .totals-row:last-child { border-bottom: none; }
.totals-panel .totals-grand { font-size: 1rem; font-weight: 700; color: var(--gold); }

/* ── Itinerary table (desktop) ── */
.itin-desktop { display: block; overflow-x: auto; }
.itin-desktop table { width: 100%; border-collapse: collapse; font-size: 0.81rem; min-width: 780px; }
.itin-desktop thead th {
    background: var(--navy); color: #fff; padding: 8px 8px;
    font-size: 0.70rem; font-weight: 600; letter-spacing: 0.03em;
    border: 1px solid rgba(255,255,255,0.15); text-align: center;
    white-space: nowrap;
}
.itin-desktop thead th .th-sub {
    display: block; font-size: 0.62rem; font-weight: 400;
    opacity: 0.75; margin-top: 1px; font-style: italic;
}
.itin-desktop tbody td { padding: 5px 5px; border: 1px solid var(--border); vertical-align: middle; }
.itin-desktop tfoot td {
    padding: 8px 10px; font-weight: 700;
    background: #f0f2ff; border: 1px solid var(--border); font-size: 0.83rem;
}
.itin-desktop input, .itin-desktop select {
    width: 100%; padding: 5px 6px;
    border: 1px solid var(--border); border-radius: 4px;
    font-size: 0.80rem; background: #fff; box-sizing: border-box;
}
.itin-desktop input[type="checkbox"] { width: auto; cursor: pointer; }
.itin-desktop input:focus, .itin-desktop select:focus {
    outline: none; border-color: var(--navy);
    box-shadow: 0 0 0 2px rgba(15,27,76,0.12);
}

/* ── Example row hint ── */
.itin-example {
    background: #f8f9ff; border: 1px dashed #b0b8d8;
    border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;
    font-size: 0.78rem; color: var(--text-mid); line-height: 1.6;
}
.itin-example strong { color: var(--navy); }

/* ── Itinerary mobile cards ── */
.itin-mobile { display: none; }
.itin-card {
    background: #f8f9ff; border: 1px solid var(--border);
    border-radius: 8px; padding: 14px; margin-bottom: 12px;
}
.itin-card-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border);
}
.itin-card-date { font-weight: 700; color: var(--navy); font-size: 0.85rem; }
.itin-card-remove {
    background: var(--red); color: white; border: none;
    border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 0.75rem;
}
.itin-card-field { margin-bottom: 10px; }
.itin-card-field label {
    display: block; font-size: 0.70rem; font-weight: 700;
    color: var(--text-mid); margin-bottom: 3px;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.itin-card-field .field-hint { font-size: 0.70rem; }
.itin-card-field input, .itin-card-field select {
    width: 100%; padding: 8px 10px;
    border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;
}
.itin-card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.itin-card-halfday { display: flex; align-items: center; gap: 8px; margin: 10px 0; }
.itin-card-halfday input { width: auto; }

/* ── Step indicator ── */
.step-indicator {
    display: flex; gap: 0; margin-bottom: 24px; border-radius: 8px;
    border: 1px solid var(--border); overflow: hidden;
}
.step-item {
    flex: 1; padding: 10px 14px; font-size: 0.76rem; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
    background: var(--surface); color: var(--text-light);
    border-right: 1px solid var(--border);
}
.step-item:last-child { border-right: none; }
.step-item.active { background: #EEF1FA; color: var(--navy); }
.step-num {
    width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
    background: var(--border); color: var(--text-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.70rem; font-weight: 700;
}
.step-item.active .step-num { background: var(--navy); color: #fff; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .tev-create-grid { grid-template-columns: 1fr !important; gap: 20px; }
    .tev-right-panel { position: static !important; }
}
@media (max-width: 768px) {
    .track-cards { grid-template-columns: 1fr; gap: 12px; }
    .form-row-grid { grid-template-columns: 1fr !important; gap: 12px !important; }
    .itin-desktop { display: none; }
    .itin-mobile  { display: block; }
    .step-indicator { display: none; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
}
@media (max-width: 480px) {
    .itin-card-row { grid-template-columns: 1fr; gap: 8px; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New TEV Request</h1>
        <p class="text-muted">Travel Expense Voucher — fill in all sections then click <strong>Save TEV Request</strong>.</p>
    </div>
    <a href="{{ route('tev.index') }}" class="btn btn-outline btn-sm">← Back to List</a>
</div>

{{-- ── Validation errors ── --}}
@if ($errors->any())
<div class="alert alert-error" style="margin-bottom:16px;">
    <strong>Please fix the following before saving:</strong>
    <ul style="margin:6px 0 0; padding-left:18px;">
        @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── Step indicator (desktop only) ── --}}
<div class="step-indicator">
    <div class="step-item active"><div class="step-num">1</div> Select Office Order</div>
    <div class="step-item active"><div class="step-num">2</div> Choose Track</div>
    <div class="step-item active"><div class="step-num">3</div> Travel Details</div>
    <div class="step-item active"><div class="step-num">4</div> Itinerary Lines</div>
    <div class="step-item active"><div class="step-num">5</div> Save</div>
</div>

{{-- Encode per diem rates as JSON for JS --}}
@php
    $ratesArr = [];
    foreach ($perDiemRates as $type => $rates) {
        $first = $rates->first();
        if ($first) {
            $ratesArr[$type] = [
                'daily'    => (float) $first->daily_rate,
                'half_day' => $first->half_day_rate
                    ? (float) $first->half_day_rate
                    : round((float) $first->daily_rate / 2, 2),
            ];
        }
    }
    $ratesJson = json_encode($ratesArr);
@endphp

<form method="POST" action="{{ route('tev.store') }}" id="tevForm">
@csrf

<div class="tev-create-grid" style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start;">

    {{-- ────────────── LEFT COLUMN ────────────── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- STEP 1: Office Order ── --}}
        <div class="card">
            <div class="card-header">
                <h3>📝 Step 1 — Office Order</h3>
            </div>
            <div class="card-body">
                <p class="field-hint" style="margin-bottom:14px;">
                    Select the <strong>approved Office Order</strong> that authorises this travel.
                    The destination, travel type, and dates will be filled in automatically.
                </p>

                <div class="form-group">
                    <label for="office_order_id">
                        Office Order <span style="color:var(--red);">*</span>
                    </label>
                    <select name="office_order_id" id="office_order_id"
                            class="{{ $errors->has('office_order_id') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select an approved Office Order —</option>
                        @foreach ($approvedOrders as $oo)
                            @php
                                $ooEmp   = $oo->employee;
                                $empName = optional($ooEmp)->last_name . ', ' . optional($ooEmp)->first_name;
                            @endphp
                            <option value="{{ $oo->id }}"
                                data-destination="{{ $oo->destination }}"
                                data-travel-type="{{ $oo->travel_type }}"
                                data-purpose="{{ $oo->purpose }}"
                                data-date-start="{{ $oo->travel_date_start->toDateString() }}"
                                data-date-end="{{ $oo->travel_date_end->toDateString() }}"
                                {{ old('office_order_id') == $oo->id ? 'selected' : '' }}>
                                {{ $oo->office_order_no }} — {{ $empName }}
                                ({{ $oo->travel_date_start->format('M d') }}–{{ $oo->travel_date_end->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('office_order_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Auto-filled preview ── --}}
                <div id="oo-preview" style="display:none; margin-top:10px; padding:10px 14px;
                     background:#f0f2ff; border-radius:6px; font-size:0.82rem;
                     border-left:3px solid var(--navy);">
                    <div><strong>Destination:</strong> <span id="oo-destination"></span></div>
                    <div><strong>Travel Type:</strong> <span id="oo-travel-type"></span>
                        <span id="oo-perdiem-hint" style="margin-left:8px; font-size:0.76rem;
                              color:#1B5E20; font-weight:600;"></span>
                    </div>
                    <div><strong>Purpose:</strong> <span id="oo-purpose"></span></div>
                    <div><strong>Travel Period:</strong> <span id="oo-dates"></span></div>
                </div>
            </div>
        </div>

        {{-- STEP 2: Track ── --}}
        <div class="card">
            <div class="card-header"><h3>💳 Step 2 — Track</h3></div>
            <div class="card-body">
                <p class="field-hint" style="margin-bottom:14px;">
                    Choose when the money moves — <strong>before</strong> or <strong>after</strong> travel.
                </p>

                <div class="track-cards">
                    @php $trackChecked = old('track', 'cash_advance'); @endphp
                    <label class="track-card cash_advance {{ $trackChecked === 'cash_advance' ? 'selected-cash' : '' }}">
                        <input type="radio" name="track" value="cash_advance"
                               {{ $trackChecked === 'cash_advance' ? 'checked' : '' }}>
                        <div class="track-card-body">
                            <span class="track-card-title">💵 Cash Advance</span>
                            <span class="track-card-desc">Request funds <em>before</em> you travel. Requires liquidation after you return.</span>
                        </div>
                    </label>
                    <label class="track-card reimbursement {{ $trackChecked === 'reimbursement' ? 'selected-reimb' : '' }}">
                        <input type="radio" name="track" value="reimbursement"
                               {{ $trackChecked === 'reimbursement' ? 'checked' : '' }}>
                        <div class="track-card-body">
                            <span class="track-card-title">🧾 Reimbursement</span>
                            <span class="track-card-desc">Claim expenses <em>after</em> travel. You pay first, DOLE reimburses you.</span>
                        </div>
                    </label>
                </div>
                @error('track')
                    <div class="invalid-feedback" style="display:block; margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- STEP 3: Travel Details ── --}}
        <div class="card">
            <div class="card-header"><h3>✈ Step 3 — Travel Details</h3></div>
            <div class="card-body">
                <p class="field-hint" style="margin-bottom:14px;">
                    These fields are <strong>auto-filled from the Office Order</strong> above.
                    You may adjust them if needed.
                </p>

                {{-- Hidden travel_type — set by JS from OO --}}
                <input type="hidden" name="travel_type" id="travel_type"
                       value="{{ old('travel_type', 'local') }}">

                <div class="form-row-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="travel_date_start">
                            Travel Date — Start <span style="color:var(--red);">*</span>
                        </label>
                        <input type="date" id="travel_date_start" name="travel_date_start"
                               value="{{ old('travel_date_start') }}"
                               class="{{ $errors->has('travel_date_start') ? 'is-invalid' : '' }}" required>
                        @error('travel_date_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="travel_date_end">
                            Travel Date — End <span style="color:var(--red);">*</span>
                        </label>
                        <input type="date" id="travel_date_end" name="travel_date_end"
                               value="{{ old('travel_date_end') }}"
                               class="{{ $errors->has('travel_date_end') ? 'is-invalid' : '' }}" required>
                        @error('travel_date_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose <span style="color:var(--red);">*</span></label>
                    <textarea id="purpose" name="purpose" rows="2"
                              placeholder="e.g. Attendance to the 1st Quarterly DOLE-PESO and JPO Meeting"
                              class="{{ $errors->has('purpose') ? 'is-invalid' : '' }}" required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="destination">Destination <span style="color:var(--red);">*</span></label>
                    <input type="text" id="destination" name="destination"
                           placeholder="e.g. Royal Farm Resort, Dipolog City, Zamboanga del Norte"
                           value="{{ old('destination') }}"
                           class="{{ $errors->has('destination') ? 'is-invalid' : '' }}" required>
                    @error('destination')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks (optional)</label>
                    <textarea id="remarks" name="remarks" rows="2"
                              placeholder="Any additional notes...">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        {{-- STEP 4: Itinerary Lines ── --}}
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <h3>🗓 Step 4 — Itinerary Lines</h3>
                <button type="button" id="addRowBtn" class="btn btn-sm btn-gold">+ Add Row</button>
            </div>
            <div class="card-body">

                {{-- ── How to fill this section ── --}}
                <div class="itin-example">
                    <strong>How to fill this section:</strong> Each row is one <em>leg</em> of your journey.
                    Enter place names (not dates) in the <strong>From</strong> and <strong>To</strong> columns.<br>
                    <strong>Example rows for a 2-day trip to Dipolog City:</strong>
                    <table style="margin-top:8px; width:100%; border-collapse:collapse; font-size:0.76rem;">
                        <thead>
                            <tr style="background:#e8ecf8;">
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:left;">Date</th>
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:left;">From</th>
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:left;">To</th>
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:left;">Mode</th>
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">Transport</th>
                                <th style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">Per Diem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Feb 18, 2026</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Residence</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">DOLE RO9 Office</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">E-Bike</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">50.00</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">0.00</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Feb 18, 2026</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">DOLE RO9 Office</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Dipolog City, ZDN</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Rented Van</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">0.00</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">1,500.00</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8;">Feb 19, 2026</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; font-style:italic; color:#666;" colspan="3">Still in Dipolog City (accommodation/meals day)</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">0.00</td>
                                <td style="padding:4px 8px; border:1px solid #c8cfe8; text-align:right;">300.00</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top:6px; font-size:0.74rem; color:#7B5800;">
                        💡 <strong>From / To</strong> = place names (e.g. "Residence", "DOLE RO9 Office", "Dipolog City, ZDN") — <em>not</em> dates.<br>
                        💡 <strong>Per Diem</strong> is auto-filled from your travel type. You can adjust it per row.<br>
                        💡 <strong>Half Day</strong> = tick this if you only travelled half a day (reduces per diem to half rate).
                    </div>
                </div>

                {{-- Desktop Table ── --}}
                <div class="itin-desktop">
                    <table>
                        <thead>
                            <tr>
                                <th style="min-width:110px;">
                                    Date
                                    <span class="th-sub">Travel date</span>
                                </th>
                                <th style="min-width:130px;">
                                    From
                                    <span class="th-sub">Origin place</span>
                                </th>
                                <th style="min-width:130px;">
                                    To
                                    <span class="th-sub">Destination place</span>
                                </th>
                                <th style="min-width:85px;">
                                    Depart
                                    <span class="th-sub">Time (optional)</span>
                                </th>
                                <th style="min-width:85px;">
                                    Arrive
                                    <span class="th-sub">Time (optional)</span>
                                </th>
                                <th style="min-width:100px;">
                                    Mode
                                    <span class="th-sub">Transport type</span>
                                </th>
                                <th style="min-width:90px;">
                                    Transport (₱)
                                    <span class="th-sub">Fare paid</span>
                                </th>
                                <th style="min-width:50px;">
                                    Half
                                    <span class="th-sub">Day?</span>
                                </th>
                                <th style="min-width:90px;">
                                    Per Diem (₱)
                                    <span class="th-sub">Auto-filled</span>
                                </th>
                                <th style="min-width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itinBodyDesktop"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right; color:var(--text-mid);">Totals:</td>
                                <td id="foot-transport" style="text-align:right;">₱0.00</td>
                                <td></td>
                                <td id="foot-perdiem" style="text-align:right;">₱0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Mobile Cards ── --}}
                <div class="itin-mobile" id="itinMobileContainer"></div>

                <div id="itin-empty" style="text-align:center; padding:24px;
                     color:var(--text-light); font-size:0.83rem; display:none;">
                    Click <strong>+ Add Row</strong> to add itinerary lines.
                </div>

                @error('lines')
                    <div class="invalid-feedback" style="display:block; margin-top:8px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ────────────── RIGHT PANEL ────────────── --}}
    <div class="tev-right-panel" style="display:flex; flex-direction:column; gap:16px; position:sticky; top:20px;">

        {{-- Running totals ── --}}
        <div class="totals-panel">
            <div style="font-size:0.70rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:0.07em; opacity:0.65; margin-bottom:10px;">
                Running Totals
            </div>
            <div class="totals-row"><span>Transportation</span><span id="tot-transport">₱0.00</span></div>
            <div class="totals-row"><span>Per Diem</span><span id="tot-perdiem">₱0.00</span></div>
            <div class="totals-row totals-grand"
                 style="margin-top:8px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.25);">
                <span>Grand Total</span><span id="tot-grand">₱0.00</span>
            </div>
        </div>

        {{-- Per diem rate info ── --}}
        <div id="perdiem-info" style="display:none; background:#f0f8f2; border:1px solid #a5d6b5;
             border-radius:8px; padding:12px 14px; font-size:0.78rem; color:#1B5E20;">
            <div style="font-weight:700; margin-bottom:6px;">💡 Per Diem Rates (auto-applied)</div>
            <div id="perdiem-daily"></div>
            <div id="perdiem-half" style="color:#555; margin-top:2px;"></div>
        </div>

        {{-- Save card ── --}}
        <div class="card">
            <div class="card-body">
                <div style="font-size:0.78rem; color:var(--text-mid); margin-bottom:14px; line-height:1.5;">
                    Review all sections before saving.<br>
                    Per diem rates are auto-filled from the selected travel type.
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;" id="saveBtn">
                    💾 Save TEV Request
                </button>
                <a href="{{ route('tev.index') }}" class="btn btn-outline"
                   style="width:100%; margin-top:8px; display:block; text-align:center;">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Quick checklist ── --}}
        <div style="background:#fff; border:1px solid var(--border); border-radius:8px;
                    padding:12px 14px; font-size:0.76rem; line-height:1.7; color:var(--text-mid);">
            <div style="font-weight:700; color:var(--navy); margin-bottom:6px;">✅ Before you save:</div>
            <div>☐ Office Order selected</div>
            <div>☐ Track chosen (CA or Reimb.)</div>
            <div>☐ Travel dates set</div>
            <div>☐ Purpose &amp; destination filled</div>
            <div>☐ At least one itinerary row added</div>
            <div>☐ From/To are <em>place names</em>, not dates</div>
            <div>☐ Mode of transport selected per row</div>
        </div>

    </div>{{-- end right panel --}}

</div>{{-- end grid --}}
</form>

@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var PER_DIEM_RATES = {!! $ratesJson !!};
    var currentType    = document.getElementById('travel_type').value || 'local';
    var rowsData       = [];

    // ── Office Order auto-fill ────────────────────────────────────────────
    var ooSelect = document.getElementById('office_order_id');

    function applyOoSelection() {
        var opt = ooSelect.options[ooSelect.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('oo-preview').style.display = 'none';
            document.getElementById('perdiem-info').style.display = 'none';
            return;
        }

        var dest  = opt.getAttribute('data-destination') || '';
        var type  = opt.getAttribute('data-travel-type') || 'local';
        var purp  = opt.getAttribute('data-purpose') || '';
        var dS    = opt.getAttribute('data-date-start') || '';
        var dE    = opt.getAttribute('data-date-end') || '';

        // Update preview block
        document.getElementById('oo-destination').textContent = dest;
        document.getElementById('oo-travel-type').textContent =
            type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('oo-purpose').textContent = purp;
        document.getElementById('oo-dates').textContent   = dS + ' → ' + dE;
        document.getElementById('oo-preview').style.display = 'block';

        // Auto-fill form fields
        document.getElementById('destination').value        = dest;
        document.getElementById('purpose').value            = purp;
        document.getElementById('travel_type').value        = type;
        if (dS) document.getElementById('travel_date_start').value = dS;
        if (dE) document.getElementById('travel_date_end').value   = dE;

        currentType = type;

        // Show per diem rate hint
        var rates = PER_DIEM_RATES[type];
        if (rates) {
            document.getElementById('oo-perdiem-hint').textContent =
                '(₱' + rates.daily.toLocaleString() + '/day per diem)';
            document.getElementById('perdiem-daily').textContent =
                'Full day: ₱' + rates.daily.toLocaleString('en-PH', {minimumFractionDigits:2});
            document.getElementById('perdiem-half').textContent =
                'Half day: ₱' + rates.half_day.toLocaleString('en-PH', {minimumFractionDigits:2});
            document.getElementById('perdiem-info').style.display = 'block';
        }

        refreshAllPerDiem();
    }

    ooSelect.addEventListener('change', applyOoSelection);

    // Run on page load if OO is pre-selected (old() repopulation)
    if (ooSelect.value) applyOoSelection();

    // ── Track radio styling ───────────────────────────────────────────────
    var trackCards = document.querySelectorAll('.track-card');
    function updateTrackStyles() {
        trackCards.forEach(function (card) {
            var radio = card.querySelector('input[type="radio"]');
            card.classList.remove('selected-cash', 'selected-reimb');
            if (radio.checked) {
                card.classList.add(radio.value === 'cash_advance' ? 'selected-cash' : 'selected-reimb');
            }
        });
    }
    trackCards.forEach(function (card) {
        card.querySelector('input[type="radio"]').addEventListener('change', updateTrackStyles);
    });
    updateTrackStyles();

    // ── Per diem helpers ──────────────────────────────────────────────────
    function getDailyRate(half) {
        var rates = PER_DIEM_RATES[currentType];
        if (!rates) return 0;
        return half ? rates.half_day : rates.daily;
    }

    function refreshAllPerDiem() {
        rowsData.forEach(function (row, idx) {
            row.per_diem_amount = getDailyRate(row.is_half_day || false);
            updateRowUI(idx);
        });
        updateTotals();
    }

    // ── Totals ────────────────────────────────────────────────────────────
    function updateTotals() {
        var transport = 0, perdiem = 0;
        rowsData.forEach(function (row) {
            transport += parseFloat(row.transportation_cost) || 0;
            perdiem   += parseFloat(row.per_diem_amount)     || 0;
        });
        var grand = transport + perdiem;
        var fmt = function (n) {
            return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        document.getElementById('tot-transport').textContent  = fmt(transport);
        document.getElementById('tot-perdiem').textContent    = fmt(perdiem);
        document.getElementById('tot-grand').textContent      = fmt(grand);
        document.getElementById('foot-transport').textContent = fmt(transport);
        document.getElementById('foot-perdiem').textContent   = fmt(perdiem);
        updateEmptyState();
    }

    function updateEmptyState() {
        document.getElementById('itin-empty').style.display =
            rowsData.length === 0 ? 'block' : 'none';
    }

    // ── Row UI sync ───────────────────────────────────────────────────────
    function updateRowUI(idx) {
        var row = rowsData[idx];
        if (!row) return;

        var dr = document.getElementById('desktop-row-' + idx);
        if (dr) {
            dr.querySelector('.itin-transport').value = row.transportation_cost;
            dr.querySelector('.itin-perdiem').value   = row.per_diem_amount;
            dr.querySelector('.itin-halfday').checked = row.is_half_day;
        }

        var mc = document.getElementById('mobile-card-' + idx);
        if (mc) {
            mc.querySelector('.itin-mobile-transport').value = row.transportation_cost;
            mc.querySelector('.itin-mobile-perdiem').value   = row.per_diem_amount;
            mc.querySelector('.itin-mobile-halfday').checked = row.is_half_day;
        }
    }

    function updateRowValue(idx, field, value) {
        if (!rowsData[idx]) return;
        rowsData[idx][field] = value;
        if (field === 'is_half_day') {
            rowsData[idx].per_diem_amount = getDailyRate(value);
        }
        updateRowUI(idx);
        updateTotals();
    }

    // ── Render all rows ───────────────────────────────────────────────────
    function renderAllRows() {
        var desktopTbody     = document.getElementById('itinBodyDesktop');
        var mobileContainer  = document.getElementById('itinMobileContainer');
        desktopTbody.innerHTML    = '';
        mobileContainer.innerHTML = '';

        var modes = ['bus','jeepney','rented van','e-bike','motorcycle','boat','plane','vehicle','other'];

        rowsData.forEach(function (row, idx) {

            var modeOptions = modes.map(function (m) {
                var label    = m.charAt(0).toUpperCase() + m.slice(1);
                var selected = (row.mode_of_transport || '').toLowerCase() === m ? ' selected' : '';
                return '<option value="' + m + '"' + selected + '>' + label + '</option>';
            }).join('');

            // ── Desktop row ──
            var tr = document.createElement('tr');
            tr.id  = 'desktop-row-' + idx;
            tr.innerHTML =
                '<td><input type="date" name="lines[' + idx + '][travel_date]" value="' + (row.travel_date || '') + '" required></td>' +
                '<td><input type="text"  name="lines[' + idx + '][origin]"      value="' + escHtml(row.origin || '') + '" placeholder="e.g. DOLE RO9 Office" required></td>' +
                '<td><input type="text"  name="lines[' + idx + '][destination]" value="' + escHtml(row.destination || '') + '" placeholder="e.g. Dipolog City" required></td>' +
                '<td><input type="time"  name="lines[' + idx + '][departure_time]" value="' + (row.departure_time || '') + '"></td>' +
                '<td><input type="time"  name="lines[' + idx + '][arrival_time]"   value="' + (row.arrival_time || '') + '"></td>' +
                '<td><select name="lines[' + idx + '][mode_of_transport]" required><option value="">— select —</option>' + modeOptions + '</select></td>' +
                '<td><input type="number" name="lines[' + idx + '][transportation_cost]" value="' + (row.transportation_cost || 0) + '" step="0.01" min="0" class="itin-transport" required placeholder="0.00"></td>' +
                '<td style="text-align:center;"><input type="checkbox" name="lines[' + idx + '][is_half_day]" value="1" class="itin-halfday"' + (row.is_half_day ? ' checked' : '') + '></td>' +
                '<td><input type="number" name="lines[' + idx + '][per_diem_amount]" value="' + (row.per_diem_amount || 0) + '" step="0.01" min="0" class="itin-perdiem" required placeholder="0.00"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger" onclick="tevRemoveRow(' + idx + ')" style="padding:3px 8px; white-space:nowrap;">✕</button></td>';

            tr.querySelector('.itin-transport').addEventListener('input', function (e) {
                updateRowValue(idx, 'transportation_cost', e.target.value);
            });
            tr.querySelector('.itin-perdiem').addEventListener('input', function (e) {
                updateRowValue(idx, 'per_diem_amount', e.target.value);
            });
            tr.querySelector('.itin-halfday').addEventListener('change', function (e) {
                updateRowValue(idx, 'is_half_day', e.target.checked);
            });
            tr.querySelectorAll('input[type="date"], input[type="text"], input[type="time"], select').forEach(function (el) {
                if (el.name) {
                    var match = el.name.match(/\[([^\]]+)\]$/);
                    if (match) {
                        el.addEventListener('change', function (e) {
                            updateRowValue(idx, match[1], e.target.value);
                        });
                    }
                }
            });

            desktopTbody.appendChild(tr);

            // ── Mobile card ──
            var card    = document.createElement('div');
            card.className = 'itin-card';
            card.id        = 'mobile-card-' + idx;
            card.innerHTML =
                '<div class="itin-card-header">' +
                    '<span class="itin-card-date">Row ' + (idx + 1) + (row.travel_date ? ' · ' + row.travel_date : '') + '</span>' +
                    '<button type="button" class="itin-card-remove" onclick="tevRemoveRow(' + idx + ')">✕ Remove</button>' +
                '</div>' +
                '<div class="itin-card-row">' +
                    '<div class="itin-card-field"><label>Travel Date *</label><input type="date" name="lines[' + idx + '][travel_date]" value="' + (row.travel_date || '') + '" required class="itin-mobile-date"></div>' +
                    '<div class="itin-card-field"><label>Mode of Transport *</label><select name="lines[' + idx + '][mode_of_transport]" required class="itin-mobile-mode"><option value="">— select —</option>' + modeOptions + '</select></div>' +
                '</div>' +
                '<div class="itin-card-field"><label>From (Origin Place) *</label><input type="text" name="lines[' + idx + '][origin]" value="' + escHtml(row.origin || '') + '" placeholder="e.g. DOLE RO9 Office, Zamboanga City" required class="itin-mobile-origin"><p class="field-hint">Place name where you departed from</p></div>' +
                '<div class="itin-card-field"><label>To (Destination Place) *</label><input type="text" name="lines[' + idx + '][destination]" value="' + escHtml(row.destination || '') + '" placeholder="e.g. Dipolog City, Zamboanga del Norte" required class="itin-mobile-dest"><p class="field-hint">Place name where you arrived</p></div>' +
                '<div class="itin-card-row">' +
                    '<div class="itin-card-field"><label>Departure Time</label><input type="time" name="lines[' + idx + '][departure_time]" value="' + (row.departure_time || '') + '" class="itin-mobile-depart"></div>' +
                    '<div class="itin-card-field"><label>Arrival Time</label><input type="time" name="lines[' + idx + '][arrival_time]" value="' + (row.arrival_time || '') + '" class="itin-mobile-arrive"></div>' +
                '</div>' +
                '<div class="itin-card-row">' +
                    '<div class="itin-card-field"><label>Transport Cost (₱) *</label><input type="number" name="lines[' + idx + '][transportation_cost]" value="' + (row.transportation_cost || 0) + '" step="0.01" min="0" class="itin-mobile-transport" required placeholder="0.00"><p class="field-hint">Fare/ticket amount paid</p></div>' +
                    '<div class="itin-card-field"><label>Per Diem (₱) *</label><input type="number" name="lines[' + idx + '][per_diem_amount]" value="' + (row.per_diem_amount || 0) + '" step="0.01" min="0" class="itin-mobile-perdiem" required placeholder="0.00"><p class="field-hint">Auto-filled; adjust if needed</p></div>' +
                '</div>' +
                '<div class="itin-card-halfday">' +
                    '<input type="checkbox" name="lines[' + idx + '][is_half_day]" value="1" class="itin-mobile-halfday"' + (row.is_half_day ? ' checked' : '') + '>' +
                    '<label style="font-size:0.82rem;">Half Day (reduces per diem to half rate)</label>' +
                '</div>';

            card.querySelector('.itin-mobile-transport').addEventListener('input', function (e) { updateRowValue(idx, 'transportation_cost', e.target.value); });
            card.querySelector('.itin-mobile-perdiem').addEventListener('input',   function (e) { updateRowValue(idx, 'per_diem_amount', e.target.value); });
            card.querySelector('.itin-mobile-halfday').addEventListener('change',  function (e) { updateRowValue(idx, 'is_half_day', e.target.checked); });
            card.querySelector('.itin-mobile-date').addEventListener('change',    function (e) { updateRowValue(idx, 'travel_date', e.target.value); });
            card.querySelector('.itin-mobile-origin').addEventListener('change',  function (e) { updateRowValue(idx, 'origin', e.target.value); });
            card.querySelector('.itin-mobile-dest').addEventListener('change',    function (e) { updateRowValue(idx, 'destination', e.target.value); });
            card.querySelector('.itin-mobile-mode').addEventListener('change',    function (e) { updateRowValue(idx, 'mode_of_transport', e.target.value); });
            card.querySelector('.itin-mobile-depart').addEventListener('change',  function (e) { updateRowValue(idx, 'departure_time', e.target.value); });
            card.querySelector('.itin-mobile-arrive').addEventListener('change',  function (e) { updateRowValue(idx, 'arrival_time', e.target.value); });

            mobileContainer.appendChild(card);
        });

        updateEmptyState();
    }

    // ── Add / Remove row ──────────────────────────────────────────────────
    function addRow(data) {
        data = data || {};
        rowsData.push({
            travel_date:          data.travel_date          || '',
            origin:               data.origin               || '',
            destination:          data.destination          || '',
            departure_time:       data.departure_time       || '',
            arrival_time:         data.arrival_time         || '',
            mode_of_transport:    data.mode_of_transport    || '',
            transportation_cost:  data.transportation_cost  || 0,
            per_diem_amount:      data.per_diem_amount !== undefined
                                    ? data.per_diem_amount
                                    : getDailyRate(false),
            is_half_day:          data.is_half_day          || false,
        });
        renderAllRows();
        updateTotals();
    }

    window.tevRemoveRow = function (idx) {
        rowsData.splice(idx, 1);
        renderAllRows();
        updateTotals();
    };

    document.getElementById('addRowBtn').addEventListener('click', function () { addRow({}); });

    // ── HTML escape helper ────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Re-populate on validation redirect (old() data) ──────────────────
    @if (old('lines'))
        @foreach (old('lines') as $i => $line)
            addRow({
                travel_date:         '{{ $line['travel_date']         ?? '' }}',
                origin:              '{!! addslashes($line['origin']          ?? '') !!}',
                destination:         '{!! addslashes($line['destination']     ?? '') !!}',
                departure_time:      '{{ $line['departure_time']      ?? '' }}',
                arrival_time:        '{{ $line['arrival_time']        ?? '' }}',
                mode_of_transport:   '{{ $line['mode_of_transport']   ?? '' }}',
                transportation_cost: '{{ $line['transportation_cost'] ?? 0 }}',
                per_diem_amount:     '{{ $line['per_diem_amount']     ?? 0 }}',
                is_half_day:         {{ !empty($line['is_half_day']) ? 'true' : 'false' }},
            });
        @endforeach
    @else
        addRow({});
    @endif

    updateTotals();

})();
</script>
@endsection